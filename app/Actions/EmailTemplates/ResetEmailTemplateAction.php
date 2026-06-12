<?php

namespace App\Actions\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class ResetEmailTemplateAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, EmailTemplate $template): EmailTemplate
    {
        $defaults = $this->defaults($template->template_key);
        $template->update([
            ...$defaults,
            'status' => 'draft',
            'updated_by' => $actor->id,
        ]);

        $this->audit->record('email_template.reset', $actor, null, [
            'email_template_id' => $template->id,
            'template_key' => $template->template_key,
            'locale_id' => $template->locale_id,
        ], ['module' => 'email-templates', 'action' => 'Reset email template', 'target' => $template]);

        return $template->refresh();
    }

    /** @return array<string, string|null> */
    private function defaults(string $key): array
    {
        return match ($key) {
            'password_reset' => [
                'subject' => 'Reset your {{ app_name }} password',
                'preheader' => 'Use your secure reset link to continue.',
                'html_body' => '<p>Hello {{ user_name }},</p><p>Use this secure link to reset your {{ app_name }} password: <a href="{{ reset_url }}">Reset password</a>.</p>',
                'plain_text_body' => 'Hello {{ user_name }}, reset your {{ app_name }} password: {{ reset_url }}',
            ],
            'email_verification' => [
                'subject' => 'Verify your {{ app_name }} email',
                'preheader' => 'Confirm your email address to finish setup.',
                'html_body' => '<p>Hello {{ user_name }},</p><p>Verify your email for {{ app_name }}: <a href="{{ verification_url }}">Verify email</a>.</p>',
                'plain_text_body' => 'Hello {{ user_name }}, verify your email for {{ app_name }}: {{ verification_url }}',
            ],
            'login_alert', 'security_alert' => [
                'subject' => '{{ app_name }} security alert',
                'preheader' => 'Review recent account activity.',
                'html_body' => '<p>Hello {{ user_name }},</p><p>A security event was recorded for your {{ app_name }} account. Review your account: <a href="{{ login_url }}">Sign in</a>.</p><p>Need help? Contact {{ support_email }}.</p>',
                'plain_text_body' => 'Hello {{ user_name }}, review your {{ app_name }} account: {{ login_url }}. Support: {{ support_email }}',
            ],
            'premium_expiring' => [
                'subject' => 'Your {{ app_name }} premium access expires soon',
                'preheader' => 'Premium access ends on {{ premium_ends_at }}.',
                'html_body' => '<p>Hello {{ user_name }},</p><p>Your premium access ends on {{ premium_ends_at }}. Sign in to review your account: <a href="{{ login_url }}">Manage account</a>.</p>',
                'plain_text_body' => 'Hello {{ user_name }}, premium access ends on {{ premium_ends_at }}. Manage: {{ login_url }}',
            ],
            default => [
                'subject' => '{{ app_name }} notification',
                'preheader' => 'A system notification from {{ app_name }}.',
                'html_body' => '<p>Hello {{ user_name }},</p><p>This is a system notification from {{ app_name }}.</p><p>Need help? Contact {{ support_email }}.</p>',
                'plain_text_body' => 'Hello {{ user_name }}, this is a system notification from {{ app_name }}. Support: {{ support_email }}',
            ],
        };
    }
}
