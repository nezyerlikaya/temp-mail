<?php

namespace App\Actions\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class CreateEmailTemplateAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): EmailTemplate
    {
        $template = EmailTemplate::query()->create([...$payload, 'updated_by' => $actor->id]);

        $this->audit->record('email_template.created', $actor, null, [
            'email_template_id' => $template->id,
            'template_key' => $template->template_key,
            'locale_id' => $template->locale_id,
            'status' => $template->status,
        ], ['module' => 'email-templates', 'action' => 'Create email template', 'target' => $template]);

        return $template;
    }
}
