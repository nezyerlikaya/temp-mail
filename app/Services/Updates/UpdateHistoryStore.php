<?php

namespace App\Services\Updates;

use App\Models\UpdateCheck;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class UpdateHistoryStore
{
    /** @return Collection<int, UpdateCheck> */
    public function recent(int $limit = 10): Collection
    {
        return UpdateCheck::query()
            ->with('checker')
            ->latest('checked_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function latest(): ?UpdateCheck
    {
        return UpdateCheck::query()->latest('checked_at')->latest('id')->first();
    }

    /**
     * @param  array<string, mixed>|null  $manifest
     * @param  array<string, mixed>|null  $compatibility
     */
    public function record(
        User $actor,
        string $channel,
        string $currentVersion,
        ?string $latestVersion,
        string $status,
        string $endpoint,
        bool $httpsEndpoint,
        bool $signedManifest,
        ?string $checksum,
        ?string $signature,
        ?array $manifest,
        ?array $compatibility,
        ?string $errorMessage = null,
    ): UpdateCheck {
        $check = UpdateCheck::query()->create([
            'uuid' => (string) Str::uuid(),
            'channel' => $channel,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'status' => $status,
            'endpoint' => $endpoint,
            'https_endpoint' => $httpsEndpoint,
            'signed_manifest' => $signedManifest,
            'checksum' => $checksum,
            'signature' => $signature,
            'manifest' => $manifest,
            'compatibility' => $compatibility,
            'error_message' => $errorMessage,
            'checked_by' => $actor->getKey(),
            'checked_at' => now(),
        ]);

        $this->prune();

        return $check;
    }

    private function prune(): void
    {
        $limit = (int) config('updates.history_limit', 50);

        $ids = UpdateCheck::query()
            ->latest('checked_at')
            ->latest('id')
            ->limit($limit)
            ->pluck('id');

        UpdateCheck::query()
            ->whereNotIn('id', $ids)
            ->delete();
    }
}
