<?php

namespace App\Services\Integrations;

class IntegrationDependencyResolver
{
    /** @return array<string, string> */
    public function warningsFor(array $integration): array
    {
        return [
            'owner' => $integration['owner'],
            'message' => $integration['name'].' configuration supports '.$integration['owner'].' workflows. Provider clients and synchronization arrive in later steps.',
        ];
    }
}
