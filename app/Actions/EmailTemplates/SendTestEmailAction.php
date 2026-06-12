<?php

namespace App\Actions\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\EmailTemplates\EmailTemplateDeliverabilityService;
use App\Services\EmailTemplates\EmailTemplatePreviewService;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestEmailAction
{
    public function __construct(
        private readonly EmailTemplatePreviewService $preview,
        private readonly EmailTemplateDeliverabilityService $deliverability,
        private readonly AuditLogger $audit,
    ) {}

    /** @return array{status: string, message: string} */
    public function handle(User $actor, EmailTemplate $template, string $recipient): array
    {
        $readiness = $this->deliverability->readiness();

        if (! $readiness['ready']) {
            return ['status' => 'failed', 'message' => $readiness['message']];
        }

        try {
            $preview = $this->preview->preview($template);
            $subject = '[TEST] '.$preview['subject'];

            Mail::html($preview['desktop_html'], function ($message) use ($recipient, $subject): void {
                $message->to($recipient)->subject($subject);
            });

            $this->audit->record('email_template.test_sent', $actor, null, [
                'email_template_id' => $template->id,
                'template_key' => $template->template_key,
                'recipient_domain' => str((string) $recipient)->after('@')->toString(),
            ], ['module' => 'email-templates', 'action' => 'Send test email', 'target' => $template]);

            return ['status' => 'sent', 'message' => 'Test email sent.'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'Test email could not be sent. Check mail configuration and logs.'];
        }
    }
}
