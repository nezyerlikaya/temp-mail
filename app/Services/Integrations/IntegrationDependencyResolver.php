<?php

namespace App\Services\Integrations;

class IntegrationDependencyResolver
{
    /** @return array<int, array{owner: string, message: string, severity: string}> */
    public function warningsFor(array $integration): array
    {
        $warnings = [[
            'owner' => $integration['owner'],
            'message' => $integration['name'].' configuration supports '.$integration['owner'].' workflows. Provider clients and synchronization arrive in later steps.',
            'severity' => 'info',
        ]];

        if (! ($integration['is_active'] ?? false)) {
            return $warnings;
        }

        return [
            ...$warnings,
            ...match ($integration['category']) {
                'payments' => [[
                    'owner' => 'Plans & Memberships',
                    'message' => 'Payment provider is active while payment mode remains manual. No checkout automation was enabled.',
                    'severity' => 'warning',
                ]],
                'email_delivery' => [[
                    'owner' => 'Notifications / Email Templates',
                    'message' => 'Email provider is active while notification and template delivery dependencies must still be reviewed.',
                    'severity' => 'warning',
                ]],
                'analytics' => [[
                    'owner' => 'Product Analytics',
                    'message' => 'Analytics provider is active while Product Analytics import and tracking integration remain disabled.',
                    'severity' => 'warning',
                ]],
                'search_seo' => [[
                    'owner' => 'SEO Growth Center',
                    'message' => 'Search provider is active while SEO data ingestion remains unavailable.',
                    'severity' => 'warning',
                ]],
                'automation' => [[
                    'owner' => 'API Access',
                    'message' => 'Webhook automation is active while API Access automation remains disabled.',
                    'severity' => 'warning',
                ]],
                default => [],
            },
        ];
    }
}
