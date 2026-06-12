<?php

namespace App\Services\Sections;

use App\Models\BlogPost;
use App\Models\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SectionRenderService
{
    public function __construct(private readonly SectionSeoReadinessService $seoReadiness) {}

    /** @return array<string, mixed>|null */
    public function renderable(Section $section): ?array
    {
        $section->loadMissing(['locale', 'items']);

        if ($section->status !== 'active') {
            return null;
        }

        $base = [
            'id' => $section->id,
            'type' => $section->section_type,
            'placement' => $section->placement,
            'locale' => $section->locale?->locale,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'content' => $section->content,
            'settings' => $section->settings ?? [],
        ];

        return match ($section->section_type) {
            'faq' => $this->faq($section, $base),
            'cta' => $this->cta($section, $base),
            'blog_teaser' => $this->blogTeaser($section, $base),
            'feature_grid', 'trust_security', 'abuse_notice', 'cookie_notice' => $this->simpleBlock($section, $base),
            default => null,
        };
    }

    /** @return Collection<int, array<string, mixed>> */
    public function renderableCollection(Collection $sections): Collection
    {
        return $sections
            ->map(fn (Section $section): ?array => $this->renderable($section))
            ->filter()
            ->values();
    }

    /** @return array<string, mixed> */
    public function readiness(Section $section): array
    {
        $renderable = $this->renderable($section);

        return [
            'state' => $renderable ? 'ready' : 'blocked',
            'renderable' => (bool) $renderable,
            'message' => $renderable
                ? 'This section can be resolved by public themes.'
                : $this->blockedMessage($section),
            'payload' => $renderable,
        ];
    }

    /** @param array<string, mixed> $base */
    private function faq(Section $section, array $base): ?array
    {
        $items = $this->activeItems($section);

        if ($items->isEmpty()) {
            return null;
        }

        $seo = $this->seoReadiness->forSection($section);

        return [
            ...$base,
            'items' => $items->map(fn ($item): array => [
                'title' => $item->title,
                'content' => $item->content,
            ])->values()->all(),
            'schema_allowed' => $seo['schema_allowed'],
            'seo' => $seo,
        ];
    }

    /** @param array<string, mixed> $base */
    private function cta(Section $section, array $base): ?array
    {
        $settings = $section->settings ?? [];

        if (blank($section->title) && blank($settings['button_label'] ?? null)) {
            return null;
        }

        return [
            ...$base,
            'button_label' => $settings['button_label'] ?? null,
            'button_url' => $settings['button_url'] ?? null,
        ];
    }

    /** @param array<string, mixed> $base */
    private function blogTeaser(Section $section, array $base): ?array
    {
        if (! Schema::hasTable('blog_posts')) {
            return null;
        }

        $postQuery = BlogPost::query()
            ->where('locale_id', $section->locale_id)
            ->where('status', 'published');

        if (! blank($section->settings['category_id'] ?? null)) {
            $postQuery->where('blog_category_id', (int) $section->settings['category_id']);
        }

        if (! $postQuery->exists()) {
            return null;
        }

        return [
            ...$base,
            'post_count' => (int) ($section->settings['post_count'] ?? 3),
            'layout' => $section->settings['layout'] ?? 'grid',
            'category_id' => $section->settings['category_id'] ?? null,
        ];
    }

    /** @param array<string, mixed> $base */
    private function simpleBlock(Section $section, array $base): ?array
    {
        if (blank($section->title) && blank($section->content) && $this->activeItems($section)->isEmpty()) {
            return null;
        }

        return [
            ...$base,
            'items' => $this->activeItems($section)->map(fn ($item): array => [
                'title' => $item->title,
                'content' => $item->content,
            ])->values()->all(),
        ];
    }

    private function activeItems(Section $section): Collection
    {
        return ($section->relationLoaded('items') ? $section->items : $section->items()->get())
            ->where('status', 'active')
            ->values();
    }

    private function blockedMessage(Section $section): string
    {
        if ($section->status !== 'active') {
            return 'Public rendering only resolves active sections.';
        }

        return match ($section->section_type) {
            'faq' => 'Add at least one active FAQ item before rendering.',
            'cta' => 'Add CTA title or button text before rendering.',
            'blog_teaser' => 'Blog teaser rendering waits for Blog Studio records.',
            default => 'Add publishable content before rendering.',
        };
    }
}
