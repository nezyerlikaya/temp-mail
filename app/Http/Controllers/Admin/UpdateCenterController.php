<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Updates\CheckForUpdatesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Updates\CheckForUpdatesRequest;
use App\Services\Updates\UpdateChannelResolver;
use App\Services\Updates\UpdateCompatibilityChecker;
use App\Services\Updates\UpdateHistoryStore;
use App\Services\Updates\UpdateLicenseCheckService;
use App\Services\Updates\UpdateLockService;
use App\Services\Updates\UpdateManifestClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateCenterController extends Controller
{
    public function index(
        Request $request,
        UpdateHistoryStore $history,
        UpdateChannelResolver $channels,
        UpdateCompatibilityChecker $compatibility,
        UpdateLicenseCheckService $license,
        UpdateLockService $locks,
        UpdateManifestClient $client,
    ): View {
        $request->user()?->can('admin.update-center.view') || abort(403);

        $latestCheck = $history->latest();

        return view('dashboard.update-center.index', [
            'adminUser' => $request->user(),
            'channels' => $channels->options(),
            'selectedChannel' => old('channel', $latestCheck?->channel ?? $channels->default()),
            'currentVersion' => $client->currentVersion(),
            'endpoint' => $client->endpoint(),
            'latestCheck' => $latestCheck,
            'history' => $history->recent(),
            'compatibility' => $latestCheck?->compatibility ?? $compatibility->check(null),
            'licenseReadiness' => $license->readiness(),
            'lockStatus' => $locks->status(),
            'canCheckUpdates' => $request->user()?->can('admin.update-center.check') ?? false,
        ]);
    }

    public function check(CheckForUpdatesRequest $request, CheckForUpdatesAction $action): RedirectResponse
    {
        $check = $action->handle($request->user(), (string) $request->validated('channel'));

        $message = match ($check->status) {
            'available' => 'A newer version is available. Review compatibility before any install step.',
            'current' => 'This installation is already on the latest available version for the selected channel.',
            'incompatible' => 'An update manifest was found, but this server is not compatible yet.',
            default => 'The update check could not be completed. The error state was recorded cleanly.',
        };

        return redirect()
            ->route('admin.update-center.index')
            ->with($check->status === 'failed' || $check->status === 'incompatible' ? 'warning' : 'status', $message);
    }
}
