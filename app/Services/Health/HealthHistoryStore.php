<?php

namespace App\Services\Health;

use App\Models\SystemHealthCheck;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HealthHistoryStore
{
    /** @param array{overall_status: string, summary: array<string, int>, results: array<int, array<string, string>>, checked_at: string} $payload */
    public function record(array $payload, ?User $actor = null): SystemHealthCheck
    {
        $record = SystemHealthCheck::query()->create([
            'uuid' => (string) Str::uuid(),
            'overall_status' => $payload['overall_status'],
            'summary' => $payload['summary'],
            'results' => $payload['results'],
            'checked_by' => $actor?->id,
            'checked_at' => $payload['checked_at'],
        ]);

        $this->prune();

        return $record;
    }

    public function latest(): ?SystemHealthCheck
    {
        return SystemHealthCheck::query()->with('checker')->latest('checked_at')->first();
    }

    /** @return Collection<int, SystemHealthCheck> */
    public function recent(int $limit = 10): Collection
    {
        return SystemHealthCheck::query()->with('checker')->latest('checked_at')->limit($limit)->get();
    }

    private function prune(): void
    {
        $idsToKeep = SystemHealthCheck::query()->latest('checked_at')->limit(50)->pluck('id');

        SystemHealthCheck::query()->whereNotIn('id', $idsToKeep)->delete();
    }
}
