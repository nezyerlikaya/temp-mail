<?php

namespace App\Providers;

use App\Models\ApiKey;
use App\Models\SystemNotification;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Admin\AdminCommandRegistry;
use App\Services\Admin\AdminNavigationRegistry;
use App\Services\Api\ApiAccessPolicyService;
use App\Services\Mail\SmtpSettingsStore;
use App\Services\Notifications\NotificationService;
use App\Services\Security\RateLimitPolicyStore;
use App\Services\Security\RateLimitResolver;
use App\Services\Settings\SettingsResolver;
use App\Services\Users\RolePermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('system_settings')) {
                app(SettingsResolver::class)->applyRuntime();
            }

            if (Schema::hasTable('security_settings')) {
                config(['session.lifetime' => app(RateLimitPolicyStore::class)->adminAccess()['admin_session_lifetime']]);
            }

            if (Schema::hasTable('smtp_connections')) {
                app(SmtpSettingsStore::class)->applyRuntimeConfig();
            }
        } catch (Throwable) {
            // Installer and database recovery must render before settings storage exists.
        }

        $permissions = app(RolePermissionResolver::class);

        Gate::define('access-admin', fn (User $user): bool => $permissions->canAccessAdmin($user));

        foreach ($permissions->abilityMap() as $abilities) {
            foreach (array_keys($abilities) as $ability) {
                Gate::define($ability, fn (User $user): bool => $permissions->allows($user, $ability));
            }
        }

        Gate::define('manage-localization', fn (User $user): bool => $permissions->allows($user, 'admin.locale-launch-center.manage'));
        Gate::define('view analytics', fn (User $user): bool => $permissions->allows($user, 'admin.product-analytics.view'));
        Gate::define('export analytics', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('activate theme', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('view appearance', fn (User $user): bool => $permissions->allows($user, 'admin.appearance-studio.view'));
        Gate::define('update appearance', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('reset appearance', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('preview-locale-readiness', fn (User $user): bool => $permissions->allows($user, 'admin.locale-launch-center.preview'));
        Gate::define('view pages', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.view'));
        Gate::define('create page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.create'));
        Gate::define('update page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.update'));
        Gate::define('publish page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.publish'));
        Gate::define('hide page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.hide'));
        Gate::define('trash page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.trash'));
        Gate::define('restore page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.restore'));
        Gate::define('permanently delete page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.delete'));
        Gate::define('preview page', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.preview'));
        Gate::define('publish page readiness', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.publish'));
        Gate::define('trash page readiness', fn (User $user): bool => $permissions->allows($user, 'admin.page-studio.trash'));
        Gate::define('view posts', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.view'));
        Gate::define('create post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.create'));
        Gate::define('update post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.update'));
        Gate::define('publish post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.publish'));
        Gate::define('hide post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.hide'));
        Gate::define('preview post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.preview'));
        Gate::define('restore post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.restore'));
        Gate::define('trash post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.trash'));
        Gate::define('permanently delete post', fn (User $user): bool => $permissions->allows($user, 'admin.blog-studio.delete'));
        Gate::define('view taxonomy', fn (User $user): bool => $permissions->allows($user, 'admin.taxonomy.view'));
        Gate::define('create taxonomy', fn (User $user): bool => $permissions->allows($user, 'admin.taxonomy.create'));
        Gate::define('update taxonomy', fn (User $user): bool => $permissions->allows($user, 'admin.taxonomy.update'));
        Gate::define('attach taxonomy to post', fn (User $user): bool => $permissions->allows($user, 'admin.taxonomy.attach'));
        Gate::define('view sections', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.view'));
        Gate::define('create section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.create'));
        Gate::define('update section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.update'));
        Gate::define('reorder section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.reorder'));
        Gate::define('update section items', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.items.update'));
        Gate::define('publish section readiness', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.publish'));
        Gate::define('activate section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.activate'));
        Gate::define('hide section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.hide'));
        Gate::define('preview section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.preview'));
        Gate::define('restore section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.restore'));
        Gate::define('trash section readiness', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.trash'));
        Gate::define('trash section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.trash'));
        Gate::define('permanently delete section', fn (User $user): bool => $permissions->allows($user, 'admin.sections-studio.delete'));
        Gate::define('view media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.view'));
        Gate::define('view media picker', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.picker'));
        Gate::define('select media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.select'));
        Gate::define('upload media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.upload'));
        Gate::define('upload through picker', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.upload-through-picker'));
        Gate::define('update media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.update'));
        Gate::define('update media metadata', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.update-metadata'));
        Gate::define('trash media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.trash'));
        Gate::define('restore media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.restore'));
        Gate::define('delete media', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.delete'));
        Gate::define('view media usage', fn (User $user): bool => $permissions->allows($user, 'admin.media-library.usage'));
        Gate::define('view SEO', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.view'));
        Gate::define('update SEO', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.update'));
        Gate::define('manage SEO settings', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.manage'));
        Gate::define('preview SEO', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.preview'));
        Gate::define('update schema', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.schema'));
        Gate::define('select SEO media', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.media'));
        Gate::define('run diagnostics', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.diagnostics'));
        Gate::define('manage SEO templates', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.templates'));
        Gate::define('manage redirects', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.redirects'));
        Gate::define('manage sitemap robots readiness', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.readiness'));
        Gate::define('rollback SEO version', fn (User $user): bool => $permissions->allows($user, 'admin.seo-growth-center.rollback'));
        Gate::define('view domains', fn (User $user): bool => $permissions->allows($user, 'admin.domains.view'));
        Gate::define('create domain', fn (User $user): bool => $permissions->allows($user, 'admin.domains.create'));
        Gate::define('update domain', fn (User $user): bool => $permissions->allows($user, 'admin.domains.update'));
        Gate::define('activate deactivate domain', fn (User $user): bool => $permissions->allows($user, 'admin.domains.status'));
        Gate::define('set default domain', fn (User $user): bool => $permissions->allows($user, 'admin.domains.default'));
        Gate::define('run DNS checks', fn (User $user): bool => $permissions->allows($user, 'admin.domains.dns-check'));
        Gate::define('view inbound mail settings', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.view'));
        Gate::define('create update inbound connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.manage'));
        Gate::define('test inbound connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.test'));
        Gate::define('activate deactivate inbound connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.status'));
        Gate::define('view SMTP settings', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.view'));
        Gate::define('create update SMTP connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.smtp.manage'));
        Gate::define('test SMTP connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.smtp.test'));
        Gate::define('send SMTP test email', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.smtp.send-test'));
        Gate::define('set default SMTP connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.smtp.default'));
        Gate::define('activate deactivate SMTP connection', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.smtp.status'));
        Gate::define('run infrastructure health checks', fn (User $user): bool => $permissions->allows($user, 'admin.imap-smtp.health.run'));
        Gate::define('view mailboxes', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.view'));
        Gate::define('view mailbox', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.view'));
        Gate::define('create mailbox readiness', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.create'));
        Gate::define('manage mailbox readiness', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.manage'));
        Gate::define('view message content', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.message-content'));
        Gate::define('expire mailbox', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.expire'));
        Gate::define('lock unlock mailbox', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.lock'));
        Gate::define('empty mailbox', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.empty'));
        Gate::define('manage message state', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-operations.message-state'));
        Gate::define('view mailbox rules', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-rules.view'));
        Gate::define('update mailbox rules', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-rules.update'));
        Gate::define('run mailbox cleanup', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-rules.cleanup'));
        Gate::define('run mailbox delivery health checks', fn (User $user): bool => $permissions->allows($user, 'admin.mailbox-rules.health'));
        Gate::define('view plans', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.view'));
        Gate::define('update plans', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.update'));
        Gate::define('update plan limits', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.limits'));
        Gate::define('activate deactivate plans', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.status'));
        Gate::define('view memberships', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.memberships.view'));
        Gate::define('grant membership', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.memberships.grant'));
        Gate::define('extend membership', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.memberships.extend'));
        Gate::define('cancel downgrade membership', fn (User $user): bool => $permissions->allows($user, 'admin.plans-memberships.memberships.cancel'));
        Gate::define('manage API globally', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('view API keys', fn (User $user): bool => $permissions->allows($user, 'admin.api-access.view'));
        Gate::define('create own API key', fn (User $user): bool => app(ApiAccessPolicyService::class)->canCreateFor($user, $user));
        Gate::define('mutate own API key', fn (User $user, ApiKey $key): bool => app(ApiAccessPolicyService::class)->canMutate($user, $key));
        Gate::define('view security settings', fn (User $user): bool => $permissions->allows($user, 'admin.security-defense-center.view'));
        Gate::define('update security settings', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('reveal security secret', fn (User $user): bool => $permissions->roleFor($user)->value === 'owner');
        Gate::define('test security provider', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('manage rate limits', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('manage admin security', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('force logout sessions', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('view failed login history', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('view security operations', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin', 'moderator'], true));
        Gate::define('review abuse signal', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin', 'moderator'], true));
        Gate::define('resolve abuse signal', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin', 'moderator'], true));
        Gate::define('view email templates', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.view'));
        Gate::define('create email template', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.create'));
        Gate::define('update email template', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.update'));
        Gate::define('activate hide email template', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.activate'));
        Gate::define('preview email template', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.preview'));
        Gate::define('send test email template', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.send-test'));
        Gate::define('reset email template readiness', fn (User $user): bool => $permissions->allows($user, 'admin.email-templates.reset'));
        Gate::define('view notifications', fn (User $user): bool => $permissions->allows($user, 'admin.notifications.view'));
        Gate::define('view notification rules', fn (User $user): bool => $permissions->allows($user, 'admin.notifications.view'));
        Gate::define('update notification rules', fn (User $user): bool => in_array($permissions->roleFor($user)->value, ['owner', 'admin'], true));
        Gate::define('view notification', fn (User $user, SystemNotification $notification): bool => app(NotificationService::class)->visibleTo($notification, $user));
        Gate::define('mark notification', fn (User $user, ?SystemNotification $notification = null): bool => $permissions->allows($user, 'admin.notifications.view')
            && ($notification === null || app(NotificationService::class)->visibleTo($notification, $user)));
        Gate::define('snooze notifications', fn (User $user, SystemNotification $notification): bool => $permissions->allows($user, 'admin.notifications.view')
            && app(NotificationService::class)->visibleTo($notification, $user));
        Gate::define('archive notification', fn (User $user, SystemNotification $notification): bool => $permissions->allows($user, 'admin.notifications.view')
            && app(NotificationService::class)->visibleTo($notification, $user));

        Gate::policy(User::class, UserPolicy::class);

        View::composer('components.admin.sidebar', function (ViewContract $view): void {
            $user = request()->user();

            $view->with(
                'navigationGroups',
                $user
                    ? app(AdminNavigationRegistry::class)->visibleFor($user, Route::currentRouteName())
                    : [],
            );
        });

        View::composer('components.admin.command-palette', function (ViewContract $view): void {
            $user = request()->user();

            $view->with(
                'commands',
                $user ? app(AdminCommandRegistry::class)->visibleFor($user) : [],
            );
        });

        View::composer('components.admin.header', function (ViewContract $view): void {
            $user = request()->user();

            $view->with('notificationUnreadCount', $user ? app(NotificationService::class)->unreadCount($user) : 0);
        });

        foreach (['login', 'register', 'forgot_password', 'mailbox_creation', 'inbox_refresh', 'comments', 'contact_form', 'api_requests'] as $limiter) {
            RateLimiter::for($limiter, fn (Request $request) => app(RateLimitResolver::class)->for($limiter, $request));
        }
    }
}
