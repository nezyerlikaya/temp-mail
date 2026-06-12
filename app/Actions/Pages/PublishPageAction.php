<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Pages\PageLifecycleService;

class PublishPageAction
{
    public function __construct(
        private readonly PageLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function statusForIntent(?string $intent, string $fallback = 'draft'): string
    {
        return match ($intent) {
            'publish' => 'published',
            'hide' => 'hidden',
            'save_draft' => 'draft',
            default => $fallback,
        };
    }

    public function handle(User $actor, Page $page): Page
    {
        $this->lifecycle->assertCanTransition($page, 'published');

        $page->update([
            'status' => 'published',
            'published_at' => $page->published_at ?? now(),
            'trashed_at' => null,
        ]);

        $this->audit->record('page.published', $actor, null, [
            'page_id' => $page->id,
            'slug' => $page->slug,
        ], ['module' => 'pages', 'action' => 'Publish page', 'target' => $page]);

        return $page;
    }
}
