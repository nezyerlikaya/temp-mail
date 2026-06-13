<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use App\Models\EmailTemplate;

class AbuseReporterNotificationService
{
    /** @return array{status: string, message: string, template: EmailTemplate|null} */
    public function readiness(AbuseReport $report): array
    {
        $template = EmailTemplate::query()->where('template_key', 'abuse_report_received')->where('status', 'active')->first();

        if (! filled($report->reporter_email)) {
            return ['status' => 'blocked', 'message' => 'No reporter email is available.', 'template' => $template];
        }

        return $template
            ? ['status' => 'ready', 'message' => 'The active Abuse Report email template is ready.', 'template' => $template]
            : ['status' => 'attention', 'message' => 'Activate the Abuse Report email template before delivery.', 'template' => null];
    }

    public function prepare(AbuseReport $report, ?string $subject, ?string $body): void
    {
        if (! filled($body)) {
            return;
        }

        $report->forceFill([
            'reporter_response_subject' => $subject ?: 'Update for abuse case '.$report->case_reference,
            'reporter_response_body' => trim(strip_tags((string) $body)),
            'reporter_response_prepared_at' => now(),
            'reporter_notification_status' => $this->readiness($report)['status'],
        ])->save();
    }
}
