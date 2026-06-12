<?php

namespace App\Services\Security;

use App\Models\UserAuditEvent;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FailedLoginService
{
    /** @return array{count_24h: int, recent: array<int, array<string, mixed>>, status: string, message: string} */
    public function summary(): array
    {
        if (! $this->tableIsReady()) {
            return ['count_24h' => 0, 'recent' => [], 'status' => 'passive', 'message' => 'Audit storage is not ready yet.'];
        }

        $count = UserAuditEvent::query()
            ->where('event', 'auth.login_failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $recent = UserAuditEvent::query()
            ->where('event', 'auth.login_failed')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (UserAuditEvent $event): array => [
                'email' => $event->metadata['email'] ?? 'Protected account',
                'reason' => $event->metadata['reason'] ?? 'Invalid credentials',
                'created_at' => $event->created_at,
            ])
            ->all();

        return [
            'count_24h' => $count,
            'recent' => $recent,
            'status' => $count > 0 ? 'ready' : 'passive',
            'message' => $count > 0 ? "{$count} failed attempts recorded in the last 24 hours." : 'No failed login attempts recorded in the last 24 hours.',
        ];
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('user_audit_events');
        } catch (Throwable) {
            return false;
        }
    }
}
