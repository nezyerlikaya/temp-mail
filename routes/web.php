<?php

use App\Http\Controllers\Admin\OperationsOverviewController;
use App\Http\Controllers\Admin\PlaceholderController;
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

        foreach (app(AdminNavigationRegistry::class)->groups() as $group) {
            foreach ($group['items'] as $item) {
                if (in_array($item['route'], ['dashboard', 'admin.people-identity.index', 'admin.roles-permissions.index'], true)) {
                    continue;
                }

                Route::get($item['path'], PlaceholderController::class)
                    ->middleware('can:'.$item['permission'])
                    ->name($item['route']);
            }
        }
    });
