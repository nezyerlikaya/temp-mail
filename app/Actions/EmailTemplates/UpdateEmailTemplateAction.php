<?php

namespace App\Actions\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class UpdateEmailTemplateAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, EmailTemplate $template, array $payload): EmailTemplate
    {
        $previousStatus = $template->status;
        $template->update([...$payload, 'updated_by' => $actor->id]);

        $this->audit->record('email_template.updated', $actor, null, [
            'email_template_id' => $template->id,
            'template_key' => $template->template_key,
            'locale_id' => $template->locale_id,
            'status' => $template->status,
        ], ['module' => 'email-templates', 'action' => 'Update email template', 'target' => $template]);

        if ($previousStatus !== $template->status && in_array($template->status, ['active', 'hidden'], true)) {
            $event = $template->status === 'active' ? 'email_template.activated' : 'email_template.hidden';
            $this->audit->record($event, $actor, null, [
                'email_template_id' => $template->id,
                'template_key' => $template->template_key,
                'locale_id' => $template->locale_id,
            ], ['module' => 'email-templates', 'action' => str($template->status)->headline().' email template', 'target' => $template]);
        }

        return $template->refresh();
    }

    public function setStatus(User $actor, EmailTemplate $template, string $status): EmailTemplate
    {
        $template->update([
            'status' => $status,
            'updated_by' => $actor->id,
        ]);

        $event = $status === 'active' ? 'email_template.activated' : 'email_template.hidden';
        $this->audit->record($event, $actor, null, [
            'email_template_id' => $template->id,
            'template_key' => $template->template_key,
            'locale_id' => $template->locale_id,
        ], ['module' => 'email-templates', 'action' => str($status)->headline().' email template', 'target' => $template]);

        return $template->refresh();
    }
}
