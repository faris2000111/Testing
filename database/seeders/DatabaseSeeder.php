<?php

namespace Database\Seeders;

use App\Models\AdminMenu;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
            ]
        );

        // Create default site settings
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'project_name' => 'My Project',
                'site_name' => 'My Website',
                'tagline' => 'Your awesome tagline here',
            ]
        );

        // Create default admin menus
        $this->seedMenus();
    }

    private function seedMenus(): void
    {
        if (AdminMenu::count() > 0) {
            return;
        }

        AdminMenu::create([
            'label' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'fa-gauge-high',
            'icon_gradient' => 'primary',
            'route_name' => 'admin.dashboard',
            'section' => 'Overview',
            'order' => 1,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Pengaturan',
            'slug' => 'settings',
            'icon' => 'fa-gear',
            'icon_gradient' => 'secondary',
            'route_name' => 'admin.settings.edit',
            'section' => 'Sistem',
            'order' => 1,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Menu Manager',
            'slug' => 'menus',
            'icon' => 'fa-bars',
            'icon_gradient' => 'info',
            'route_name' => 'admin.menus.index',
            'section' => 'Sistem',
            'order' => 2,
            'is_system' => true,
        ]);

        AdminMenu::create([
            'label' => 'Ubah Password',
            'slug' => 'password',
            'icon' => 'fa-key',
            'icon_gradient' => 'warning',
            'route_name' => 'admin.password.edit',
            'section' => 'Sistem',
            'order' => 3,
            'is_system' => true,
        ]);
    }
}
