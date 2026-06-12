<?php

namespace App\Services\Security;

use App\Models\AbuseSignal;

class SecuritySignalDeduplicator
{
    /** @param array<string, mixed> $payload */
    public function key(array $payload): string
    {
        return hash('sha256', implode('|', [
            (string) ($payload['signal_type'] ?? ''),
            (string) ($payload['source_module'] ?? ''),
            (string) ($payload['target_reference'] ?? ''),
            (string) ($payload['actor_user_id'] ?? ''),
            (string) ($payload['ip_hash'] ?? ''),
        ]));
    }

    /** @param array<string, mixed> $payload */
    public function findOpen(array $payload): ?AbuseSignal
    {
        return AbuseSignal::query()
            ->where('deduplication_key', $this->key($payload))
            ->whereIn('status', ['open', 'reviewing'])
            ->first();
    }
}
