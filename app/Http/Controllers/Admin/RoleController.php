<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminMenu;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $menus = AdminMenu::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('order'), 'section'])
            ->get()
            ->sortBy(fn ($m) => ($m->section->order ?? 0) * 10000 + $m->order);

        return view('admin.roles.create', compact('menus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9\-_]+$/', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:100'],
            'is_superadmin' => ['nullable', 'boolean'],
            'menus' => ['nullable', 'array'],
            'menus.*' => ['exists:admin_menus,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'label' => $validated['label'],
            'is_superadmin' => $request->boolean('is_superadmin', false),
        ]);

        // If superadmin, assign all menus. Otherwise assign selected menus.
        if ($role->is_superadmin) {
            $allMenuIds = AdminMenu::pluck('id')->toArray();
            $role->syncMenus($allMenuIds);
        } else {
            $role->syncMenus($validated['menus'] ?? []);
        }

        ActivityLog::record('created', $role, "Menambah role: {$role->label}");

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->label}\" berhasil ditambahkan.");
    }

    public function edit(Role $role): View
    {
        $menus = AdminMenu::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('order'), 'section'])
            ->get()
            ->sortBy(fn ($m) => ($m->section->order ?? 0) * 10000 + $m->order);

        $assignedMenuIds = $role->menus()->pluck('admin_menu_id')->toArray();

        return view('admin.roles.edit', compact('role', 'menus', 'assignedMenuIds'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'is_superadmin' => ['nullable', 'boolean'],
            'menus' => ['nullable', 'array'],
            'menus.*' => ['exists:admin_menus,id'],
        ]);

        $role->update([
            'label' => $validated['label'],
            'is_superadmin' => $request->boolean('is_superadmin', false),
        ]);

        if ($role->is_superadmin) {
            $allMenuIds = AdminMenu::pluck('id')->toArray();
            $role->syncMenus($allMenuIds);
        } else {
            $role->syncMenus($validated['menus'] ?? []);
        }

        ActivityLog::record('updated', $role, "Mengubah role: {$role->label}");

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->label}\" berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_superadmin) {
            return redirect()->route('admin.roles.index')->with('error', 'Role superadmin tidak bisa dihapus.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Role masih digunakan oleh user. Pindahkan user terlebih dahulu.');
        }

        $label = $role->label;
        $role->delete();

        ActivityLog::record('deleted', null, "Menghapus role: {$label}");

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$label}\" berhasil dihapus.");
    }
}
