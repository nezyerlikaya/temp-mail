<?php

namespace App\Services\PublicSite;

use App\Models\Locale;

class PublicHomepageViewDataService
{
    public function __construct(
        private readonly PublicViewDataService $base,
        private readonly PublicMailboxService $mailboxes,
        private readonly PublicSectionResolver $sections,
        private readonly PublicFaqSchemaService $faqSchema,
    ) {}

    /** @param array<string, mixed> $theme */
    public function home(Locale $locale, array $theme): array
    {
        $data = $this->base->home($locale, $theme);
        $sections = $this->sections->home($locale);

        return [
            ...$data,
            'mailbox_creator' => [
                'action' => route('public.mailbox.store', ['locale' => $locale->locale]),
                'domains' => $this->mailboxes->availableDomains()
                    ->map(fn ($domain): array => [
                        'id' => $domain->id,
                        'name' => $domain->display_name,
                        'domain' => $domain->domain_name,
                        'is_default' => $domain->is_default,
                    ])
                    ->values()
                    ->all(),
                'custom_alias_allowed' => $this->mailboxes->allowsCustomAlias(request()->user()),
                'bot_protection' => ['required' => false],
            ],
            'sections' => $sections,
            'faq_schema' => $this->faqSchema->schema($sections['faq'] ?? []),
        ];
    }
}
