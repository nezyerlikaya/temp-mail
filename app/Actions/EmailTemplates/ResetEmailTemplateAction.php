<?php

namespace App\Actions\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class ResetEmailTemplateAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function readiness(User $actor, EmailTemplate $template): array
    {
        $this->audit->record('email_template.reset_readiness', $actor, null, [
            'email_template_id' => $template->id,
            'template_key' => $template->template_key,
            'locale_id' => $template->locale_id,
        ], ['module' => 'email-templates', 'action' => 'Review email template reset', 'target' => $template]);

        return [
            'ready' => true,
            'message' => 'Default reset readiness recorded. Default content library will be applied in the next email templates step.',
        ];
    }
}
