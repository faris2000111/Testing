<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminMenu;
use App\Models\MenuSection;
use App\Models\Role;
use App\Services\MenuScaffolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMenuController extends Controller
{
    public function index(): View
    {
        $menus = AdminMenu::roots()
            ->with(['children' => fn ($q) => $q->orderBy('order'), 'section'])
            ->get()
            ->sortBy(fn ($m) => ($m->section->order ?? 0) * 10000 + $m->order);

        return view('admin.menu.index', compact('menus'));
    }

    public function create(): View
    {
        $parentMenus = AdminMenu::roots()->with('section')->orderBy('order')->get();
        $sections = MenuSection::ordered()->get();

        return view('admin.menu.create', compact('parentMenus', 'sections'));
    }

    public function store(Request $request, MenuScaffolder $scaffolder): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', 'unique:admin_menus,slug'],
            'icon' => ['required', 'string', 'max:100'],
            'icon_gradient' => ['required', 'string', 'max:50'],
            'section_id' => ['required', 'exists:menu_sections,id'],
            'parent_id' => ['nullable', 'exists:admin_menus,id'],
            'is_active' => ['nullable', 'boolean'],
            'has_crud' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['has_crud'] = $request->boolean('has_crud', false);
        $validated['order'] = AdminMenu::where('section_id', $validated['section_id'])->max('order') + 1;

        // Auto-set route name based on slug
        $validated['route_name'] = "admin.{$validated['slug']}.index";

        $menu = AdminMenu::create($validated);

        // Auto-assign menu to all superadmin roles
        $superadminRoles = Role::where('is_superadmin', true)->get();
        foreach ($superadminRoles as $role) {
            $role->menus()->attach($menu->id);
        }

        // If this menu has a parent, convert parent to parent-only (remove its controller/views)
        if ($menu->parent_id) {
            $parent = AdminMenu::find($menu->parent_id);
            if ($parent) {
                $this->convertToParentMenu($parent);
            }
        }

        // Scaffold controller + views (only for non-child or standalone menus)
        $generated = $scaffolder->scaffold($menu);

        ActivityLog::record('created', $menu, "Menambah menu: {$menu->label}");

        $msg = "Menu \"{$menu->label}\" berhasil ditambahkan.";
        if (count($generated) > 0) {
            $msg .= ' (' . count($generated) . ' file di-generate)';
        }

        return redirect()->route('admin.menus.index')->with('success', $msg);
    }

    public function edit(AdminMenu $menu): View
    {
        $parentMenus = AdminMenu::roots()
            ->with('section')
            ->where('id', '!=', $menu->id)
            ->orderBy('order')
            ->get();
        $sections = MenuSection::ordered()->get();

        return view('admin.menu.edit', compact('menu', 'parentMenus', 'sections'));
    }

    public function update(Request $request, AdminMenu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:100'],
            'icon_gradient' => ['required', 'string', 'max:50'],
            'section_id' => ['required', 'exists:menu_sections,id'],
            'parent_id' => ['nullable', 'exists:admin_menus,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (isset($validated['parent_id']) && $validated['parent_id'] == $menu->id) {
            $validated['parent_id'] = null;
        }

        $menu->update($validated);

        // If this menu now has a parent, cleanup the parent's controller/views
        // because a parent menu is just a grouper, not a page
        if (! empty($validated['parent_id'])) {
            $parent = AdminMenu::find($validated['parent_id']);
            if ($parent) {
                $this->convertToParentMenu($parent);
            }
        }

        // If this menu itself has children, ensure it's treated as parent-only
        if ($menu->children()->count() > 0) {
            $this->convertToParentMenu($menu);
        }

        ActivityLog::record('updated', $menu, "Mengubah menu: {$menu->label}");

        return redirect()->route('admin.menus.index')->with('success', "Menu \"{$menu->label}\" berhasil diperbarui.");
    }

    public function destroy(AdminMenu $menu): RedirectResponse
    {
        if ($menu->is_system) {
            return redirect()->route('admin.menus.index')->with('error', 'Menu sistem tidak bisa dihapus.');
        }

        $label = $menu->label;
        $slug = $menu->slug;

        // Collect children slugs before cascade delete removes them
        $childSlugs = $menu->children()->pluck('slug')->toArray();

        $menu->delete();

        // Cleanup: hapus controller dan views yang di-scaffold (parent)
        $this->cleanupScaffoldedFiles($slug);

        // Cleanup: hapus controller dan views children
        foreach ($childSlugs as $childSlug) {
            $this->cleanupScaffoldedFiles($childSlug);
        }

        ActivityLog::record('deleted', null, "Menghapus menu: {$label}");

        return redirect()->route('admin.menus.index')->with('success', "Menu \"{$label}\" berhasil dihapus.");
    }

    /**
     * Remove scaffolded controller and view files for a menu slug.
     */
    private function cleanupScaffoldedFiles(string $slug): void
    {
        $studly = \Illuminate\Support\Str::studly($slug);

        // Hapus controller
        $controllerPath = app_path("Http/Controllers/Admin/{$studly}Controller.php");
        if (\Illuminate\Support\Facades\File::exists($controllerPath)) {
            \Illuminate\Support\Facades\File::delete($controllerPath);
        }

        // Hapus folder views
        $viewDir = resource_path("views/admin/{$slug}");
        if (\Illuminate\Support\Facades\File::isDirectory($viewDir)) {
            \Illuminate\Support\Facades\File::deleteDirectory($viewDir);
        }
    }

    /**
     * Convert a menu to parent-only: remove its route_name and cleanup scaffolded files.
     * A parent menu is just a grouper/dropdown, it doesn't have its own page.
     */
    private function convertToParentMenu(AdminMenu $menu): void
    {
        // Clear route_name so it won't be treated as a navigable page
        if ($menu->route_name) {
            $menu->update(['route_name' => null]);
        }

        // Remove controller and views
        $this->cleanupScaffoldedFiles($menu->slug);
    }

    /**
     * Reorder menus via AJAX.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:admin_menus,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->input('items') as $item) {
            AdminMenu::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
