<?php

use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// ─── Auth ───
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Admin Panel ───
Route::prefix('admin')->middleware(['auth', 'maintenance', 'menu.access'])->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', 'admin/dashboard');

    // Settings
    Route::get('/settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');

    // Password
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Menu Manager
    Route::resource('menus', AdminMenuController::class)->except(['show']);
    Route::post('/menus/reorder', [AdminMenuController::class, 'reorder'])->name('menus.reorder');
    Route::get('/menus/routes/list', [AdminMenuController::class, 'routes'])->name('menus.routes');

    // Section Manager
    Route::resource('sections', SectionController::class)->except(['show']);
    Route::post('/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');

    // Role Manager
    Route::resource('roles', RoleController::class)->except(['show']);

    // User Manager
    Route::resource('users', UserController::class)->except(['show']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Backup
    Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');

    // Impersonate
    Route::post('/impersonate/{user}', [ImpersonateController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate-stop', [ImpersonateController::class, 'stop'])->name('impersonate.stop');

    // Global Search
    Route::get('/search', [SearchController::class, 'search'])->name('search');
});
