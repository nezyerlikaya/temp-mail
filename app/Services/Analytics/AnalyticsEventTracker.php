<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;
use App\Models\Domain;
use App\Models\Locale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AnalyticsEventTracker
{
    public function __construct(
        private readonly AnalyticsMetricRegistry $registry,
        private readonly AnalyticsPrivacySanitizer $privacy,
    ) {}

    /** @param array<string, mixed> $payload */
    public function track(string $eventKey, array $payload = []): ?AnalyticsEvent
    {
        $this->registry->assertRegistered($eventKey);

        if (! $this->tableIsReady()) {
            return null;
        }

        $request = $payload['request'] ?? (app()->runningInConsole() ? null : request());

        return AnalyticsEvent::query()->create([
            'event_key' => $eventKey,
            'user_id' => $this->id($payload['user'] ?? $payload['user_id'] ?? null),
            'locale_id' => $this->id($payload['locale'] ?? $payload['locale_id'] ?? null),
            'domain_id' => $this->id($payload['domain'] ?? $payload['domain_id'] ?? null),
            'visitor_hash' => $this->privacy->visitorHash($payload['visitor_id'] ?? null, $request instanceof Request ? $request : null),
            'session_hash' => $this->privacy->sessionHash($payload['session_id'] ?? null, $request instanceof Request ? $request : null),
            'ip_hash' => $this->privacy->hashIp($payload['ip'] ?? ($request instanceof Request ? $request->ip() : null)),
            'metadata' => $this->privacy->metadata($payload['metadata'] ?? []),
        ]);
    }

    /** @param array<string, mixed> $payload */
    public function trackSafely(string $eventKey, array $payload = []): ?AnalyticsEvent
    {
        try {
            return $this->track($eventKey, $payload);
        } catch (Throwable) {
            return null;
        }
    }

    private function id(mixed $value): ?int
    {
        return match (true) {
            $value instanceof User, $value instanceof Locale, $value instanceof Domain => (int) $value->getKey(),
            filled($value) && is_numeric($value) => (int) $value,
            default => null,
        };
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('analytics_events');
        } catch (Throwable) {
            return false;
        }
    }
}
