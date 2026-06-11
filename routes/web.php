<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\OperationsOverviewController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Users\AuthorProfileController;
use App\Http\Controllers\Admin\Users\RolePermissionController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InstallerController;
use App\Models\User;
use App\Services\Admin\AdminNavigationRegistry;
use App\Services\Installer\InstallState;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return app(InstallState::class)->isInstalled()
        ? redirect()->route('login')
        : redirect()->route('install.readiness');
})->name('home');

Route::prefix('install')->name('install.')->group(function (): void {
    Route::get('/', [InstallerController::class, 'readiness'])->name('readiness');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'storeDatabase'])->name('database.store');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'storeAdmin'])->name('admin.store');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');

    Route::get('/register', function () {
        abort_unless((bool) config('auth.registration_enabled', false), 404);

        return view('auth.register');
    })->name('register');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('dashboard')
    ->middleware(['auth', 'can:access-admin'])
    ->group(function (): void {
        Route::get('/', OperationsOverviewController::class)->name('dashboard');

        Route::get('people-identity', [UserController::class, 'index'])
            ->middleware('can:viewAny,'.User::class)
            ->name('admin.people-identity.index');
        Route::get('people-identity/{user}', [UserController::class, 'show'])
            ->middleware('can:view,user')
            ->name('admin.people-identity.show');
        Route::get('people-identity/{user}/edit', [UserController::class, 'edit'])
            ->middleware('can:update,user')
            ->name('admin.people-identity.edit');
        Route::put('people-identity/{user}', [UserController::class, 'update'])
            ->middleware('can:update,user')
            ->name('admin.people-identity.update');

        Route::get('roles-permissions', [RolePermissionController::class, 'index'])
            ->middleware('can:admin.roles-permissions.view')
            ->name('admin.roles-permissions.index');
        Route::patch('roles-permissions/{user}', [RolePermissionController::class, 'update'])
            ->middleware('can:admin.roles-permissions.manage')
            ->name('admin.roles-permissions.update');

        Route::get('author-profiles', [AuthorProfileController::class, 'index'])
            ->middleware('can:admin.author-profiles.view')
            ->name('admin.author-profiles.index');
        Route::get('author-profiles/{user}/edit', [AuthorProfileController::class, 'edit'])
            ->middleware('can:updateAuthorProfile,user')
            ->name('admin.author-profiles.edit');
        Route::put('author-profiles/{user}', [AuthorProfileController::class, 'update'])
            ->middleware('can:updateAuthorProfile,user')
            ->name('admin.author-profiles.update');
        Route::patch('people-identity/{user}/avatar', [AuthorProfileController::class, 'updateAvatar'])
            ->middleware('can:updateAvatar,user')
            ->name('admin.people-identity.avatar.update');

        Route::get('settings', [SettingsController::class, 'index'])
            ->middleware('can:admin.settings.view')
            ->name('admin.settings.index');
        Route::put('settings/general', [SettingsController::class, 'updateGeneral'])->middleware('can:admin.settings.manage')->name('admin.settings.general.update');
        Route::put('settings/brand', [SettingsController::class, 'updateBrand'])->middleware('can:admin.settings.manage')->name('admin.settings.brand.update');
        Route::put('settings/localization', [SettingsController::class, 'updateLocalization'])->middleware('can:admin.settings.manage')->name('admin.settings.localization.update');
        Route::put('settings/maintenance', [SettingsController::class, 'updateMaintenance'])->middleware('can:admin.settings.manage')->name('admin.settings.maintenance.update');
        Route::put('settings/legal', [SettingsController::class, 'updateLegal'])->middleware('can:admin.settings.manage')->name('admin.settings.legal.update');
        Route::delete('settings/{group}', [SettingsController::class, 'reset'])->middleware('can:admin.settings.manage')->name('admin.settings.reset');

        Route::get('activity-audit-logs', [AuditLogController::class, 'index'])
            ->middleware('can:admin.activity-audit-logs.view')
            ->name('admin.activity-audit-logs.index');
        Route::get('activity-audit-logs/export', [AuditLogController::class, 'export'])
            ->middleware('can:admin.activity-audit-logs.export')
            ->name('admin.activity-audit-logs.export');
        Route::put('activity-audit-logs/retention', [AuditLogController::class, 'updateRetention'])
            ->middleware('can:admin.activity-audit-logs.manage-retention')
            ->name('admin.activity-audit-logs.retention.update');

        Route::get('backups-health', [BackupController::class, 'index'])
            ->middleware('can:admin.backups-health.view')
            ->name('admin.backups-health.index');
        Route::post('backups-health', [BackupController::class, 'store'])
            ->middleware('can:admin.backups-health.create')
            ->name('admin.backups-health.store');
        Route::post('backups-health/health-check', [BackupController::class, 'runHealthCheck'])
            ->middleware('can:admin.backups-health.run-health')
            ->name('admin.backups-health.health-check.run');
        Route::get('backups-health/{backup}/download', [BackupController::class, 'download'])
            ->middleware('can:admin.backups-health.download')
            ->name('admin.backups-health.download');
        Route::delete('backups-health/{backup}', [BackupController::class, 'destroy'])
            ->middleware('can:admin.backups-health.delete')
            ->name('admin.backups-health.destroy');

        foreach (app(AdminNavigationRegistry::class)->groups() as $group) {
            foreach ($group['items'] as $item) {
                if (in_array($item['route'], ['dashboard', 'admin.people-identity.index', 'admin.roles-permissions.index', 'admin.author-profiles.index', 'admin.settings.index', 'admin.activity-audit-logs.index', 'admin.backups-health.index'], true)) {
                    continue;
                }

                Route::get($item['path'], PlaceholderController::class)
                    ->middleware('can:'.$item['permission'])
                    ->name($item['route']);
            }
        }
    });
