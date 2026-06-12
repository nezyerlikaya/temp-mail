<?php

namespace App\Services\Domains;

use App\Models\Domain;
use App\Services\Notifications\NotificationService;

class DomainHealthNotificationService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function dispatchIfNeeded(Domain $domain): void
    {
        if (! in_array($domain->status, ['degraded', 'offline'], true)) {
            return;
        }

        $this->notifications->dispatch([
            'event_key' => 'domain_health_failed',
            'type' => 'domain_health',
            'severity' => $domain->status === 'offline' ? 'critical' : 'warning',
            'title' => 'Domain DNS health needs review',
            'message' => $domain->domain_name.' is '.$domain->status.'. Review DNS readiness before publishing mailbox traffic.',
            'related_module' => 'mail-infrastructure',
            'target_type' => Domain::class,
            'target_id' => $domain->id,
            'action_route' => 'admin.domains.edit',
            'action_parameters' => ['domain' => $domain->id],
        ], sendEmail: false);
    }
}
