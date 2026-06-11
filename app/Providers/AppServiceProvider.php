<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Admin\AdminCommandRegistry;
use App\Services\Admin\AdminNavigationRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;

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
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->is_admin && str_starts_with($ability, 'admin.') ? true : null;
        });

        Gate::define('access-admin', fn (User $user): bool => $user->is_admin);
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
