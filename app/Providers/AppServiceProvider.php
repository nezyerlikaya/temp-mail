<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Admin\AdminCommandRegistry;
use App\Services\Admin\AdminNavigationRegistry;
use App\Services\Settings\SettingsResolver;
use App\Services\Users\RolePermissionResolver;
use Illuminate\Cache\RateLimiting\Limit;
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

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
