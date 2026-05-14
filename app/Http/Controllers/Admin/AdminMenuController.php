<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminMenu;
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
            ->with(['children' => fn ($q) => $q->orderBy('order')])
            ->orderBy('section')
            ->orderBy('order')
            ->get();

        return view('admin.menu.index', compact('menus'));
    }

    public function create(): View
    {
        $parentMenus = AdminMenu::roots()->orderBy('section')->orderBy('order')->get();
        $sections = AdminMenu::distinct()->pluck('section')->filter()->values()->toArray();

        return view('admin.menu.create', compact('parentMenus', 'sections'));
    }

    public function store(Request $request, MenuScaffolder $scaffolder): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', 'unique:admin_menus,slug'],
            'icon' => ['required', 'string', 'max:100'],
            'icon_gradient' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:admin_menus,id'],
            'is_active' => ['nullable', 'boolean'],
            'has_crud' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['has_crud'] = $request->boolean('has_crud', false);
        $validated['order'] = AdminMenu::where('section', $validated['section'])->max('order') + 1;

        // Auto-set route name based on slug
        $validated['route_name'] = "admin.{$validated['slug']}.index";

        $menu = AdminMenu::create($validated);

        // Scaffold controller + views
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
            ->where('id', '!=', $menu->id)
            ->orderBy('section')
            ->orderBy('order')
            ->get();
        $sections = AdminMenu::distinct()->pluck('section')->filter()->values()->toArray();

        return view('admin.menu.edit', compact('menu', 'parentMenus', 'sections'));
    }

    public function update(Request $request, AdminMenu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:100'],
            'icon_gradient' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:admin_menus,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (isset($validated['parent_id']) && $validated['parent_id'] == $menu->id) {
            $validated['parent_id'] = null;
        }

        $menu->update($validated);

        ActivityLog::record('updated', $menu, "Mengubah menu: {$menu->label}");

        return redirect()->route('admin.menus.index')->with('success', "Menu \"{$menu->label}\" berhasil diperbarui.");
    }

    public function destroy(AdminMenu $menu): RedirectResponse
    {
        if ($menu->is_system) {
            return redirect()->route('admin.menus.index')->with('error', 'Menu sistem tidak bisa dihapus.');
        }

        $label = $menu->label;
        $menu->delete();

        ActivityLog::record('deleted', null, "Menghapus menu: {$label}");

        return redirect()->route('admin.menus.index')->with('success', "Menu \"{$label}\" berhasil dihapus.");
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
