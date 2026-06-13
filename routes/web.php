<?php

use App\Http\Controllers\AbuseReportSubmissionController;
use App\Http\Controllers\Admin\AbuseReportController;
use App\Http\Controllers\Admin\ApiAccessController;
use App\Http\Controllers\Admin\AppearanceStudioController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BlockedListController;
use App\Http\Controllers\Admin\BlogStudioController;
use App\Http\Controllers\Admin\BlogTaxonomyController;
use App\Http\Controllers\Admin\CommentModerationController;
use App\Http\Controllers\Admin\DashboardLiveMetricsController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\InboundMailConnectionController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\LocaleLaunchController;
use App\Http\Controllers\Admin\MailboxOperationsController;
use App\Http\Controllers\Admin\MailboxRulesController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperationsOverviewController;
use App\Http\Controllers\Admin\PageStudioController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\PlanMembershipController;
use App\Http\Controllers\Admin\ProductAnalyticsController;
use App\Http\Controllers\Admin\SectionsStudioController;
use App\Http\Controllers\Admin\SecurityDefenseController;
use App\Http\Controllers\Admin\SeoGrowthCenterController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SmtpConnectionController;
use App\Http\Controllers\Admin\ThemeLaunchController;
use App\Http\Controllers\Admin\TranslationCenterController;
use App\Http\Controllers\Admin\TypographyCenterController;
use App\Http\Controllers\Admin\UpdateCenterController;
use App\Http\Controllers\Admin\Users\AuthorProfileController;
use App\Http\Controllers\Admin\Users\RolePermissionController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CommentSubmissionController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\PublicBlogController;
use App\Http\Controllers\PublicEntryController;
use App\Http\Controllers\PublicMailboxController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PublicSitemapController;
use App\Models\User;
use App\Services\Admin\AdminNavigationRegistry;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicEntryController::class)->name('home');

Route::get('/{locale}', PublicSiteController::class)
    ->where('locale', '[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})?')
    ->middleware(['public.locale', 'public.locale.active', 'public.theme', 'public.direction'])
    ->name('public.home');

Route::get('/sitemap.xml', PublicSitemapController::class)->name('public.sitemap');

Route::prefix('{locale}')
    ->where(['locale' => '[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})?'])
    ->middleware(['public.locale', 'public.locale.active', 'public.theme', 'public.direction'])
    ->group(function (): void {
        Route::get('blog', [PublicBlogController::class, 'index'])->name('public.blog.index');
        Route::get('blog/category/{slug}', [PublicBlogController::class, 'category'])->name('public.blog.category');
        Route::get('blog/tag/{slug}', [PublicBlogController::class, 'tag'])->name('public.blog.tag');
        Route::get('blog/author/{slug}', [PublicBlogController::class, 'author'])->name('public.blog.author');
        Route::get('blog/{slug}', [PublicBlogController::class, 'show'])->name('public.blog.show');
        Route::post('blog/{post:slug}/comments', [CommentSubmissionController::class, 'storeLocalized'])
            ->middleware(['public.content.published', 'public.comments.available', 'throttle:comments'])
            ->name('public.blog.comments.store');
        Route::post('mailboxes', [PublicMailboxController::class, 'store'])
            ->middleware('throttle:mailbox_creation')
            ->name('public.mailbox.store');
        Route::get('mailboxes/{mailbox}', [PublicMailboxController::class, 'show'])
            ->middleware('public.mailbox.access')
            ->name('public.mailbox.show');
        Route::post('mailboxes/{mailbox}/refresh', [PublicMailboxController::class, 'refresh'])
            ->middleware(['public.mailbox.access', 'throttle:inbox_refresh'])
            ->name('public.mailbox.refresh');
        Route::get('mailboxes/{mailbox}/messages/{message}', [PublicMailboxController::class, 'message'])
            ->middleware('public.mailbox.access')
            ->name('public.mailbox.messages.show');
        Route::get('{slug}', [PublicPageController::class, 'show'])->name('public.pages.show');
    });

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

Route::get('/report-abuse', [AbuseReportSubmissionController::class, 'create'])->name('abuse-report.create');
Route::post('/report-abuse', [AbuseReportSubmissionController::class, 'store'])
    ->middleware('throttle:abuse_reports')
    ->name('abuse-report.store');

