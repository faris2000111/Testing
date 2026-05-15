<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TemplateResetCommand extends Command
{
    protected $signature = 'template:reset {--force : Skip confirmation}';
    protected $description = 'Reset template to fresh state for a new project. Removes scaffolded files and re-migrates.';

    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will DELETE all scaffolded controllers, views, and reset the database. Continue?')) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        $this->info('🔄 Resetting template...');

        // 1. Remove scaffolded controllers (non-system ones)
        $this->removeScaffoldedFiles();

        // 2. Fresh migrate + seed
        $this->call('migrate:fresh', ['--seed' => true]);

        // 3. Clear caches
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // 4. Remove uploaded files
        $storagePath = storage_path('app/public');
        if (File::isDirectory($storagePath)) {
            foreach (File::directories($storagePath) as $dir) {
                File::deleteDirectory($dir);
            }
            foreach (File::files($storagePath) as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file->getPathname());
                }
            }
        }

        $this->newLine();
        $this->info('✅ Template berhasil di-reset!');
        $this->info('   Login: username "admin", password "password"');

        return 0;
    }

    private function removeScaffoldedFiles(): void
    {
        $this->info('  Removing scaffolded controllers...');

        // System controllers that should NOT be deleted
        $systemControllers = [
            'AdminMenuController.php',
            'BackupController.php',
            'DashboardController.php',
            'PasswordController.php',
            'ProfileController.php',
            'RoleController.php',
            'SectionController.php',
            'SiteSettingController.php',
            'UserController.php',
        ];

        $controllerDir = app_path('Http/Controllers/Admin');
        if (File::isDirectory($controllerDir)) {
            foreach (File::files($controllerDir) as $file) {
                if (! in_array($file->getFilename(), $systemControllers)) {
                    File::delete($file->getPathname());
                    $this->line("    Deleted: {$file->getFilename()}");
                }
            }
        }

        $this->info('  Removing scaffolded views...');

        // System view folders that should NOT be deleted
        $systemViews = ['dashboard', 'menu', 'password', 'profile', 'roles', 'sections', 'settings', 'template', 'users'];

        $viewDir = resource_path('views/admin');
        if (File::isDirectory($viewDir)) {
            foreach (File::directories($viewDir) as $dir) {
                $folderName = basename($dir);
                if (! in_array($folderName, $systemViews)) {
                    File::deleteDirectory($dir);
                    $this->line("    Deleted: views/admin/{$folderName}/");
                }
            }
        }
    }
}
