<?php

namespace App\Services\Sections;

class SectionTypeRegistry
{
    /** @return array<string, string> */
    public function options(): array
    {
        return [
            'cta' => 'CTA',
            'faq' => 'FAQ',
            'blog_teaser' => 'Blog teaser',
            'feature_grid' => 'Feature grid',
            'trust_security' => 'Trust/security',
            'abuse_notice' => 'Abuse notice',
            'cookie_notice' => 'Cookie notice',
            'pricing_teaser' => 'Pricing teaser readiness',
            'mailbox_promo' => 'Mailbox promo readiness',
        ];
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->options());
    }
}
