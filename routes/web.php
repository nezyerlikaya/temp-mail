<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BlogStudioController;
use App\Http\Controllers\Admin\LocaleLaunchController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\OperationsOverviewController;
use App\Http\Controllers\Admin\PageStudioController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UpdateCenterController;
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

        Route::get('update-center', [UpdateCenterController::class, 'index'])
            ->middleware('can:admin.update-center.view')
            ->name('admin.update-center.index');
        Route::post('update-center/check', [UpdateCenterController::class, 'check'])
            ->middleware('can:admin.update-center.check')
            ->name('admin.update-center.check');
        Route::post('update-center/install', [UpdateCenterController::class, 'install'])
            ->middleware('can:admin.update-center.install')
            ->name('admin.update-center.install');
        Route::post('update-center/manual-upload', [UpdateCenterController::class, 'uploadManual'])
            ->middleware('can:admin.update-center.manual-upload')
            ->name('admin.update-center.manual-upload');
        Route::post('update-center/rollback-readiness', [UpdateCenterController::class, 'rollback'])
            ->middleware('can:admin.update-center.rollback')
            ->name('admin.update-center.rollback-readiness');

        Route::get('locale-launch-center', [LocaleLaunchController::class, 'index'])
            ->middleware('can:admin.locale-launch-center.view')
            ->name('admin.locale-launch-center.index');
        Route::put('locale-launch-center', [LocaleLaunchController::class, 'update'])
            ->middleware('can:manage-localization')
            ->name('admin.locale-launch-center.update');
        Route::post('locale-launch-center/bulk', [LocaleLaunchController::class, 'bulk'])
            ->middleware('can:manage-localization')
            ->name('admin.locale-launch-center.bulk');
        Route::patch('locale-launch-center/{locale:locale}/status', [LocaleLaunchController::class, 'status'])
            ->middleware('can:admin.locale-launch-center.publish')
            ->name('admin.locale-launch-center.status');

        Route::get('page-studio', [PageStudioController::class, 'index'])
            ->middleware('can:admin.page-studio.view')
            ->name('admin.page-studio.index');
        Route::get('page-studio/create', [PageStudioController::class, 'create'])
            ->middleware('can:admin.page-studio.create')
            ->name('admin.page-studio.create');
        Route::post('page-studio', [PageStudioController::class, 'store'])
            ->middleware('can:admin.page-studio.create')
            ->name('admin.page-studio.store');
        Route::get('page-studio/{page}/edit', [PageStudioController::class, 'edit'])
            ->middleware('can:admin.page-studio.update')
            ->name('admin.page-studio.edit');
        Route::put('page-studio/{page}', [PageStudioController::class, 'update'])
            ->middleware('can:admin.page-studio.update')
            ->name('admin.page-studio.update');
        Route::get('page-studio/{page}/preview', [PageStudioController::class, 'preview'])
            ->middleware(['can:admin.page-studio.preview', 'signed'])
            ->name('admin.page-studio.preview');
        Route::post('page-studio/{page}/publish', [PageStudioController::class, 'publish'])
            ->middleware('can:admin.page-studio.publish')
            ->name('admin.page-studio.publish');
        Route::post('page-studio/{page}/hide', [PageStudioController::class, 'hide'])
            ->middleware('can:admin.page-studio.hide')
            ->name('admin.page-studio.hide');
        Route::post('page-studio/{page}/trash', [PageStudioController::class, 'trash'])
            ->middleware('can:admin.page-studio.trash')
            ->name('admin.page-studio.trash');
        Route::post('page-studio/{page}/restore', [PageStudioController::class, 'restore'])
            ->middleware('can:admin.page-studio.restore')
            ->name('admin.page-studio.restore');
        Route::delete('page-studio/{page}', [PageStudioController::class, 'destroy'])
            ->middleware('can:admin.page-studio.delete')
            ->name('admin.page-studio.destroy');

        Route::get('blog-studio', [BlogStudioController::class, 'index'])
            ->middleware('can:admin.blog-studio.view')
            ->name('admin.blog-studio.index');
        Route::post('blog-studio', [BlogStudioController::class, 'store'])
            ->middleware('can:admin.blog-studio.create')
            ->name('admin.blog-studio.store');
        Route::put('blog-studio/{blogPost}', [BlogStudioController::class, 'update'])
            ->middleware('can:admin.blog-studio.update')
            ->name('admin.blog-studio.update');

        Route::get('media-library', [MediaLibraryController::class, 'index'])
            ->middleware('can:admin.media-library.view')
            ->name('admin.media-library.index');
        Route::get('media-library/picker/search', [MediaLibraryController::class, 'picker'])
            ->middleware('can:admin.media-library.picker')
            ->name('admin.media-library.picker');
        Route::post('media-library', [MediaLibraryController::class, 'store'])
            ->middleware('can:admin.media-library.upload')
            ->name('admin.media-library.store');
        Route::post('media-library/usage', [MediaLibraryController::class, 'attachUsage'])
            ->middleware('can:admin.media-library.update')
            ->name('admin.media-library.usage.attach');
        Route::delete('media-library/usage', [MediaLibraryController::class, 'detachUsage'])
            ->middleware('can:admin.media-library.update')
            ->name('admin.media-library.usage.detach');
        Route::get('media-library/{mediaAsset}', [MediaLibraryController::class, 'edit'])
            ->middleware('can:admin.media-library.view')
            ->name('admin.media-library.edit');
        Route::put('media-library/{mediaAsset}', [MediaLibraryController::class, 'update'])
            ->middleware('can:admin.media-library.update-metadata')
            ->name('admin.media-library.update');
        Route::patch('media-library/{mediaAsset}/status', [MediaLibraryController::class, 'updateStatus'])
            ->middleware('can:admin.media-library.update')
            ->name('admin.media-library.status.update');
        Route::post('media-library/{mediaAsset}/trash', [MediaLibraryController::class, 'trash'])
            ->middleware('can:admin.media-library.trash')
            ->name('admin.media-library.trash');
        Route::post('media-library/{mediaAsset}/restore', [MediaLibraryController::class, 'restore'])
            ->middleware('can:admin.media-library.restore')
            ->name('admin.media-library.restore');
        Route::delete('media-library/{mediaAsset}', [MediaLibraryController::class, 'destroy'])
            ->middleware('can:admin.media-library.delete')
            ->name('admin.media-library.destroy');

        foreach (app(AdminNavigationRegistry::class)->groups() as $group) {
            foreach ($group['items'] as $item) {
                if (in_array($item['route'], ['dashboard', 'admin.people-identity.index', 'admin.roles-permissions.index', 'admin.author-profiles.index', 'admin.settings.index', 'admin.activity-audit-logs.index', 'admin.backups-health.index', 'admin.update-center.index', 'admin.locale-launch-center.index', 'admin.page-studio.index', 'admin.blog-studio.index', 'admin.media-library.index'], true)) {
                    continue;
                }

                Route::get($item['path'], PlaceholderController::class)
                    ->middleware('can:'.$item['permission'])
                    ->name($item['route']);
            }
        }
    });