Route::prefix('dashboard')
    ->middleware(['auth', 'can:access-admin'])
    ->group(function (): void {
        Route::get('/', OperationsOverviewController::class)
            ->middleware('can:admin.operations.view')
            ->name('dashboard');
        Route::get('live-metrics', DashboardLiveMetricsController::class)
            ->middleware('can:view live metrics')
            ->name('admin.dashboard.live-metrics');
        Route::get('product-analytics', [ProductAnalyticsController::class, 'index'])
            ->middleware('can:view analytics')
            ->name('admin.product-analytics.index');
        Route::get('product-analytics/export', [ProductAnalyticsController::class, 'export'])
            ->middleware('can:export analytics')
            ->name('admin.product-analytics.export');
        Route::get('theme-launch-center', [ThemeLaunchController::class, 'index'])
            ->middleware('can:admin.theme-launch-center.view')
            ->name('admin.theme-launch-center.index');
        Route::post('theme-launch-center/activate', [ThemeLaunchController::class, 'activate'])
            ->middleware('can:activate theme')
            ->name('admin.theme-launch-center.activate');
        Route::get('appearance-studio', [AppearanceStudioController::class, 'index'])
            ->middleware('can:view appearance')
            ->name('admin.appearance-studio.index');
        Route::get('appearance-studio/preview', [AppearanceStudioController::class, 'preview'])
            ->middleware(['signed', 'can:preview appearance'])
            ->name('admin.appearance-studio.preview');
        Route::put('appearance-studio', [AppearanceStudioController::class, 'update'])
            ->middleware('can:update appearance')
            ->name('admin.appearance-studio.update');
        Route::post('appearance-studio/publish', [AppearanceStudioController::class, 'publish'])
            ->middleware('can:publish appearance')
            ->name('admin.appearance-studio.publish');
        Route::post('appearance-studio/rollback', [AppearanceStudioController::class, 'rollback'])
            ->middleware('can:rollback appearance')
            ->name('admin.appearance-studio.rollback');
        Route::post('appearance-studio/reset', [AppearanceStudioController::class, 'reset'])
            ->middleware('can:reset appearance')
            ->name('admin.appearance-studio.reset');
        Route::post('appearance-studio/reset-token', [AppearanceStudioController::class, 'resetToken'])
            ->middleware('can:reset appearance')
            ->name('admin.appearance-studio.reset-token');
        Route::get('typography-center', [TypographyCenterController::class, 'index'])
            ->middleware('can:view typography')
            ->name('admin.typography-center.index');
        Route::put('typography-center/families/{fontFamily:slug}', [TypographyCenterController::class, 'updateFamily'])
            ->middleware('can:manage font families')
            ->name('admin.typography-center.families.update');
        Route::post('typography-center/families/{fontFamily:slug}/activate', [TypographyCenterController::class, 'activate'])
            ->middleware('can:manage font families')
            ->name('admin.typography-center.families.activate');
        Route::post('typography-center/families/{fontFamily:slug}/deactivate', [TypographyCenterController::class, 'deactivate'])
            ->middleware('can:manage font families')
            ->name('admin.typography-center.families.deactivate');
        Route::put('typography-center/assignments', [TypographyCenterController::class, 'updateAssignments'])
            ->middleware('can:manage font assignments')
            ->name('admin.typography-center.assignments.update');
        Route::post('typography-center/locales/{locale:locale}/reset', [TypographyCenterController::class, 'resetLocaleOverride'])
            ->middleware('can:reset locale font override')
            ->name('admin.typography-center.locales.reset');

        Route::get('mailbox-operations', [MailboxOperationsController::class, 'index'])
            ->middleware('can:view mailboxes')
            ->name('admin.mailbox-operations.index');
        Route::get('mailbox-operations/create', [MailboxOperationsController::class, 'create'])
            ->middleware('can:create mailbox readiness')
            ->name('admin.mailbox-operations.create');
        Route::post('mailbox-operations', [MailboxOperationsController::class, 'store'])
            ->middleware('can:create mailbox readiness')
            ->name('admin.mailbox-operations.store');
        Route::get('mailbox-operations/{mailbox}', [MailboxOperationsController::class, 'show'])
            ->middleware('can:view mailbox')
            ->name('admin.mailbox-operations.show');
        Route::get('mailbox-operations/{mailbox}/messages/{message}', [MailboxOperationsController::class, 'message'])
            ->middleware('can:view message content')
            ->name('admin.mailbox-operations.messages.show');
        Route::post('mailbox-operations/{mailbox}/expire', [MailboxOperationsController::class, 'expire'])
            ->middleware('can:expire mailbox')->name('admin.mailbox-operations.expire');
        Route::post('mailbox-operations/{mailbox}/lock', [MailboxOperationsController::class, 'lock'])
            ->middleware('can:lock unlock mailbox')->name('admin.mailbox-operations.lock');
        Route::post('mailbox-operations/{mailbox}/unlock', [MailboxOperationsController::class, 'unlock'])
            ->middleware('can:lock unlock mailbox')->name('admin.mailbox-operations.unlock');
        Route::post('mailbox-operations/{mailbox}/empty', [MailboxOperationsController::class, 'empty'])
            ->middleware('can:empty mailbox')->name('admin.mailbox-operations.empty');
        Route::post('mailbox-operations/{mailbox}/messages/{message}', [MailboxOperationsController::class, 'messageAction'])
            ->middleware('can:manage message state')->name('admin.mailbox-operations.messages.action');

        Route::get('mailbox-rules', [MailboxRulesController::class, 'index'])
            ->middleware('can:view mailbox rules')->name('admin.mailbox-rules.index');
        Route::put('mailbox-rules', [MailboxRulesController::class, 'update'])
            ->middleware('can:update mailbox rules')->name('admin.mailbox-rules.update');
        Route::post('mailbox-rules/cleanup', [MailboxRulesController::class, 'cleanup'])
            ->middleware('can:run mailbox cleanup')->name('admin.mailbox-rules.cleanup');
        Route::post('mailbox-rules/health', [MailboxRulesController::class, 'health'])
            ->middleware('can:run mailbox delivery health checks')->name('admin.mailbox-rules.health');

        Route::get('plans-memberships', [PlanMembershipController::class, 'index'])
            ->middleware('can:view plans')->name('admin.plans-memberships.index');
        Route::put('plans-memberships/{plan}', [PlanMembershipController::class, 'update'])
            ->middleware('can:update plans')->name('admin.plans-memberships.update');
        Route::put('plans-memberships/{plan}/limits', [PlanMembershipController::class, 'limits'])
            ->middleware('can:update plan limits')->name('admin.plans-memberships.limits');
        Route::post('plans-memberships/{plan}/status', [PlanMembershipController::class, 'toggle'])
            ->middleware('can:activate deactivate plans')->name('admin.plans-memberships.status');
        Route::post('plans-memberships/memberships', [PlanMembershipController::class, 'grant'])
            ->middleware('can:grant membership')->name('admin.plans-memberships.memberships.grant');
        Route::put('plans-memberships/memberships/{membership}/extend', [PlanMembershipController::class, 'extend'])
            ->middleware('can:extend membership')->name('admin.plans-memberships.memberships.extend');
        Route::post('plans-memberships/memberships/{membership}/cancel', [PlanMembershipController::class, 'cancel'])
            ->middleware('can:cancel downgrade membership')->name('admin.plans-memberships.memberships.cancel');
        Route::post('plans-memberships/memberships/{membership}/downgrade', [PlanMembershipController::class, 'downgrade'])
            ->middleware('can:cancel downgrade membership')->name('admin.plans-memberships.memberships.downgrade');

        Route::get('integrations', [IntegrationController::class, 'index'])
            ->middleware('can:view integrations')
            ->name('admin.integrations.index');
        Route::put('integrations/{integration}', [IntegrationController::class, 'update'])
            ->middleware('can:configure integrations')
            ->name('admin.integrations.update');
        Route::post('integrations/{integration}/activate', [IntegrationController::class, 'activate'])
            ->middleware('can:activate deactivate integrations')
            ->name('admin.integrations.activate');
        Route::post('integrations/{integration}/deactivate', [IntegrationController::class, 'deactivate'])
            ->middleware('can:activate deactivate integrations')
            ->name('admin.integrations.deactivate');
        Route::post('integrations/{integration}/test', [IntegrationController::class, 'test'])
            ->middleware('can:test integration connection')
            ->name('admin.integrations.test');
        Route::get('integrations/{integration}/secrets/{field}', [IntegrationController::class, 'reveal'])
            ->middleware('can:reveal integration secret')
            ->name('admin.integrations.secrets.reveal');

        Route::get('api-access', [ApiAccessController::class, 'index'])
            ->middleware('can:view API keys')->name('admin.api-access.index');
        Route::put('api-access/settings', [ApiAccessController::class, 'settings'])
            ->middleware('can:manage API globally')->name('admin.api-access.settings.update');
        Route::post('api-access/keys', [ApiAccessController::class, 'store'])
            ->name('admin.api-access.keys.store');
        Route::post('api-access/keys/{apiKey}/revoke', [ApiAccessController::class, 'revoke'])
            ->name('admin.api-access.keys.revoke');
        Route::post('api-access/keys/{apiKey}/regenerate', [ApiAccessController::class, 'regenerate'])
            ->name('admin.api-access.keys.regenerate');

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
        Route::get('blocked-lists', [BlockedListController::class, 'index'])
            ->middleware('can:view blocked lists')
            ->name('admin.blocked-lists.index');
        Route::post('blocked-lists', [BlockedListController::class, 'store'])
            ->middleware('can:create blocked entry')
            ->name('admin.blocked-lists.store');
        Route::post('blocked-lists/test', [BlockedListController::class, 'test'])
            ->middleware('can:run enforcement test')
            ->name('admin.blocked-lists.test');
        Route::post('blocked-lists/import', [BlockedListController::class, 'import'])
            ->middleware('can:import blocked entries')
            ->name('admin.blocked-lists.import');
        Route::get('blocked-lists/export', [BlockedListController::class, 'export'])
            ->middleware('can:export blocked entries')
            ->name('admin.blocked-lists.export');
        Route::post('blocked-lists/bulk', [BlockedListController::class, 'bulk'])
            ->middleware('can:bulk modify blocked entries')
            ->name('admin.blocked-lists.bulk');
        Route::post('blocked-lists/expire', [BlockedListController::class, 'expire'])
            ->middleware('can:bulk modify blocked entries')
            ->name('admin.blocked-lists.expire');
        Route::put('blocked-lists/{blockedListEntry}', [BlockedListController::class, 'update'])
            ->middleware('can:update blocked entry')
            ->name('admin.blocked-lists.update');
        Route::post('blocked-lists/{blockedListEntry}/activate', [BlockedListController::class, 'activate'])
            ->middleware('can:activate deactivate blocked entry')
            ->name('admin.blocked-lists.activate');
        Route::post('blocked-lists/{blockedListEntry}/deactivate', [BlockedListController::class, 'deactivate'])
            ->middleware('can:activate deactivate blocked entry')
            ->name('admin.blocked-lists.deactivate');
        Route::get('abuse-reports', [AbuseReportController::class, 'index'])
            ->middleware('can:view abuse reports')
            ->name('admin.abuse-reports.index');
        Route::get('abuse-reports/{abuseReport}', [AbuseReportController::class, 'show'])
            ->middleware('can:review abuse case')
            ->name('admin.abuse-reports.show');
        Route::put('abuse-reports/{abuseReport}/assignment', [AbuseReportController::class, 'assign'])
            ->middleware('can:assign abuse case')
            ->name('admin.abuse-reports.assign');
        Route::put('abuse-reports/{abuseReport}/status', [AbuseReportController::class, 'status'])
            ->middleware('can:update abuse case status')
            ->name('admin.abuse-reports.status');
        Route::post('abuse-reports/{abuseReport}/notes', [AbuseReportController::class, 'note'])->name('admin.abuse-reports.notes.store');
        Route::post('abuse-reports/{abuseReport}/evidence', [AbuseReportController::class, 'addEvidence'])->name('admin.abuse-reports.evidence.store');
        Route::get('abuse-reports/{abuseReport}/evidence/{abuseEvidence}', [AbuseReportController::class, 'downloadEvidence'])->name('admin.abuse-reports.evidence.download');
        Route::delete('abuse-reports/{abuseReport}/evidence/{abuseEvidence}', [AbuseReportController::class, 'removeEvidence'])->name('admin.abuse-reports.evidence.destroy');
        Route::post('abuse-reports/{abuseReport}/resolve', [AbuseReportController::class, 'resolve'])->name('admin.abuse-reports.resolve');
        Route::post('abuse-reports/{abuseReport}/reject', [AbuseReportController::class, 'reject'])->name('admin.abuse-reports.reject');
        Route::post('abuse-reports/{abuseReport}/reopen', [AbuseReportController::class, 'reopen'])->name('admin.abuse-reports.reopen');
        Route::post('abuse-reports/{abuseReport}/archive', [AbuseReportController::class, 'archive'])->name('admin.abuse-reports.archive');
        Route::post('abuse-reports/{abuseReport}/operational-actions', [AbuseReportController::class, 'operationalAction'])->name('admin.abuse-reports.operational-actions.execute');
        Route::get('activity-audit-logs/export', [AuditLogController::class, 'export'])
            ->middleware('can:admin.activity-audit-logs.export')
            ->name('admin.activity-audit-logs.export');
        Route::put('activity-audit-logs/retention', [AuditLogController::class, 'updateRetention'])
            ->middleware('can:admin.activity-audit-logs.manage-retention')
            ->name('admin.activity-audit-logs.retention.update');

        Route::get('domains', [DomainController::class, 'index'])
            ->middleware('can:view domains')
            ->name('admin.domains.index');
        Route::get('domains/create', [DomainController::class, 'create'])
            ->middleware('can:create domain')
            ->name('admin.domains.create');
        Route::post('domains', [DomainController::class, 'store'])
            ->middleware('can:create domain')
            ->name('admin.domains.store');
        Route::get('domains/{domain}/edit', [DomainController::class, 'edit'])
            ->middleware('can:update domain')
            ->name('admin.domains.edit');
        Route::put('domains/{domain}', [DomainController::class, 'update'])
            ->middleware('can:update domain')
            ->name('admin.domains.update');
        Route::patch('domains/{domain}/status', [DomainController::class, 'status'])
            ->middleware('can:activate deactivate domain')
            ->name('admin.domains.status');
        Route::post('domains/{domain}/default', [DomainController::class, 'default'])
            ->middleware('can:set default domain')
            ->name('admin.domains.default');
        Route::post('domains/{domain}/dns-check', [DomainController::class, 'runDnsCheck'])
            ->middleware('can:run DNS checks')
            ->name('admin.domains.dns-check');

        Route::get('imap-smtp', [InboundMailConnectionController::class, 'index'])
            ->middleware('can:view inbound mail settings')
            ->name('admin.imap-smtp.index');
        Route::get('imap-smtp/create', [InboundMailConnectionController::class, 'create'])
            ->middleware('can:create update inbound connection')
            ->name('admin.imap-smtp.create');
        Route::post('imap-smtp', [InboundMailConnectionController::class, 'store'])
            ->middleware('can:create update inbound connection')
            ->name('admin.imap-smtp.store');
        Route::get('imap-smtp/{inboundMailConnection}/edit', [InboundMailConnectionController::class, 'edit'])
            ->middleware('can:create update inbound connection')
            ->name('admin.imap-smtp.edit');
        Route::put('imap-smtp/{inboundMailConnection}', [InboundMailConnectionController::class, 'update'])
            ->middleware('can:create update inbound connection')
            ->name('admin.imap-smtp.update');
        Route::post('imap-smtp/{inboundMailConnection}/test', [InboundMailConnectionController::class, 'test'])
            ->middleware('can:test inbound connection')
            ->name('admin.imap-smtp.test');
        Route::patch('imap-smtp/{inboundMailConnection}/status', [InboundMailConnectionController::class, 'toggle'])
            ->middleware('can:activate deactivate inbound connection')
            ->name('admin.imap-smtp.status');
        Route::post('imap-smtp/run-all-checks', [SmtpConnectionController::class, 'runAll'])
            ->middleware('can:run infrastructure health checks')
            ->name('admin.imap-smtp.run-all-checks');
        Route::get('imap-smtp/smtp/create', [SmtpConnectionController::class, 'create'])
            ->middleware('can:create update SMTP connection')
            ->name('admin.imap-smtp.smtp.create');
        Route::post('imap-smtp/smtp', [SmtpConnectionController::class, 'store'])
            ->middleware('can:create update SMTP connection')
            ->name('admin.imap-smtp.smtp.store');
        Route::get('imap-smtp/smtp/{smtpConnection}/edit', [SmtpConnectionController::class, 'edit'])
            ->middleware('can:create update SMTP connection')
            ->name('admin.imap-smtp.smtp.edit');
        Route::put('imap-smtp/smtp/{smtpConnection}', [SmtpConnectionController::class, 'update'])
            ->middleware('can:create update SMTP connection')
            ->name('admin.imap-smtp.smtp.update');
        Route::post('imap-smtp/smtp/{smtpConnection}/test', [SmtpConnectionController::class, 'test'])
            ->middleware('can:test SMTP connection')
            ->name('admin.imap-smtp.smtp.test');
        Route::post('imap-smtp/smtp/{smtpConnection}/send-test', [SmtpConnectionController::class, 'sendTest'])
            ->middleware('can:send SMTP test email')
            ->name('admin.imap-smtp.smtp.send-test');
        Route::post('imap-smtp/smtp/{smtpConnection}/default', [SmtpConnectionController::class, 'default'])
            ->middleware('can:set default SMTP connection')
            ->name('admin.imap-smtp.smtp.default');
        Route::patch('imap-smtp/smtp/{smtpConnection}/status', [SmtpConnectionController::class, 'toggle'])
            ->middleware('can:activate deactivate SMTP connection')
            ->name('admin.imap-smtp.smtp.status');

        Route::get('security-defense-center', [SecurityDefenseController::class, 'index'])
            ->middleware('can:admin.security-defense-center.view')
            ->name('admin.security-defense-center.index');
        Route::put('security-defense-center/bot-protection', [SecurityDefenseController::class, 'updateBot'])
            ->middleware('can:update security settings')
            ->name('admin.security-defense-center.bot.update');
        Route::put('security-defense-center/akismet', [SecurityDefenseController::class, 'updateAkismet'])
            ->middleware('can:update security settings')
            ->name('admin.security-defense-center.akismet.update');
        Route::put('security-defense-center/rate-limits', [SecurityDefenseController::class, 'updateRateLimits'])
            ->middleware('can:manage rate limits')
            ->name('admin.security-defense-center.rate-limits.update');
        Route::put('security-defense-center/ip-access', [SecurityDefenseController::class, 'updateIpAccess'])
            ->middleware('can:manage admin security')
            ->name('admin.security-defense-center.ip-access.update');
        Route::put('security-defense-center/admin-access', [SecurityDefenseController::class, 'updateAdminSecurity'])
            ->middleware('can:manage admin security')
            ->name('admin.security-defense-center.admin-access.update');
        Route::post('security-defense-center/force-logout', [SecurityDefenseController::class, 'forceLogout'])
            ->middleware('can:force logout sessions')
            ->name('admin.security-defense-center.force-logout');
        Route::patch('security-defense-center/signals/{abuseSignal}', [SecurityDefenseController::class, 'updateSignalStatus'])
            ->name('admin.security-defense-center.signals.status');
        Route::post('security-defense-center/test', [SecurityDefenseController::class, 'test'])
            ->middleware('can:test security provider')
            ->name('admin.security-defense-center.test');
        Route::get('security-defense-center/reveal/{group}/{field}', [SecurityDefenseController::class, 'reveal'])
            ->middleware('can:reveal security secret')
            ->name('admin.security-defense-center.secret.reveal');

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
        Route::get('translation-center', [TranslationCenterController::class, 'index'])
            ->middleware('can:view translations')
            ->name('admin.translation-center.index');
        Route::post('translation-center', [TranslationCenterController::class, 'store'])
            ->middleware('can:manage translation sources')
            ->name('admin.translation-center.sources.store');
        Route::post('translation-center/editor/save', [TranslationCenterController::class, 'save'])
            ->middleware('can:edit translations')
            ->name('admin.translation-center.translations.save');
        Route::post('translation-center/editor/review', [TranslationCenterController::class, 'review'])
            ->middleware('can:review translations')
            ->name('admin.translation-center.translations.review');
        Route::post('translation-center/editor/publish', [TranslationCenterController::class, 'publish'])
            ->middleware('can:publish translations')
            ->name('admin.translation-center.translations.publish');
        Route::put('translation-center/{translationSource}', [TranslationCenterController::class, 'update'])
            ->middleware('can:update translation sources')
            ->name('admin.translation-center.sources.update');

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
        Route::get('comment-moderation', [CommentModerationController::class, 'index'])
            ->middleware('can:view comments')
            ->name('admin.comment-moderation.index');
        Route::post('comment-moderation/{comment}/approve', [CommentModerationController::class, 'approve'])
            ->middleware('can:approve comments')
            ->name('admin.comment-moderation.approve');
        Route::post('comment-moderation/{comment}/mark', [CommentModerationController::class, 'mark'])
            ->middleware('can:mark comments as spam')
            ->name('admin.comment-moderation.mark');
        Route::post('comment-moderation/{comment}/reply', [CommentModerationController::class, 'reply'])
            ->middleware('can:reply to comments')
            ->name('admin.comment-moderation.reply');
        Route::put('comment-moderation/{comment}/edit', [CommentModerationController::class, 'edit'])
            ->middleware('can:edit comments')
            ->name('admin.comment-moderation.edit');
        Route::post('comment-moderation/{comment}/trash', [CommentModerationController::class, 'trash'])
            ->middleware('can:trash restore comments')
            ->name('admin.comment-moderation.trash');
        Route::post('comment-moderation/{comment}/restore', [CommentModerationController::class, 'restore'])
            ->middleware('can:trash restore comments')
            ->name('admin.comment-moderation.restore');
        Route::delete('comment-moderation/{comment}', [CommentModerationController::class, 'destroy'])
            ->middleware('can:permanently delete comments')
            ->name('admin.comment-moderation.destroy');
        Route::post('comment-moderation/{comment}/false-positive', [CommentModerationController::class, 'falsePositive'])
            ->middleware('can:approve comments')
            ->name('admin.comment-moderation.false-positive');
        Route::post('comment-moderation/bulk', [CommentModerationController::class, 'bulk'])
            ->middleware('can:moderate comments')
            ->name('admin.comment-moderation.bulk');
        Route::post('comment-moderation/{comment}/block', [CommentModerationController::class, 'block'])
            ->middleware('can:manage comment blocklist')
            ->name('admin.comment-moderation.block');
        Route::put('comment-moderation/settings', [CommentModerationController::class, 'settings'])
            ->middleware('can:update comment settings')
            ->name('admin.comment-moderation.settings');
        Route::put('comment-moderation/posts/{post}/settings', [CommentModerationController::class, 'postSettings'])
            ->middleware('can:update comment settings')
            ->name('admin.comment-moderation.posts.settings');
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
                if (in_array($item['route'], ['dashboard', 'admin.mailbox-operations.index', 'admin.product-analytics.index', 'admin.theme-launch-center.index', 'admin.appearance-studio.index', 'admin.typography-center.index', 'admin.mailbox-rules.index', 'admin.blocked-lists.index', 'admin.plans-memberships.index', 'admin.integrations.index', 'admin.api-access.index', 'admin.people-identity.index', 'admin.roles-permissions.index', 'admin.author-profiles.index', 'admin.settings.index', 'admin.activity-audit-logs.index', 'admin.abuse-reports.index', 'admin.domains.index', 'admin.imap-smtp.index', 'admin.security-defense-center.index', 'admin.backups-health.index', 'admin.update-center.index', 'admin.notifications.index', 'admin.email-templates.index', 'admin.locale-launch-center.index', 'admin.translation-center.index', 'admin.page-studio.index', 'admin.blog-studio.index', 'admin.taxonomy.index', 'admin.sections-studio.index', 'admin.media-library.index', 'admin.comment-moderation.index', 'admin.seo-growth-center.index'], true)) {
                    continue;
                }

                Route::get($item['path'], PlaceholderController::class)
                    ->middleware('can:'.$item['permission'])
                    ->name($item['route']);
            }
        }
    });
