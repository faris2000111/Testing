<?php

use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\AiTestGeneratorController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\ManualTestExecutionController;
use App\Http\Controllers\Admin\ManualTestingController;
use App\Http\Controllers\Admin\ManualTestScenarioController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TestCaseController;
use App\Http\Controllers\Admin\TestProjectController;
use App\Http\Controllers\Admin\TestRunnerController;
use App\Http\Controllers\Admin\TestSuiteController;
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

    // Blackbox Testing
    Route::prefix('blackbox')->name('blackbox.')->group(function () {
        Route::resource('projects', TestProjectController::class);
        Route::post('/projects/{project}/run', [TestRunnerController::class, 'run'])->name('projects.run');
        Route::get('/projects/{project}/runs/{run}', [TestRunnerController::class, 'show'])->name('projects.runs.show');
        Route::delete('/projects/{project}/runs/{run}', [TestRunnerController::class, 'destroy'])->name('projects.runs.destroy');
        Route::get('/projects/{project}/cases/create', [TestCaseController::class, 'create'])->name('projects.cases.create');
        Route::post('/projects/{project}/cases', [TestCaseController::class, 'store'])->name('projects.cases.store');
        Route::get('/projects/{project}/cases/{case}/edit', [TestCaseController::class, 'edit'])->name('projects.cases.edit');
        Route::put('/projects/{project}/cases/{case}', [TestCaseController::class, 'update'])->name('projects.cases.update');
        Route::delete('/projects/{project}/cases/{case}', [TestCaseController::class, 'destroy'])->name('projects.cases.destroy');

        // Test Suites
        Route::get('/projects/{project}/suites/create', [TestSuiteController::class, 'create'])->name('projects.suites.create');
        Route::post('/projects/{project}/suites', [TestSuiteController::class, 'store'])->name('projects.suites.store');
        Route::get('/projects/{project}/suites/{suite}/edit', [TestSuiteController::class, 'edit'])->name('projects.suites.edit');
        Route::put('/projects/{project}/suites/{suite}', [TestSuiteController::class, 'update'])->name('projects.suites.update');
        Route::delete('/projects/{project}/suites/{suite}', [TestSuiteController::class, 'destroy'])->name('projects.suites.destroy');
    });

    // Manual Testing
    Route::prefix('manual-testing')->name('manual-testing.')->group(function () {
        // Landing page
        Route::get('/', [ManualTestingController::class, 'index'])->name('index');

        // Scenarios
        Route::get('/projects/{project}/scenarios', [ManualTestScenarioController::class, 'index'])->name('scenarios.index');
        Route::get('/projects/{project}/scenarios/create', [ManualTestScenarioController::class, 'create'])->name('scenarios.create');
        Route::post('/projects/{project}/scenarios', [ManualTestScenarioController::class, 'store'])->name('scenarios.store');
        Route::get('/projects/{project}/scenarios/{scenario}', [ManualTestScenarioController::class, 'show'])->name('scenarios.show');
        Route::get('/projects/{project}/scenarios/{scenario}/edit', [ManualTestScenarioController::class, 'edit'])->name('scenarios.edit');
        Route::put('/projects/{project}/scenarios/{scenario}', [ManualTestScenarioController::class, 'update'])->name('scenarios.update');
        Route::delete('/projects/{project}/scenarios/{scenario}', [ManualTestScenarioController::class, 'destroy'])->name('scenarios.destroy');

        // Executions
        Route::get('/projects/{project}/executions', [ManualTestExecutionController::class, 'index'])->name('executions.index');
        Route::get('/projects/{project}/executions/create', [ManualTestExecutionController::class, 'create'])->name('executions.create');
        Route::post('/projects/{project}/executions', [ManualTestExecutionController::class, 'store'])->name('executions.store');
        Route::get('/projects/{project}/executions/{execution}', [ManualTestExecutionController::class, 'show'])->name('executions.show');
        Route::get('/projects/{project}/executions/{execution}/execute', [ManualTestExecutionController::class, 'execute'])->name('executions.execute');
        Route::post('/projects/{project}/executions/{execution}/step-result', [ManualTestExecutionController::class, 'updateStepResult'])->name('executions.step-result');
        Route::post('/projects/{project}/executions/{execution}/scenario-result', [ManualTestExecutionController::class, 'updateScenarioResult'])->name('executions.scenario-result');
        Route::post('/projects/{project}/executions/{execution}/complete', [ManualTestExecutionController::class, 'complete'])->name('executions.complete');
        Route::delete('/projects/{project}/executions/{execution}', [ManualTestExecutionController::class, 'destroy'])->name('executions.destroy');
    });

    // Reports (Print/PDF)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/blackbox/{project}/runs/{run}', [ReportController::class, 'blackboxRun'])->name('blackbox-run');
        Route::get('/manual/{project}/executions/{execution}', [ReportController::class, 'manualExecution'])->name('manual-execution');
    });

    // AI Test Generator
    Route::post('/ai/generate-scenario', [AiTestGeneratorController::class, 'generate'])->name('ai.generate-scenario');
    Route::post('/ai/generate-blackbox', [AiTestGeneratorController::class, 'generateBlackbox'])->name('ai.generate-blackbox');
});


