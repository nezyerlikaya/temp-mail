<?php

namespace App\Services\Appearance;

class AppearancePreviewService
{
    public function __construct(
        private readonly AppearanceCssVariableResolver $css,
        private readonly AppearanceContrastService $contrast,
    ) {}

    /** @param array<string, string> $tokens @return array<string, mixed> */
    public function build(string $theme, array $tokens): array
    {
        return [
            'theme' => $theme,
            'tokens' => $tokens,
            'style' => $this->css->inlineStyle($tokens),
            'contrast' => $this->contrast->report($tokens),
            'modes' => $this->modes(),
            'devices' => $this->devices(),
            'directions' => $this->directions(),
        ];
    }

    /** @return array<string, string> */
    public function modes(): array
    {
        return [
            'homepage' => 'Homepage',
            'mailbox' => 'Mailbox',
            'blog_card' => 'Blog card',
            'cta' => 'CTA',
            'faq' => 'FAQ',
        ];
    }

    /** @return array<string, string> */
    public function devices(): array
    {
        return [
            'desktop' => 'Desktop',
            'mobile' => 'Mobile',
        ];
    }

    /** @return array<string, string> */
    public function directions(): array
    {
        return [
            'ltr' => 'LTR',
            'rtl' => 'RTL',
        ];
    }
}
