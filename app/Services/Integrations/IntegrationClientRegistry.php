<?php

namespace App\Services\Integrations;

use App\Services\Integrations\Contracts\IntegrationConnectionTester;
use App\Services\Integrations\Testing\ReadinessIntegrationConnectionTester;

class IntegrationClientRegistry
{
    public function __construct(private readonly ReadinessIntegrationConnectionTester $readinessTester) {}

    public function testerFor(string $integrationKey): IntegrationConnectionTester
    {
        return $this->readinessTester;
    }
}
