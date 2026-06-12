<?php

namespace Database\Factories;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SystemNotification> */
class SystemNotificationFactory extends Factory
{
    protected $model = SystemNotification::class;

    public function definition(): array
    {
        return [
            'recipient_user_id' => User::factory()->admin(),
            'event_key' => 'domain_health_failed',
            'type' => 'infrastructure',
            'severity' => 'warning',
            'title' => 'Domain health failed',
            'message' => 'A monitored domain failed the latest health check.',
            'related_module' => 'mail-infrastructure',
            'target_type' => null,
            'target_id' => null,
            'action_route' => 'admin.domains.index',
            'action_parameters' => [],
            'action_url' => null,
            'read_at' => null,
            'archived_at' => null,
            'occurrence_count' => 1,
            'first_occurred_at' => now(),
            'last_occurred_at' => now(),
            'snoozed_until' => null,
            'deduplication_key' => null,
            'digest_status' => null,
            'email_status' => null,
        ];
    }

    public function critical(): static
    {
        return $this->state(fn (): array => [
            'severity' => 'critical',
            'event_key' => 'failed_admin_login',
            'type' => 'security',
            'title' => 'Failed admin login',
            'related_module' => 'trust',
            'action_route' => 'admin.security-defense-center.index',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'archived_at' => now(),
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (): array => [
            'read_at' => now(),
        ]);
    }
}
