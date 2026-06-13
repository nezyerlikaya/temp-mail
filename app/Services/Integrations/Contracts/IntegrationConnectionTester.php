<?php

namespace App\Services\Integrations\Contracts;

use App\Models\IntegrationSetting;

interface IntegrationConnectionTester
{
    /** @return array{status: string, error_code: string|null, message: string, provider_state: string} */
    public function test(array $definition, IntegrationSetting $setting, array $payload, array $secrets): array;
}
