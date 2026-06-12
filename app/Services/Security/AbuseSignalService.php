<?php

namespace App\Services\Security;

use App\Models\AbuseSignal;
use App\Models\User;
use App\Services\Admin\AdminNavigationRegistry;
use App\Services\Audit\AuditSanitizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class AbuseSignalService
{
    private const ALLOWED_SEVERITIES = ['low', 'medium', 'high', 'critical'];

    private const ALLOWED_STATUSES = ['open', 'reviewing', 'resolved', 'ignored'];

    private const BLOCKED_METADATA_KEYS = ['body', 'content', 'message_body', 'email_body', 'raw_message', 'email', 'sender', 'recipient', 'password', 'token', 'secret', 'session_id'];

    public function __construct(
        private readonly SecuritySignalDeduplicator $deduplicator,
        private readonly SecurityNotificationDispatcher $notifications,
        private readonly AuditSanitizer $sanitizer,
    ) {}

    /** @param array<string, mixed> $payload */
    public function record(array $payload): AbuseSignal
    {
        $safe = $this->safePayload($payload);
        $existing = $this->deduplicator->findOpen($safe);
        $sourceEventId = $safe['metadata']['source_event_id'] ?? null;

        if ($existing !== null && $sourceEventId !== null && in_array($sourceEventId, $existing->metadata['source_event_ids'] ?? [], true)) {
            return $existing;
        }

        if ($existing !== null) {
            $metadata = $existing->metadata ?? [];
            $sourceIds = collect($metadata['source_event_ids'] ?? [])
                ->when($sourceEventId !== null, fn ($ids) => $ids->push($sourceEventId))
                ->unique()
                ->take(-25)
                ->values()
                ->all();

            $existing->forceFill([
                'severity' => $this->higherSeverity($existing->severity, $safe['severity']),
                'occurrence_count' => $existing->occurrence_count + 1,
                'last_seen_at' => now(),
                'metadata' => [
                    ...$metadata,
                    ...$safe['metadata'],
                    'source_event_ids' => $sourceIds,
                ],
            ])->save();

            $this->notifications->dispatchCritical($existing);

            return $existing;
        }

        $signal = AbuseSignal::query()->create([
            ...$safe,
            'occurrence_count' => 1,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'status' => 'open',
            'deduplication_key' => $this->deduplicator->key($safe),
            'metadata' => [
                ...$safe['metadata'],
                'source_event_ids' => $sourceEventId === null ? [] : [$sourceEventId],
            ],
        ]);

        $this->notifications->dispatchCritical($signal);

        return $signal;
    }

    /** @param array<string, mixed> $filters */
    public function feed(User $user, array $filters = []): LengthAwarePaginator
    {
        return AbuseSignal::query()
            ->with(['actor', 'reviewer'])
            ->when(($filters['severity'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('severity', $filters['severity']))
            ->when(($filters['signal_type'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('signal_type', $filters['signal_type']))
            ->when(($filters['source_module'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('source_module', $filters['source_module']))
            ->when(($filters['status'] ?? 'open') !== 'all', fn (Builder $query) => $query->where('status', $filters['status'] ?? 'open'))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('last_seen_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('last_seen_at', '<=', $filters['date_to']))
            ->latest('last_seen_at')
            ->paginate(8)
            ->withQueryString()
            ->through(function (AbuseSignal $signal) use ($user): AbuseSignal {
                $signal->setAttribute('safe_action_link', $this->actionLink($signal, $user));

                return $signal;
            });
    }

    /** @return array<string, array<string, string>> */
    public function filterOptions(): array
    {
        return [
            'severities' => collect(self::ALLOWED_SEVERITIES)->mapWithKeys(fn (string $value): array => [$value => str($value)->headline()->toString()])->all(),
            'statuses' => collect(self::ALLOWED_STATUSES)->mapWithKeys(fn (string $value): array => [$value => str($value)->headline()->toString()])->all(),
            'types' => collect($this->signalCatalog())->mapWithKeys(fn (array $item, string $key): array => [$key => $item['label']])->all(),
            'modules' => [
                'auth' => 'Authentication',
                'security' => 'Security',
                'mailbox' => 'Mailbox Operations',
                'mail-infrastructure' => 'Mail Infrastructure',
                'comments' => 'Comment Moderation',
                'api' => 'API Access',
            ],
        ];
    }

    /** @return array<string, array{label: string, severity: string, source_module: string}> */
    public function signalCatalog(): array
    {
        return [
            'mailbox_creation_spike' => ['label' => 'Mailbox creation spike', 'severity' => 'high', 'source_module' => 'mailbox'],
            'rate_limited_request' => ['label' => 'Rate-limited requests', 'severity' => 'medium', 'source_module' => 'security'],
            'failed_admin_login' => ['label' => 'Failed admin logins', 'severity' => 'high', 'source_module' => 'auth'],
            'blacklisted_sender_activity' => ['label' => 'Blacklisted sender activity', 'severity' => 'high', 'source_module' => 'mail-infrastructure'],
            'blocked_recipient_activity' => ['label' => 'Blocked recipient activity', 'severity' => 'high', 'source_module' => 'mail-infrastructure'],
            'suspicious_comment' => ['label' => 'Suspicious comments', 'severity' => 'medium', 'source_module' => 'comments'],
            'bot_provider_failure' => ['label' => 'Bot provider failures', 'severity' => 'high', 'source_module' => 'security'],
            'akismet_failure' => ['label' => 'Akismet failures', 'severity' => 'high', 'source_module' => 'security'],
            'security_setting_changed' => ['label' => 'Security setting changes', 'severity' => 'medium', 'source_module' => 'security'],
            'unusual_api_failure' => ['label' => 'Unusual API failures', 'severity' => 'high', 'source_module' => 'api'],
            'bot_challenge' => ['label' => 'Bot challenges', 'severity' => 'low', 'source_module' => 'security'],
            'spam_blocked' => ['label' => 'Spam blocked', 'severity' => 'low', 'source_module' => 'comments'],
        ];
    }

    /** @param array<string, mixed> $payload */
    private function safePayload(array $payload): array
    {
        $catalog = $this->signalCatalog();
        $type = array_key_exists((string) ($payload['signal_type'] ?? ''), $catalog)
            ? (string) $payload['signal_type']
            : 'rate_limited_request';
        $definition = $catalog[$type];

        return [
            'signal_type' => $type,
            'severity' => in_array($payload['severity'] ?? null, self::ALLOWED_SEVERITIES, true) ? $payload['severity'] : $definition['severity'],
            'source_module' => (string) ($payload['source_module'] ?? $definition['source_module']),
            'target_reference' => $this->safeTargetReference($payload['target_reference'] ?? null),
            'actor_user_id' => $payload['actor_user_id'] ?? null,
            'ip_hash' => filled($payload['ip'] ?? null) ? hash_hmac('sha256', (string) $payload['ip'], (string) config('app.key')) : ($payload['ip_hash'] ?? null),
            'metadata' => $this->sanitizeMetadata($payload['metadata'] ?? []),
        ];
    }

    /** @param array<string, mixed> $metadata */
    private function sanitizeMetadata(array $metadata): array
    {
        return collect($this->sanitizer->sanitize($metadata))
            ->reject(fn (mixed $value, string $key): bool => $this->isBlockedMetadataKey($key))
            ->map(fn (mixed $value): mixed => is_array($value)
                ? $this->sanitizeMetadata($value)
                : (is_string($value) ? str($value)->limit(240)->toString() : $value))
            ->all();
    }

    private function isBlockedMetadataKey(string $key): bool
    {
        return collect(self::BLOCKED_METADATA_KEYS)
            ->contains(fn (string $blockedKey): bool => str_contains(strtolower($key), $blockedKey));
    }

    private function safeTargetReference(mixed $reference): ?string
    {
        if (! filled($reference)) {
            return null;
        }

        $value = str((string) $reference)->limit(190)->toString();

        return str_contains($value, '@')
            ? 'ref:'.substr(hash_hmac('sha256', $value, (string) config('app.key')), 0, 16)
            : $value;
    }

    private function higherSeverity(string $current, string $incoming): string
    {
        $rank = array_flip(self::ALLOWED_SEVERITIES);

        return ($rank[$incoming] ?? 0) > ($rank[$current] ?? 0) ? $incoming : $current;
    }

    /** @return array{label: string, url: string}|null */
    private function actionLink(AbuseSignal $signal, User $user): ?array
    {
        $route = match ($signal->source_module) {
            'mailbox' => 'admin.mailbox-operations.index',
            'mail-infrastructure' => 'admin.blocked-lists.index',
            'comments' => 'admin.comment-moderation.index',
            'api' => 'admin.api-access.index',
            'auth' => 'admin.activity-audit-logs.index',
            default => 'admin.security-defense-center.index',
        };

        $ability = app(AdminNavigationRegistry::class)->findByRoute($route)['permission'] ?? null;

        if (! Route::has($route) || ($ability !== null && Gate::forUser($user)->denies($ability))) {
            return null;
        }

        return ['label' => 'Open related module', 'url' => route($route)];
    }
}
