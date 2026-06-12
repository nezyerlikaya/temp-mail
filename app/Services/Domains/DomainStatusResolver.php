<?php

namespace App\Services\Domains;

class DomainStatusResolver
{
    /** @param array<string, array<string, mixed>> $checks */
    public function resolve(array $checks, bool $isActive): string
    {
        if (! $isActive) {
            return 'offline';
        }

        if ($checks === []) {
            return 'pending_dns';
        }

        $statuses = collect($checks)->pluck('status');

        if ($statuses->every(fn (string $status): bool => $status === 'ready')) {
            return 'ready';
        }

        if ($statuses->contains('ready')) {
            return 'degraded';
        }

        return 'pending_dns';
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return [
            'draft' => 'Draft',
            'pending_dns' => 'Pending DNS',
            'ready' => 'Ready',
            'degraded' => 'Degraded',
            'offline' => 'Offline',
        ];
    }
}
