<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Health\RunHealthCheckAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backups\CreateBackupRequest;
use App\Http\Requests\Backups\DeleteBackupRequest;
use App\Http\Requests\Backups\DownloadBackupRequest;
use App\Http\Requests\Backups\RunHealthCheckRequest;
use App\Models\SystemBackup;
use App\Services\Backups\BackupDeleteAction;
use App\Services\Backups\BackupDownloadService;
use App\Services\Backups\BackupIntegrityChecker;
use App\Services\Backups\BackupService;
use App\Services\Backups\DiskSpaceChecker;
use App\Services\Health\HealthHistoryStore;
use App\Services\Health\SystemHealthChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(
        Request $request,
        BackupService $backups,
        DiskSpaceChecker $diskSpace,
        BackupIntegrityChecker $integrity,
        HealthHistoryStore $healthHistory,
        SystemHealthChecker $healthChecker,
    ): View {
        Gate::authorize('admin.backups-health.view');

        $records = $backups->backups();
        $latestHealth = $healthHistory->latest();
        $healthSnapshot = $latestHealth
            ? [
                'overall_status' => $latestHealth->overall_status,
                'summary' => $latestHealth->summary,
                'results' => $latestHealth->results,
                'checked_at' => $latestHealth->checked_at?->toIso8601String(),
            ]
            : $healthChecker->run();

        return view('dashboard.backups-health.index', [
            'adminUser' => $request->user(),
            'backups' => $records,
            'summary' => $backups->summary(),
            'diskSpace' => $diskSpace->status(),
            'integrity' => $records->mapWithKeys(fn (SystemBackup $backup): array => [
                $backup->id => $integrity->check($backup),
            ])->all(),
            'canCreate' => $request->user()?->can('admin.backups-health.create') === true,
            'canDownload' => $request->user()?->can('admin.backups-health.download') === true,
            'canDelete' => $request->user()?->can('admin.backups-health.delete') === true,
            'health' => $healthSnapshot,
            'healthHistory' => $healthHistory->recent(),
            'lastHealthCheck' => $latestHealth,
            'canRunHealth' => $request->user()?->can('admin.backups-health.run-health') === true,
        ]);
    }

    public function store(CreateBackupRequest $request, BackupService $backups): RedirectResponse
    {
        $backup = $backups->create($request->user(), $request->validated('type'));

        return redirect()
            ->route('admin.backups-health.index')
            ->with($backup->status === 'completed' ? 'status' : 'warning', $backup->status === 'completed'
                ? 'Backup completed successfully.'
                : 'Backup failed. Notification readiness was recorded.');
    }

    public function download(DownloadBackupRequest $request, SystemBackup $backup, BackupDownloadService $download): BinaryFileResponse
    {
        return $download->download($backup, $request->user());
    }

    public function destroy(DeleteBackupRequest $request, SystemBackup $backup, BackupDeleteAction $delete): RedirectResponse
    {
        $delete->handle($backup, $request->user());

        return redirect()->route('admin.backups-health.index')->with('status', 'Backup deleted.');
    }

    public function runHealthCheck(RunHealthCheckRequest $request, RunHealthCheckAction $run): RedirectResponse
    {
        $record = $run->handle($request->user());

        return redirect()
            ->route('admin.backups-health.index')
            ->with($record->overall_status === 'critical' ? 'warning' : 'status', $record->overall_status === 'critical'
                ? 'Health check completed with critical issues. Notification readiness was recorded.'
                : 'Health check completed.');
    }
}
