<?php

namespace App\Actions\Pages;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Pages\PageSlugService;
use Illuminate\Support\Facades\DB;

class UpdatePageAction
{
    public function __construct(
        private readonly PageSlugService $slugs,
        private readonly PublishPageAction $publisher,
        private readonly AuditLogger $audit,
        private readonly AttachMediaUsageAction $attachMediaUsage,
        private readonly DetachMediaUsageAction $detachMediaUsage,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, Page $page, array $payload): Page
    {
        return DB::transaction(function () use ($actor, $page, $payload): Page {
            $before = $page->only(['locale_id', 'title', 'slug', 'excerpt', 'content', 'page_type', 'status', 'content_readiness', 'featured_media_id', 'published_at']);
            $payload['status'] = $this->publisher->statusForIntent($payload['intent'] ?? null, (string) ($payload['status'] ?? $page->status));
            unset($payload['intent']);

            $payload['slug'] = $payload['slug'] ?: $this->slugs->fromTitle((string) $payload['title']);
            $payload['published_at'] = $payload['status'] === 'published'
                ? ($payload['published_at'] ?? $page->published_at ?? now())
                : ($payload['published_at'] ?? null);

            $page->update($payload);
            $this->syncMediaUsage($actor, $page, $before['featured_media_id'] ?? null);

            $changed = collect($page->only(array_keys($before)))
                ->filter(fn (mixed $value, string $key): bool => $before[$key] !== $value)
                ->keys()
                ->all();

            if ($changed !== []) {
                $this->audit->record('page.updated', $actor, null, [
                    'page_id' => $page->id,
                    'changed_keys' => $changed,
                ], ['module' => 'pages', 'action' => 'Update page', 'target' => $page]);

                if (($before['status'] ?? null) !== $page->status && in_array($page->status, ['published', 'hidden'], true)) {
                    $this->audit->record('page.'.$page->status, $actor, null, [
                        'page_id' => $page->id,
                        'slug' => $page->slug,
                    ], ['module' => 'pages', 'action' => str($page->status)->headline().' page', 'target' => $page]);
                }
            }

            return $page;
        });
    }

    private function syncMediaUsage(User $actor, Page $page, mixed $previousMediaId): void
    {
        if ((int) $previousMediaId === (int) $page->featured_media_id) {
            return;
        }

        $usage = [
            'module' => 'pages',
            'usage_context' => 'page_studio',
            'slot' => 'featured_media_id',
            'usable_type' => Page::class,
            'usable_id' => (string) $page->id,
        ];

        $this->detachMediaUsage->handle($actor, $usage);

        $asset = $page->featured_media_id ? MediaAsset::query()->find($page->featured_media_id) : null;
        if ($asset) {
            $this->attachMediaUsage->handle($actor, $asset, [
                ...$usage,
                'label' => 'Page featured image',
            ]);
        }
    }
}
