<?php

namespace App\Services\Sections;

class SectionPlacementRegistry
{
    /** @return array<string, string> */
    public function options(): array
    {
        return [
            'home.primary' => 'Home primary',
            'home.secondary' => 'Home secondary',
            'home.faq' => 'Home FAQ',
            'pricing.before_plans' => 'Pricing before plans',
            'pricing.after_plans' => 'Pricing after plans',
            'blog.sidebar' => 'Blog sidebar',
            'mailbox.promo' => 'Mailbox promo',
            'legal.notice' => 'Legal notice',
        ];
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->options());
    }
}
