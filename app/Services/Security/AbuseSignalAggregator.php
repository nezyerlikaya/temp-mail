<?php

namespace App\Services\Security;

use App\Models\UserAuditEvent;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AbuseSignalAggregator
{
    public function __construct(private readonly AbuseSignalService $signals) {}

    public function aggregateRecent(): int
    {
        if (! $this->tableIsReady()) {
            return 0;
        }

        $count = 0;

        UserAuditEvent::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->where(function ($query): void {
                $query
                    ->where('event', 'auth.login_failed')
                    ->orWhere('event', 'security.bot_provider_tested')
                    ->orWhere('event', 'security.akismet_tested')
                    ->orWhere('event', 'like', 'security.%_updated');
            })
            ->oldest()
            ->limit(250)
            ->each(function (UserAuditEvent $event) use (&$count): void {
                $payload = $this->mapAuditEvent($event);

                if ($payload === null) {
                    return;
                }

                $this->signals->record($payload);
                $count++;
            });

        return $count;
    }

    /** @return array<string, mixed>|null */
    private function mapAuditEvent(UserAuditEvent $event): ?array
    {
        $metadata = $event->metadata ?? [];

        if (in_array($event->event, ['security.bot_provider_tested', 'security.akismet_tested'], true)
            && ($metadata['status'] ?? null) !== 'failed') {
            return null;
        }

        $type = match (true) {
            $event->event === 'auth.login_failed' => 'failed_admin_login',
            $event->event === 'security.bot_provider_tested' => 'bot_provider_failure',
            $event->event === 'security.akismet_tested' => 'akismet_failure',
            str_starts_with($event->event, 'security.') && str_ends_with($event->event, '_updated') => 'security_setting_changed',
            default => null,
        };

        if ($type === null) {
            return null;
        }

        return [
            'signal_type' => $type,
            'severity' => $type === 'failed_admin_login' && ($metadata['reason'] ?? null) === 'suspended' ? 'critical' : null,
            'source_module' => $type === 'failed_admin_login' ? 'auth' : 'security',
            'target_reference' => $event->route_name,
            'actor_user_id' => $event->actor_id,
            'ip' => $event->ip_address,
            'metadata' => [
                'source_event_id' => $event->id,
                'event' => $event->event,
                'reason' => $metadata['reason'] ?? null,
                'status' => $metadata['status'] ?? null,
            ],
        ];
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('user_audit_events') && Schema::hasTable('abuse_signals');
        } catch (Throwable) {
            return false;
        }
    }
}
