<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use App\Services\Notifications\NotificationService;

class AbuseNotificationDispatcher
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function dispatchNewCase(AbuseReport $report): void
    {
        if (! in_array($report->priority, ['high', 'critical'], true)) {
            return;
        }

        $this->notifications->dispatch([
            'event_key' => 'new_abuse_report',
            'type' => 'trust',
            'severity' => $report->priority === 'critical' ? 'critical' : 'warning',
            'title' => 'New '.str($report->priority)->headline().' abuse report',
            'message' => 'Case '.$report->case_reference.' requires review.',
            'related_module' => 'trust',
            'target_type' => AbuseReport::class,
            'target_id' => $report->id,
            'action_route' => 'admin.abuse-reports.show',
            'action_parameters' => ['abuseReport' => $report->case_reference],
        ], sendEmail: false);
    }
}
