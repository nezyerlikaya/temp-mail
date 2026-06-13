<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;
use Illuminate\Support\Collection;

class IntegrationHealthService
{
    public function __construct(private readonly IntegrationSettingsStore $settings) {}

    /** @return array{connected: int, degraded: int, failed: int, disabled: int, not_tested: int} */
    public function summary(string $category, string $environment): array
    {
        return $this->settings->cards($category, $environment)
            ->countBy(fn (array $integration): string => (string) $integration['connection_status'])
            ->only(['connected', 'degraded', 'failed', 'disabled', 'not_tested'])
            ->union(['connected' => 0, 'degraded' => 0, 'failed' => 0, 'disabled' => 0, 'not_tested' => 0])
            ->all();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function history(?IntegrationSetting $setting): Collection
    {
        return collect($setting?->test_history ?? [])->take(5)->values();
    }
}
