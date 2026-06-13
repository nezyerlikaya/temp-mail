<?php

namespace App\Services\Api;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use App\Models\ApiUsageEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ApiUsageTracker
{
    public function recordUsage(ApiKey $key): void
    {
        ApiUsageEvent::query()->updateOrCreate(
            ['api_key_id' => $key->id, 'usage_date' => today()],
            ['user_id' => $key->user_id],
        )->increment('request_count');
    }

    public function recordRequest(?ApiKey $key, string $endpoint, string $method, int $status, int $durationMs): void
    {
        ApiRequestLog::query()->create([
            'api_key_id' => $key?->id,
            'user_id' => $key?->user_id,
            'key_prefix' => $key?->key_prefix,
            'endpoint' => str($endpoint)->limit(190)->toString(),
            'method' => $method,
            'response_status' => $status,
            'duration_ms' => $durationMs,
            'requested_at' => now(),
        ]);
    }

    /** @return array<string, int> */
    public function summary(User $user, int $limit): array
    {
        $today = $this->countBetween($user, today(), today()->endOfDay());
        $month = $this->countBetween($user, now()->startOfMonth(), now()->endOfMonth());

        return [
            'requests_today' => $today,
            'requests_this_month' => $month,
            'limit' => $limit,
            'remaining' => max(0, $limit - $month),
        ];
    }

    /** @return array<string, int> */
    public function platformSummary(int $limit): array
    {
        $today = (int) ApiUsageEvent::query()->whereDate('usage_date', today())->sum('request_count');
        $month = (int) ApiUsageEvent::query()
            ->whereBetween('usage_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('request_count');

        return [
            'requests_today' => $today,
            'requests_this_month' => $month,
            'limit' => $limit,
            'remaining' => max(0, $limit - $month),
        ];
    }

    /** @return Collection<int, ApiRequestLog> */
    public function recent(User $user, int $limit = 10)
    {
        return ApiRequestLog::query()
            ->where('user_id', $user->id)
            ->latest('requested_at')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, ApiRequestLog> */
    public function recentPlatform(int $limit = 10)
    {
        return ApiRequestLog::query()
            ->latest('requested_at')
            ->limit($limit)
            ->get();
    }

    private function countBetween(User $user, Carbon $from, Carbon $to): int
    {
        return (int) ApiUsageEvent::query()
            ->where('user_id', $user->id)
            ->whereBetween('usage_date', [$from->toDateString(), $to->toDateString()])
            ->sum('request_count');
    }
}
