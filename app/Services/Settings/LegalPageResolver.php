<?php

namespace App\Services\Settings;

use App\Models\Page;
use Illuminate\Support\Facades\Schema;

class LegalPageResolver
{
    public function __construct(private readonly SettingsResolver $settings) {}

    /** @return array<string, array{page_id: int|null, connected: bool, label: string, page_title: string|null, page_status: string|null}> */
    public function pages(): array
    {
        $legal = $this->settings->group('legal');
        $labels = [
            'privacy' => 'Privacy page', 'terms' => 'Terms page', 'cookie' => 'Cookie page',
            'abuse' => 'Abuse page', 'dmca' => 'DMCA page', 'contact' => 'Contact page',
        ];

        $pages = $this->mappedPages($legal);

        return collect($labels)->mapWithKeys(function (string $label, string $key) use ($legal, $pages): array {
            $pageId = $legal[$key.'_page_id'] ?? null;
            $page = $pageId ? ($pages[(int) $pageId] ?? null) : null;

            return [$key => [
                'page_id' => $pageId,
                'connected' => $page !== null,
                'label' => $label,
                'page_title' => $page?->title,
                'page_status' => $page?->status,
            ]];
        })->all();
    }

    /** @param array<string, mixed> $legal */
    private function mappedPages(array $legal): array
    {
        if (! Schema::hasTable('pages')) {
            return [];
        }

        $ids = collect($legal)
            ->filter(fn (mixed $value, string $key): bool => str_ends_with($key, '_page_id') && filled($value))
            ->map(fn (mixed $value): int => (int) $value)
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Page::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->all();
    }
}
