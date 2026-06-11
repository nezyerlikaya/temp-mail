<?php

namespace App\Actions\Updates;

use App\Models\UpdateCheck;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Updates\UpdateCompatibilityChecker;
use App\Services\Updates\UpdateHistoryStore;
use App\Services\Updates\UpdateManifestClient;

class CheckForUpdatesAction
{
    public function __construct(
        private readonly UpdateManifestClient $client,
        private readonly UpdateCompatibilityChecker $compatibility,
        private readonly UpdateHistoryStore $history,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $channel): UpdateCheck
    {
        $currentVersion = $this->client->currentVersion();
        $response = $this->client->fetch($channel);

        if (! $response['ok']) {
            $check = $this->history->record(
                actor: $actor,
                channel: $channel,
                currentVersion: $currentVersion,
                latestVersion: null,
                status: 'failed',
                endpoint: $response['endpoint'],
                httpsEndpoint: $response['https'],
                signedManifest: false,
                checksum: null,
                signature: null,
                manifest: null,
                compatibility: $this->compatibility->check(null),
                errorMessage: $response['error'],
            );

            $this->audit($actor, $check);

            return $check;
        }

        $manifest = $response['manifest'] ?? [];
        $latestVersion = $this->versionFrom($manifest);
        $compatibility = $this->compatibility->check($manifest);
        $signedManifest = (bool) ($manifest['signed'] ?? $manifest['signature'] ?? false);
        $status = $this->statusFor($currentVersion, $latestVersion, $compatibility['compatible']);

        $check = $this->history->record(
            actor: $actor,
            channel: $channel,
            currentVersion: $currentVersion,
            latestVersion: $latestVersion,
            status: $status,
            endpoint: $response['endpoint'],
            httpsEndpoint: $response['https'],
            signedManifest: $signedManifest,
            checksum: isset($manifest['checksum']) ? (string) $manifest['checksum'] : null,
            signature: isset($manifest['signature']) ? (string) $manifest['signature'] : null,
            manifest: $manifest,
            compatibility: $compatibility,
            errorMessage: $signedManifest ? null : 'Manifest signature is missing or not marked as signed. Install actions must not trust this package.',
        );

        $this->audit($actor, $check);

        return $check;
    }

    /** @param array<string, mixed> $manifest */
    private function versionFrom(array $manifest): ?string
    {
        $version = $manifest['version'] ?? $manifest['latest_version'] ?? null;

        return is_string($version) && $version !== '' ? $version : null;
    }

    private function statusFor(string $currentVersion, ?string $latestVersion, bool $compatible): string
    {
        if (! $compatible) {
            return 'incompatible';
        }

        if ($latestVersion === null) {
            return 'failed';
        }

        return version_compare($latestVersion, $currentVersion, '>') ? 'available' : 'current';
    }

    private function audit(User $actor, UpdateCheck $check): void
    {
        $this->audit->record('update.checked', $actor, null, [
            'channel' => $check->channel,
            'status' => $check->status,
            'current_version' => $check->current_version,
            'latest_version' => $check->latest_version,
            'endpoint' => $check->endpoint,
            'signed_manifest' => $check->signed_manifest,
        ], [
            'module' => 'system',
            'action' => 'Check Updates',
            'severity' => $check->status === 'failed' ? 'warning' : 'info',
            'target_type' => UpdateCheck::class,
            'target_id' => $check->getKey(),
        ]);
    }
}
