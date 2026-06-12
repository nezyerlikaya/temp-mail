<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Pages\PageLifecycleService;

class TrashPageAction
{
    public function __construct(
        private readonly PageLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, Page $page): Page
    {
        $this->lifecycle->assertCanTransition($page, 'trashed');

        $page->update([
            'status' => 'trashed',
            'trashed_at' => now(),
        ]);

        $this->audit->record('page.trashed', $actor, null, [
            'page_id' => $page->id,
            'slug' => $page->slug,
        ], ['module' => 'pages', 'action' => 'Trash page', 'target' => $page]);

        return $page;
    }
}
