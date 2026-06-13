<?php

namespace App\Services\PublicSite;

class PublicFaqSchemaService
{
    /** @param array<int, array<string, mixed>> $faqSections */
    public function schema(array $faqSections): ?array
    {
        $questions = collect($faqSections)
            ->filter(fn (array $section): bool => (bool) ($section['schema_allowed'] ?? false))
            ->flatMap(fn (array $section): array => $section['items'] ?? [])
            ->take(12)
            ->map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => strip_tags((string) $item['title']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags((string) ($item['content'] ?? '')),
                ],
            ])
            ->values();

        if ($questions->count() < 4) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions->all(),
        ];
    }
}
