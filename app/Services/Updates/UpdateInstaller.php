<?php

namespace App\Services\Updates;

use App\Actions\Updates\PostUpdateHealthCheck;
use App\Models\UpdateCheck;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;
use ZipArchive;

class UpdateInstaller
{
    public function __construct(
        private readonly UpdatePackageDownloader $downloader,
        private readonly UpdatePackageVerifier $verifier,
        private readonly UpdatePathProtector $paths,
        private readonly UpdateHistoryStore $history,
        private readonly UpdateLockService $locks,
        private readonly UpdateBackupService $backups,
        private readonly PostUpdateHealthCheck $health,
        private readonly AuditLogger $audit,
    ) {}

    public function install(User $actor, UpdateCheck $check, ?string $maintenanceMessage = null): UpdateCheck
    {
        $this->assertInstallable($check);

        $this->locks->acquire($actor->email);
        $maintenanceEnabled = false;
        $latest = $this->recordLike($actor, $check, 'pending', 'Update install started.');

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(300);
            }

            $this->backups->createPreUpdateBackup($actor);

            Artisan::call('down', [
                '--render' => 'maintenance',
                '--refresh' => 15,
            ]);
            $maintenanceEnabled = true;

            $download = $this->downloader->download($check->manifest ?? [], $check->endpoint, (string) $check->latest_version);
            $latest = $this->recordLike($actor, $check, 'downloaded', null, [
                'download' => ['bytes' => $download['bytes'], 'url' => $download['url']],
                'maintenance_message' => $maintenanceMessage,
            ]);

            $verification = $this->verifier->verify(
                $download['path'],
                (string) $check->checksum,
                $check->signature,
            );
            $latest = $this->recordLike($actor, $check, 'verified', null, [
                'verification' => $verification,
            ]);

            $this->applyPackage($download['path']);

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');

            $health = $this->health->run();
            $latest = $this->recordLike($actor, $check, 'installed', null, [
                'verification' => $verification,
                'post_update_health' => $health,
            ]);

            $this->audit->record('update.installed', $actor, null, [
                'from' => $check->current_version,
                'to' => $check->latest_version,
                'channel' => $check->channel,
                'post_update_health' => $health['status'],
            ], ['module' => 'system', 'action' => 'Install update', 'severity' => 'critical']);

            return $latest;
        } catch (Throwable $exception) {
            $failed = $this->recordLike($actor, $check, 'failed', $exception->getMessage(), [
                'notification_ready' => true,
                'recovery' => 'Review the failed update history, confirm the backup, and use manual mode if automatic install is unavailable.',
            ]);

            $this->audit->record('update.failed', $actor, null, [
                'from' => $check->current_version,
                'to' => $check->latest_version,
                'channel' => $check->channel,
                'notification_ready' => true,
            ], ['module' => 'system', 'action' => 'Update failed', 'severity' => 'warning']);

            return $failed;
        } finally {
            if ($maintenanceEnabled) {
                Artisan::call('up');
            }

            $this->locks->release();
        }
    }

    private function assertInstallable(UpdateCheck $check): void
    {
        if ($this->locks->isLocked()) {
            throw new \RuntimeException('Another update operation is already running. Wait until the active update lock clears.');
        }

        if (! in_array($check->status, ['available', 'verified'], true)) {
            throw new \RuntimeException('Only an available verified manifest can be installed.');
        }

        if (! $check->https_endpoint || ! $check->signed_manifest) {
            throw new \RuntimeException('Unsigned or non-HTTPS update manifests cannot be installed.');
        }

        if (! is_array($check->compatibility) || ($check->compatibility['compatible'] ?? false) !== true) {
            throw new \RuntimeException('The latest compatibility check must pass before installation.');
        }

        if (! is_array($check->manifest) || empty($check->manifest['package_url']) || empty($check->checksum)) {
            throw new \RuntimeException('The manifest must include a package URL and checksum before installation.');
        }
    }

    /** @param array<string, mixed> $extraManifest */
    private function recordLike(User $actor, UpdateCheck $check, string $status, ?string $errorMessage = null, array $extraManifest = []): UpdateCheck
    {
        return $this->history->record(
            actor: $actor,
            channel: $check->channel,
            currentVersion: $check->current_version,
            latestVersion: $check->latest_version,
            status: $status,
            endpoint: $check->endpoint,
            httpsEndpoint: $check->https_endpoint,
            signedManifest: $check->signed_manifest,
            checksum: $check->checksum,
            signature: $check->signature,
            manifest: [...($check->manifest ?? []), ...$extraManifest],
            compatibility: $check->compatibility,
            errorMessage: $errorMessage,
        );
    }

    private function applyPackage(string $zipPath): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('The verified update package could not be opened for installation.');
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);

            if ($name === '' || str_ends_with($name, '/')) {
                continue;
            }

            $relative = $this->paths->normalize($name);
            $destination = $this->paths->destinationFor($relative);
            $contents = $zip->getFromIndex($index);

            if ($contents === false) {
                $zip->close();
                throw new \RuntimeException('A verified update file could not be read from the package.');
            }

            File::ensureDirectoryExists(dirname($destination));
            File::put($destination, $contents, true);
        }

        $zip->close();
    }
}
