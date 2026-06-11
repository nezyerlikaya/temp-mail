<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Updates\CheckForUpdatesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Updates\CheckForUpdatesRequest;
use App\Http\Requests\Updates\InstallUpdateRequest;
use App\Http\Requests\Updates\RollbackUpdateRequest;
use App\Http\Requests\Updates\UploadManualUpdateRequest;
use App\Services\Audit\AuditLogger;
use App\Services\Updates\ManualUpdateService;
use App\Services\Updates\UpdateBackupService;
use App\Services\Updates\UpdateChannelResolver;
use App\Services\Updates\UpdateCompatibilityChecker;
use App\Services\Updates\UpdateHistoryStore;
use App\Services\Updates\UpdateInstaller;
use App\Services\Updates\UpdateLicenseCheckService;
use App\Services\Updates\UpdateLockService;
use App\Services\Updates\UpdateManifestClient;
use App\Services\Updates\UpdatePathProtector;
use App\Services\Updates\UpdateRollbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

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
        UpdateBackupService $backupReadiness,
        UpdateRollbackService $rollback,
        UpdatePathProtector $paths,
        ManualUpdateService $manual,
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
            'backupReadiness' => $backupReadiness->readiness(),
            'rollbackReadiness' => $rollback->readiness($latestCheck),
            'protectedPaths' => $paths->protectedPaths(),
            'manualSteps' => $manual->manualSteps(),
            'canCheckUpdates' => $request->user()?->can('admin.update-center.check') ?? false,
            'canInstallUpdates' => $request->user()?->can('admin.update-center.install') ?? false,
            'canUploadManualUpdates' => $request->user()?->can('admin.update-center.manual-upload') ?? false,
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

    public function install(InstallUpdateRequest $request, UpdateHistoryStore $history, UpdateInstaller $installer): RedirectResponse
    {
        $latestCheck = $history->latest();

        abort_if($latestCheck === null, 404);

        try {
            $result = $installer->install($request->user(), $latestCheck, $request->validated('maintenance_message'));
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.update-center.index')
                ->with('warning', $exception->getMessage());
        }

        return redirect()
            ->route('admin.update-center.index')
            ->with($result->status === 'installed' ? 'status' : 'warning', $result->status === 'installed'
                ? 'Update installed successfully. Post-update checks were recorded.'
                : 'Update installation failed safely. Review the recovery state before continuing.');
    }

    public function uploadManual(UploadManualUpdateRequest $request, ManualUpdateService $manual, AuditLogger $audit): RedirectResponse
    {
        try {
            $result = $manual->storeAndVerify(
                $request->file('package'),
                (string) $request->validated('expected_checksum'),
                $request->validated('signature'),
            );
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.update-center.index')
                ->with('warning', $exception->getMessage());
        }

        $audit->record('update.manual_verified', $request->user(), null, [
            'checksum' => $result['verification']['checksum'],
            'entries' => count($result['verification']['entries']),
        ], ['module' => 'system', 'action' => 'Verify manual update', 'severity' => 'critical']);

        return redirect()
            ->route('admin.update-center.index')
            ->with('status', 'Manual update package verified. Follow the manual mode steps for shared hosting.');
    }

    public function rollback(RollbackUpdateRequest $request, UpdateRollbackService $rollback, UpdateHistoryStore $history, AuditLogger $audit): RedirectResponse
    {
        $readiness = $rollback->readiness($history->latest());

        $audit->record('update.rollback_readiness_reviewed', $request->user(), null, [
            'ready' => $readiness['ready'],
            'status' => $readiness['status'],
        ], ['module' => 'system', 'action' => 'Rollback readiness', 'severity' => 'warning']);

        return redirect()
            ->route('admin.update-center.index')
            ->with('warning', 'Rollback readiness was reviewed. Full automatic restore is intentionally not exposed here.');
    }
}
