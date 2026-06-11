<?php

namespace App\Services\Audit;

use App\Models\User;
use App\Models\UserAuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditLogger
{
    public function __construct(
        private readonly AuditSanitizer $sanitizer,
        private readonly AuditCorrelationResolver $correlation,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $event,
        ?User $actor = null,
        ?User $subject = null,
        array $metadata = [],
        array $context = [],
    ): ?UserAuditEvent {
        if (! $this->tableIsReady()) {
            return null;
        }

        $request = request();
        $target = $context['target'] ?? $subject;

        return UserAuditEvent::query()->create([
            'actor_id' => $actor?->getKey(),
            'subject_id' => $subject?->getKey(),
            'event' => $event,
            'module' => $context['module'] ?? $this->moduleFrom($event),
            'action' => $context['action'] ?? $this->actionFrom($event),
            'severity' => $context['severity'] ?? $this->severityFor($event),
            'correlation_id' => $context['correlation_id'] ?? $this->correlation->resolve($request),
            'ip_address' => $context['ip_address'] ?? $request->ip(),
            'user_agent' => $context['user_agent'] ?? $request->userAgent(),
            'target_type' => $context['target_type'] ?? ($target instanceof Model ? $target::class : null),
            'target_id' => $context['target_id'] ?? ($target instanceof Model ? $target->getKey() : null),
            'target_url' => $context['target_url'] ?? null,
            'route_name' => $context['route_name'] ?? $request->route()?->getName(),
            'request_method' => $context['request_method'] ?? $this->method($request),
            'metadata' => $this->sanitizer->sanitize($metadata),
        ]);
    }

    /** @param array<string, mixed> $metadata */
    public function system(string $event, array $metadata = [], array $context = []): ?UserAuditEvent
    {
        return $this->record($event, null, null, $metadata, ['module' => 'system', ...$context]);
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('user_audit_events') && Schema::hasColumn('user_audit_events', 'severity');
        } catch (Throwable) {
            return false;
        }
    }

    private function moduleFrom(string $event): string
    {
        return str($event)->before('.')->slug()->toString() ?: 'system';
    }

    private function actionFrom(string $event): string
    {
        return str($event)->after('.')->replace('_', ' ')->headline()->toString() ?: 'Recorded';
    }

    private function severityFor(string $event): string
    {
        return match (true) {
            str_contains($event, 'failed'),
            str_contains($event, 'suspended'),
            str_contains($event, 'rolled_back') => 'warning',
            str_contains($event, 'role_changed'),
            str_contains($event, 'security'),
            str_contains($event, 'settings'),
            str_contains($event, 'backup') => 'critical',
            default => 'info',
        };
    }

    private function method(Request $request): ?string
    {
        return app()->runningInConsole() ? null : $request->method();
    }
}
