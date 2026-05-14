<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
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
    }
}
