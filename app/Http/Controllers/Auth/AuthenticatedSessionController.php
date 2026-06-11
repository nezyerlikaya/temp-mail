<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'registrationEnabled' => (bool) config('auth.registration_enabled', false),
        ]);
    }

    public function store(LoginRequest $request, AuditLogger $audit): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $audit->record('auth.login_failed', null, null, [
                'email' => $request->input('email'),
            ], [
                'module' => 'auth',
                'action' => 'Login failed',
                'severity' => 'warning',
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if ($request->user()?->status === 'suspended') {
            $audit->record('auth.login_failed', $request->user(), $request->user(), [
                'reason' => 'suspended',
            ], [
                'module' => 'auth',
                'action' => 'Login failed',
                'severity' => 'warning',
            ]);

            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This account is suspended. Contact an administrator for assistance.']);
        }

        $request->session()->regenerate();
        $audit->record('auth.login_success', $request->user(), $request->user(), [], [
            'module' => 'auth',
            'action' => 'Login success',
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuditLogger $audit): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $audit->record('auth.logout', $user, $user, [], [
                'module' => 'auth',
                'action' => 'Logout',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
