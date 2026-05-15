<?php

namespace App\Providers;

use App\Models\AdminMenu;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AdminMenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Register dynamic routes from admin_menus table.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $this->registerDynamicRoutes();
        });
    }

    private function registerDynamicRoutes(): void
    {
        // Skip if table doesn't exist yet (fresh install / migration pending)
        try {
            if (! Schema::hasTable('admin_menus')) {
                return;
            }

            $menus = AdminMenu::where('is_active', true)
                ->whereNotNull('slug')
                ->where('is_system', false)
                ->get();
        } catch (\Exception) {
            return;
        }

        Route::prefix('admin')
            ->middleware(['web', 'auth', 'maintenance', 'menu.access'])
            ->name('admin.')
            ->group(function () use ($menus) {
                foreach ($menus as $menu) {
                    $slug = $menu->slug;
                    $controllerClass = $menu->getControllerClass();

                    // Skip if controller doesn't exist
                    if (! class_exists($controllerClass)) {
                        continue;
                    }

                    if ($menu->has_crud) {
                        // Full resource routes
                        Route::resource($slug, $controllerClass)
                            ->names("{$slug}")
                            ->parameters([$slug => 'id']);
                    } else {
                        // Index only
                        Route::get($slug, [$controllerClass, 'index'])->name("{$slug}.index");
                    }
                }
            });
    }
}
