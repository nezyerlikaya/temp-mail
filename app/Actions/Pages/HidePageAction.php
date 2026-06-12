<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Pages\PageLifecycleService;

class HidePageAction
{
    public function __construct(
        private readonly PageLifecycleService $lifecycle,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, Page $page): Page
    {
        $this->lifecycle->assertCanTransition($page, 'hidden');

        $page->update(['status' => 'hidden', 'trashed_at' => null]);

        $this->audit->record('page.hidden', $actor, null, [
            'page_id' => $page->id,
            'slug' => $page->slug,
        ], ['module' => 'pages', 'action' => 'Hide page', 'target' => $page]);

        return $page;
    }
}
