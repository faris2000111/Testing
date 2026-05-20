<?php

namespace Database\Seeders;

use App\Models\AdminMenu;
use App\Models\MenuSection;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'label' => 'Administrator',
                'is_superadmin' => true,
            ]
        );

        // Create default admin user
        $user = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
            ]
        );

        // Ensure user has role assigned
        if (! $user->role_id) {
            $user->update(['role_id' => $adminRole->id]);
        }

        // Create default site settings
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'project_name' => 'My Project',
                'site_name' => 'My Website',
                'tagline' => 'Your awesome tagline here',
            ]
        );

        // Create sections and menus
        $this->seedSections();
        $this->seedMenus();

        // Assign all menus to admin role
        $allMenuIds = AdminMenu::pluck('id')->toArray();
        $adminRole->menus()->sync($allMenuIds);
    }

    private function seedSections(): void
    {
        if (MenuSection::count() > 0) {
            return;
        }

        MenuSection::create(['name' => 'Overview', 'order' => 1]);
        MenuSection::create(['name' => 'Testing', 'order' => 2]);
        MenuSection::create(['name' => 'Sistem', 'order' => 99]);
    }

    private function seedMenus(): void
    {
        if (AdminMenu::count() > 0) {
            return;
        }

        $overview = MenuSection::where('name', 'Overview')->first();
        $sistem = MenuSection::where('name', 'Sistem')->first();

        AdminMenu::create([
            'label' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'fa-gauge-high',
            'icon_gradient' => 'primary',
            'route_name' => 'admin.dashboard',
            'section_id' => $overview->id,
            'order' => 1,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Pengaturan',
            'slug' => 'settings',
            'icon' => 'fa-gear',
            'icon_gradient' => 'secondary',
            'route_name' => 'admin.settings.edit',
            'section_id' => $sistem->id,
            'order' => 1,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Menu Manager',
            'slug' => 'menus',
            'icon' => 'fa-bars',
            'icon_gradient' => 'info',
            'route_name' => 'admin.menus.index',
            'section_id' => $sistem->id,
            'order' => 2,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Section Manager',
            'slug' => 'sections',
            'icon' => 'fa-layer-group',
            'icon_gradient' => 'success',
            'route_name' => 'admin.sections.index',
            'section_id' => $sistem->id,
            'order' => 3,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Role Manager',
            'slug' => 'roles',
            'icon' => 'fa-shield-halved',
            'icon_gradient' => 'info',
            'route_name' => 'admin.roles.index',
            'section_id' => $sistem->id,
            'order' => 4,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'User Manager',
            'slug' => 'users',
            'icon' => 'fa-users',
            'icon_gradient' => 'primary',
            'route_name' => 'admin.users.index',
            'section_id' => $sistem->id,
            'order' => 5,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Ubah Password',
            'slug' => 'password',
            'icon' => 'fa-key',
            'icon_gradient' => 'warning',
            'route_name' => 'admin.password.edit',
            'section_id' => $sistem->id,
            'order' => 6,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Profil Saya',
            'slug' => 'profile',
            'icon' => 'fa-user',
            'icon_gradient' => 'primary',
            'route_name' => 'admin.profile.edit',
            'section_id' => $sistem->id,
            'order' => 7,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Backup Database',
            'slug' => 'backup',
            'icon' => 'fa-database',
            'icon_gradient' => 'dark',
            'route_name' => 'admin.backup.download',
            'section_id' => $sistem->id,
            'order' => 8,
            'is_system' => true,
        ]);

        // Testing section
        $testing = MenuSection::where('name', 'Testing')->first();

        if ($testing) {
            AdminMenu::create([
                'label' => 'Blackbox Testing',
                'slug' => 'blackbox-projects',
                'icon' => 'fa-vial',
                'icon_gradient' => 'info',
                'route_name' => 'admin.blackbox.projects.index',
                'section_id' => $testing->id,
                'order' => 1,
                'is_system' => true,
            ]);

            AdminMenu::create([
                'label' => 'Manual Testing',
                'slug' => 'manual-testing',
                'icon' => 'fa-clipboard-check',
                'icon_gradient' => 'success',
                'route_name' => 'admin.manual-testing.index',
                'section_id' => $testing->id,
                'order' => 2,
                'is_system' => true,
            ]);
        }
    }
}
