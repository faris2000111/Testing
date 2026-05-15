<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use App\Models\MenuSection;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across menus, users, roles, and settings.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search menus
        $menus = AdminMenu::where('label', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->active()
            ->limit(5)
            ->get();

        foreach ($menus as $menu) {
            $url = $menu->resolveUrl();
            if ($url) {
                $results[] = [
                    'type' => 'menu',
                    'icon' => "fa {$menu->icon}",
                    'label' => $menu->label,
                    'description' => 'Menu',
                    'url' => $url,
                ];
            }
        }

        // Search users
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'type' => 'user',
                'icon' => 'fa fa-user',
                'label' => $user->name,
                'description' => "@{$user->username}" . ($user->role ? " · {$user->role->label}" : ''),
                'url' => route('admin.users.edit', $user),
            ];
        }

        // Search roles
        $roles = Role::where('label', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($roles as $role) {
            $results[] = [
                'type' => 'role',
                'icon' => 'fa fa-shield-halved',
                'label' => $role->label,
                'description' => 'Role',
                'url' => route('admin.roles.edit', $role),
            ];
        }

        // Search sections
        $sections = MenuSection::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($sections as $section) {
            $results[] = [
                'type' => 'section',
                'icon' => 'fa fa-layer-group',
                'label' => $section->name,
                'description' => 'Section',
                'url' => route('admin.sections.edit', $section),
            ];
        }

        // Quick links for settings
        $settingsKeywords = ['pengaturan', 'settings', 'seo', 'branding', 'logo', 'maintenance', 'ai', 'social'];
        foreach ($settingsKeywords as $keyword) {
            if (stripos($keyword, $query) !== false || stripos($query, $keyword) !== false) {
                $results[] = [
                    'type' => 'settings',
                    'icon' => 'fa fa-gear',
                    'label' => 'Pengaturan Website',
                    'description' => 'Settings',
                    'url' => route('admin.settings.edit'),
                ];
                break;
            }
        }

        return response()->json(['results' => array_slice($results, 0, 10)]);
    }
}
