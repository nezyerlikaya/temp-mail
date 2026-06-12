<?php

namespace App\Actions\Pages;

use App\Actions\Media\DetachMediaUsageAction;
use App\Models\Page;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeletePageAction
{
    public function __construct(
        private readonly DetachMediaUsageAction $detachMediaUsage,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, Page $page): void
    {
        if ($page->status !== 'trashed') {
            throw ValidationException::withMessages([
                'confirm_delete' => 'Move this page to trash before permanent deletion.',
            ]);
        }

        DB::transaction(function () use ($actor, $page): void {
            $metadata = [
                'page_id' => $page->id,
                'slug' => $page->slug,
                'status' => $page->status,
            ];

            $this->detachMediaUsage->handle($actor, [
                'module' => 'pages',
                'usage_context' => 'page_studio',
                'slot' => 'featured_media_id',
                'usable_type' => Page::class,
                'usable_id' => (string) $page->id,
            ]);

            $page->delete();

            $this->audit->record('page.permanently_deleted', $actor, null, $metadata, [
                'module' => 'pages',
                'action' => 'Permanently delete page',
                'target_type' => Page::class,
                'target_id' => $metadata['page_id'],
            ]);
        });
    }
}
