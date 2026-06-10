<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InstallerController;
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

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware('auth')
    ->name('dashboard');
