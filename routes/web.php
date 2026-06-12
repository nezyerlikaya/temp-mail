<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BlogStudioController;
use App\Http\Controllers\Admin\BlogTaxonomyController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\LocaleLaunchController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperationsOverviewController;
use App\Http\Controllers\Admin\PageStudioController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\SectionsStudioController;
use App\Http\Controllers\Admin\SeoGrowthCenterController;
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

        Route::get('notifications', [NotificationController::class, 'index'])
            ->middleware('can:admin.notifications.view')
            ->name('admin.notifications.index');
        Route::get('notifications/{systemNotification}', [NotificationController::class, 'show'])
            ->middleware('can:view notification,systemNotification')
            ->name('admin.notifications.show');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
            ->middleware('can:mark notification')
            ->name('admin.notifications.mark-all-read');
        Route::put('notifications/rules', [NotificationController::class, 'updateRules'])
            ->middleware('can:update notification rules')
            ->name('admin.notifications.rules.update');
        Route::post('notifications/{systemNotification}/mark-read', [NotificationController::class, 'markRead'])
            ->middleware('can:mark notification,systemNotification')
            ->name('admin.notifications.mark-read');
        Route::post('notifications/{systemNotification}/snooze', [NotificationController::class, 'snooze'])
            ->middleware('can:snooze notifications,systemNotification')
            ->name('admin.notifications.snooze');
        Route::post('notifications/{systemNotification}/archive', [NotificationController::class, 'archive'])
            ->middleware('can:archive notification,systemNotification')
            ->name('admin.notifications.archive');

        Route::get('email-templates', [EmailTemplateController::class, 'index'])
            ->middleware('can:admin.email-templates.view')
            ->name('admin.email-templates.index');
        Route::get('email-templates/create', [EmailTemplateController::class, 'create'])
            ->middleware('can:admin.email-templates.create')
            ->name('admin.email-templates.create');
        Route::post('email-templates', [EmailTemplateController::class, 'store'])
            ->middleware('can:admin.email-templates.create')
            ->name('admin.email-templates.store');
        Route::get('email-templates/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])
            ->middleware('can:admin.email-templates.update')
            ->name('admin.email-templates.edit');
        Route::put('email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])
            ->middleware('can:admin.email-templates.update')
            ->name('admin.email-templates.update');
        Route::post('email-templates/{emailTemplate}/send-test', [EmailTemplateController::class, 'sendTest'])
            ->middleware('can:admin.email-templates.send-test')
            ->name('admin.email-templates.send-test');
        Route::post('email-templates/{emailTemplate}/status', [EmailTemplateController::class, 'status'])
            ->middleware('can:admin.email-templates.activate')
            ->name('admin.email-templates.status');
        Route::post('email-templates/{emailTemplate}/reset-readiness', [EmailTemplateController::class, 'reset'])
            ->middleware('can:admin.email-templates.reset')
            ->name('admin.email-templates.reset');

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
        Route::get('blog-studio/create', [BlogStudioController::class, 'create'])
            ->middleware('can:admin.blog-studio.create')
            ->name('admin.blog-studio.create');
        Route::post('blog-studio', [BlogStudioController::class, 'store'])
            ->middleware('can:admin.blog-studio.create')
            ->name('admin.blog-studio.store');
        Route::get('blog-studio/{blogPost}/edit', [BlogStudioController::class, 'edit'])
            ->middleware('can:admin.blog-studio.update')
            ->name('admin.blog-studio.edit');
        Route::put('blog-studio/{blogPost}', [BlogStudioController::class, 'update'])
            ->middleware('can:admin.blog-studio.update')
            ->name('admin.blog-studio.update');
        Route::get('blog-studio/{blogPost}/preview', [BlogStudioController::class, 'preview'])
            ->middleware(['can:admin.blog-studio.preview', 'signed'])
            ->name('admin.blog-studio.preview');
        Route::post('blog-studio/{blogPost}/publish', [BlogStudioController::class, 'publish'])
            ->middleware('can:admin.blog-studio.publish')
            ->name('admin.blog-studio.publish');
        Route::post('blog-studio/{blogPost}/hide', [BlogStudioController::class, 'hide'])
            ->middleware('can:admin.blog-studio.hide')
            ->name('admin.blog-studio.hide');
        Route::post('blog-studio/{blogPost}/trash', [BlogStudioController::class, 'trash'])
            ->middleware('can:admin.blog-studio.trash')
            ->name('admin.blog-studio.trash');
        Route::post('blog-studio/{blogPost}/restore', [BlogStudioController::class, 'restore'])
            ->middleware('can:admin.blog-studio.restore')
            ->name('admin.blog-studio.restore');
        Route::delete('blog-studio/{blogPost}', [BlogStudioController::class, 'destroy'])
            ->middleware('can:admin.blog-studio.delete')
            ->name('admin.blog-studio.destroy');

        Route::get('taxonomy', [BlogTaxonomyController::class, 'index'])
            ->middleware('can:admin.taxonomy.view')
            ->name('admin.taxonomy.index');
        Route::post('taxonomy/categories', [BlogTaxonomyController::class, 'storeCategory'])
            ->middleware('can:admin.taxonomy.create')
            ->name('admin.taxonomy.categories.store');
        Route::put('taxonomy/categories/{blogCategory}', [BlogTaxonomyController::class, 'updateCategory'])
            ->middleware('can:admin.taxonomy.update')
            ->name('admin.taxonomy.categories.update');
        Route::post('taxonomy/tags', [BlogTaxonomyController::class, 'storeTag'])
            ->middleware('can:admin.taxonomy.create')
            ->name('admin.taxonomy.tags.store');
        Route::put('taxonomy/tags/{blogTag}', [BlogTaxonomyController::class, 'updateTag'])
            ->middleware('can:admin.taxonomy.update')
            ->name('admin.taxonomy.tags.update');

        Route::get('sections-studio', [SectionsStudioController::class, 'index'])
            ->middleware('can:admin.sections-studio.view')
            ->name('admin.sections-studio.index');
        Route::get('sections-studio/create', [SectionsStudioController::class, 'create'])
            ->middleware('can:admin.sections-studio.create')
            ->name('admin.sections-studio.create');
        Route::post('sections-studio', [SectionsStudioController::class, 'store'])
            ->middleware('can:admin.sections-studio.create')
            ->name('admin.sections-studio.store');
        Route::post('sections-studio/reorder', [SectionsStudioController::class, 'reorder'])
            ->middleware('can:admin.sections-studio.reorder')
            ->name('admin.sections-studio.reorder');
        Route::get('sections-studio/{section}/edit', [SectionsStudioController::class, 'edit'])
            ->middleware('can:admin.sections-studio.update')
            ->name('admin.sections-studio.edit');
        Route::put('sections-studio/{section}', [SectionsStudioController::class, 'update'])
            ->middleware('can:admin.sections-studio.update')
            ->name('admin.sections-studio.update');
        Route::get('sections-studio/{section}/preview', [SectionsStudioController::class, 'preview'])
            ->middleware(['can:admin.sections-studio.preview', 'signed'])
            ->name('admin.sections-studio.preview');
        Route::post('sections-studio/{section}/activate', [SectionsStudioController::class, 'activate'])
            ->middleware('can:admin.sections-studio.activate')
            ->name('admin.sections-studio.activate');
        Route::post('sections-studio/{section}/hide', [SectionsStudioController::class, 'hide'])
            ->middleware('can:admin.sections-studio.hide')
            ->name('admin.sections-studio.hide');
        Route::post('sections-studio/{section}/trash', [SectionsStudioController::class, 'trash'])
            ->middleware('can:admin.sections-studio.trash')
            ->name('admin.sections-studio.trash');
        Route::post('sections-studio/{section}/restore', [SectionsStudioController::class, 'restore'])
            ->middleware('can:admin.sections-studio.restore')
            ->name('admin.sections-studio.restore');
        Route::delete('sections-studio/{section}', [SectionsStudioController::class, 'destroy'])
            ->middleware('can:admin.sections-studio.delete')
            ->name('admin.sections-studio.destroy');
        Route::post('sections-studio/{section}/items', [SectionsStudioController::class, 'storeItem'])
            ->middleware('can:admin.sections-studio.items.update')
            ->name('admin.sections-studio.items.store');
        Route::post('sections-studio/{section}/items/reorder', [SectionsStudioController::class, 'reorderItems'])
            ->middleware('can:admin.sections-studio.items.update')
            ->name('admin.sections-studio.items.reorder');
        Route::put('sections-studio/{section}/items/{sectionItem}', [SectionsStudioController::class, 'updateItem'])
            ->middleware('can:admin.sections-studio.items.update')
            ->name('admin.sections-studio.items.update');
        Route::delete('sections-studio/{section}/items/{sectionItem}', [SectionsStudioController::class, 'destroyItem'])
            ->middleware('can:admin.sections-studio.items.update')
            ->name('admin.sections-studio.items.destroy');

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

        Route::get('seo-growth-center', [SeoGrowthCenterController::class, 'index'])
            ->middleware('can:admin.seo-growth-center.view')
            ->name('admin.seo-growth-center.index');
        Route::post('seo-growth-center/diagnostics', [SeoGrowthCenterController::class, 'runDiagnostics'])
            ->middleware('can:admin.seo-growth-center.diagnostics')
            ->name('admin.seo-growth-center.diagnostics.run');
        Route::post('seo-growth-center/templates', [SeoGrowthCenterController::class, 'saveTemplate'])
            ->middleware('can:admin.seo-growth-center.templates')
            ->name('admin.seo-growth-center.templates.save');
        Route::post('seo-growth-center/redirects', [SeoGrowthCenterController::class, 'storeRedirect'])
            ->middleware('can:admin.seo-growth-center.redirects')
            ->name('admin.seo-growth-center.redirects.store');
        Route::put('seo-growth-center/redirects/{seoRedirect}', [SeoGrowthCenterController::class, 'updateRedirect'])
            ->middleware('can:admin.seo-growth-center.redirects')
            ->name('admin.seo-growth-center.redirects.update');
        Route::post('seo-growth-center/versions/{seoVersion}/rollback', [SeoGrowthCenterController::class, 'rollback'])
            ->middleware('can:admin.seo-growth-center.rollback')
            ->name('admin.seo-growth-center.versions.rollback');
        Route::get('seo-growth-center/create', [SeoGrowthCenterController::class, 'create'])
            ->middleware('can:admin.seo-growth-center.update')
            ->name('admin.seo-growth-center.records.create');
        Route::post('seo-growth-center/records', [SeoGrowthCenterController::class, 'ensure'])
            ->middleware('can:admin.seo-growth-center.update')
            ->name('admin.seo-growth-center.records.ensure');
        Route::get('seo-growth-center/records/{seoRecord}/edit', [SeoGrowthCenterController::class, 'edit'])
            ->middleware('can:admin.seo-growth-center.update')
            ->name('admin.seo-growth-center.records.edit');
        Route::put('seo-growth-center/records/{seoRecord}', [SeoGrowthCenterController::class, 'update'])
            ->middleware('can:admin.seo-growth-center.update')
            ->name('admin.seo-growth-center.records.update');
        Route::post('seo-growth-center/records/{seoRecord}/preview', [SeoGrowthCenterController::class, 'preview'])
            ->middleware('can:admin.seo-growth-center.preview')
            ->name('admin.seo-growth-center.records.preview');

        foreach (app(AdminNavigationRegistry::class)->groups() as $group) {
            foreach ($group['items'] as $item) {
                if (in_array($item['route'], ['dashboard', 'admin.people-identity.index', 'admin.roles-permissions.index', 'admin.author-profiles.index', 'admin.settings.index', 'admin.activity-audit-logs.index', 'admin.backups-health.index', 'admin.update-center.index', 'admin.notifications.index', 'admin.email-templates.index', 'admin.locale-launch-center.index', 'admin.page-studio.index', 'admin.blog-studio.index', 'admin.taxonomy.index', 'admin.sections-studio.index', 'admin.media-library.index', 'admin.seo-growth-center.index'], true)) {
                    continue;
                }

                Route::get($item['path'], PlaceholderController::class)
                    ->middleware('can:'.$item['permission'])
                    ->name($item['route']);
            }
        }
    });
