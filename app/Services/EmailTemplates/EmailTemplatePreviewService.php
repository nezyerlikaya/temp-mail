<?php

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;

class EmailTemplatePreviewService
{
    public function __construct(
        private readonly EmailTemplateRenderer $renderer,
        private readonly SystemEmailLayoutResolver $layout,
    ) {}

    /** @return array<string, mixed> */
    public function preview(EmailTemplate $template): array
    {
        $sample = $this->sampleData();

        return [
            'sample' => $sample,
            'subject' => $this->replace((string) $template->subject, $sample),
            'preheader' => $this->replace((string) $template->preheader, $sample),
            'desktop_html' => $this->renderer->renderHtml($template, $sample),
            'mobile_html' => $this->renderer->renderHtml($template, $sample),
            'plain_text' => $this->renderer->renderPlain($template, $sample),
            'dark_mode_note' => 'Dark mode is an approximation. Final rendering depends on the mail client.',
            'layout' => $this->layout->readiness(),
        ];
    }

    /** @return array<string, string> */
    public function sampleData(): array
    {
        return [
            'app_name' => (string) config('app.name', 'Temp Mail Cloud'),
            'user_name' => 'Alex Morgan',
            'reset_url' => url('/password/reset/sample-token'),
            'login_url' => route('login'),
            'verification_url' => url('/email/verify/sample'),
            'premium_ends_at' => now()->addDays(7)->format('F j, Y'),
            'support_email' => (string) config('mail.from.address', 'support@example.test'),
            'abuse_email' => (string) config('mail.from.address', 'abuse@example.test'),
        ];
    }

    /** @param array<string, string> $values */
    private function replace(string $content, array $values): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', fn (array $match): string => e((string) ($values[$match[1]] ?? '')), $content) ?? $content;
    }
}
