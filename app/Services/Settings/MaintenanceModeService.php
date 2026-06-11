<?php

namespace App\Services\Settings;

use Illuminate\Http\Request;

class MaintenanceModeService
{
    public function __construct(private readonly SettingsResolver $settings) {}

    public function enabled(): bool
    {
        return (bool) $this->settings->group('maintenance')['enabled'];
    }

    public function shouldBlock(Request $request): bool
    {
        if (! $this->enabled() || $request->user()?->hasAdminAccess()) {
            return false;
        }

        if ($request->is('install*', 'dashboard*', 'login', 'logout', 'up')) {
            return false;
        }

        return ! in_array($request->ip(), $this->settings->group('maintenance')['allowed_admin_ips'], true);
    }

    public function message(): string
    {
        return (string) $this->settings->group('maintenance')['message'];
    }
}
