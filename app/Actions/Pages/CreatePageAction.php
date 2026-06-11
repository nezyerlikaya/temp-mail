<?php

namespace App\Actions\Pages;

use App\Actions\Media\AttachMediaUsageAction;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Pages\PageSlugService;
use Illuminate\Support\Facades\DB;

class CreatePageAction
{
    public function __construct(
        private readonly PageSlugService $slugs,
        private readonly PublishPageAction $publisher,
        private readonly AuditLogger $audit,
        private readonly AttachMediaUsageAction $attachMediaUsage,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): Page
    {
        return DB::transaction(function () use ($actor, $payload): Page {
            $payload['status'] = $this->publisher->statusForIntent($payload['intent'] ?? null, (string) ($payload['status'] ?? 'draft'));
            unset($payload['intent']);

            $payload['slug'] = $payload['slug'] ?: $this->slugs->fromTitle((string) $payload['title']);
            $payload['author_id'] = $payload['author_id'] ?? $actor->id;
            $payload['published_at'] = $payload['status'] === 'published'
                ? ($payload['published_at'] ?? now())
                : ($payload['published_at'] ?? null);

            $page = Page::query()->create($payload);
            $this->syncMediaUsage($actor, $page);

            $this->audit->record('page.created', $actor, null, [
                'page_id' => $page->id,
                'locale_id' => $page->locale_id,
                'slug' => $page->slug,
                'page_type' => $page->page_type,
            ], ['module' => 'pages', 'action' => 'Create page', 'target' => $page]);

            return $page;
        });
    }

    private function syncMediaUsage(User $actor, Page $page): void
    {
        $asset = $page->featured_media_id ? MediaAsset::query()->find($page->featured_media_id) : null;

        if (! $asset) {
            return;
        }

        $this->attachMediaUsage->handle($actor, $asset, [
            'module' => 'pages',
            'usage_context' => 'page_studio',
            'slot' => 'featured_media_id',
            'usable_type' => Page::class,
            'usable_id' => (string) $page->id,
            'label' => 'Page featured image',
        ]);
    }
}
