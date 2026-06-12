<?php

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;

class EmailTemplateRenderer
{
    public function __construct(
        private readonly EmailTemplateVariableRegistry $variables,
        private readonly SystemEmailLayoutResolver $layout,
    ) {}

    /** @param array<string, string> $values */
    public function renderHtml(EmailTemplate $template, array $values): string
    {
        return $this->replace($this->layout($template), $values, true);
    }

    /** @param array<string, string> $values */
    public function renderPlain(EmailTemplate $template, array $values): string
    {
        return $this->replace((string) $template->plain_text_body, $values, false);
    }

    private function layout(EmailTemplate $template): string
    {
        return $this->layout->wrap($template->html_body, $template->preheader);
    }

    /** @param array<string, string> $values */
    private function replace(string $content, array $values, bool $escapeHtml): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function (array $match) use ($values, $escapeHtml): string {
            if (! array_key_exists($match[1], $this->variables->variables())) {
                return '';
            }

            $value = (string) ($values[$match[1]] ?? '');

            return $escapeHtml ? e($value) : $value;
        }, $content) ?? $content;
    }
}
