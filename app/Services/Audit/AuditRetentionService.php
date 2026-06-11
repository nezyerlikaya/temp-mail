<?php

namespace App\Services\Audit;

use App\Models\AuditRetentionSetting;
use App\Models\User;
use App\Models\UserAuditEvent;
use Illuminate\Support\Carbon;

class AuditRetentionService
{
    public function __construct(private readonly AuditLogger $logger) {}

    /** @return array{retention_days: int, preserve_critical: bool, recommended_days: int, cutoff: Carbon, expired_non_critical: int, expired_critical: int} */
    public function status(): array
    {
        $settings = $this->settings();
        $cutoff = now()->subDays($settings->retention_days);

        return [
            'retention_days' => $settings->retention_days,
            'preserve_critical' => $settings->preserve_critical,
            'recommended_days' => 180,
            'cutoff' => $cutoff,
            'expired_non_critical' => UserAuditEvent::query()
                ->where('created_at', '<', $cutoff)
                ->where('severity', '!=', 'critical')
                ->count(),
            'expired_critical' => UserAuditEvent::query()
                ->where('created_at', '<', $cutoff)
                ->where('severity', 'critical')
                ->count(),
        ];
    }

    /** @param array{retention_days: int, preserve_critical?: bool} $data */
    public function update(User $actor, array $data): AuditRetentionSetting
    {
        $settings = $this->settings();
        $before = $settings->only(['retention_days', 'preserve_critical']);

        $settings->forceFill([
            'retention_days' => $data['retention_days'],
            'preserve_critical' => (bool) ($data['preserve_critical'] ?? false),
            'updated_by' => $actor->id,
        ])->save();

        $this->logger->record('audit.retention_updated', $actor, $actor, [
            'changes' => [
                'retention_days' => ['old' => $before['retention_days'], 'new' => $settings->retention_days],
                'preserve_critical' => ['old' => $before['preserve_critical'], 'new' => $settings->preserve_critical],
            ],
        ], [
            'module' => 'audit',
            'action' => 'Retention updated',
            'severity' => 'critical',
        ]);

        return $settings;
    }

    public function purgeExpired(): int
    {
        $settings = $this->settings();
        $query = UserAuditEvent::query()->where('created_at', '<', now()->subDays($settings->retention_days));

        if ($settings->preserve_critical) {
            $query->where('severity', '!=', 'critical');
        }

        return $query->delete();
    }

    private function settings(): AuditRetentionSetting
    {
        return AuditRetentionSetting::query()->firstOrCreate([], [
            'retention_days' => 180,
            'preserve_critical' => true,
        ]);
    }
}
