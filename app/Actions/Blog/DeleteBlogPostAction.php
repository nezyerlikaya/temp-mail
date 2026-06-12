<?php

namespace App\Actions\Blog;

use App\Actions\Media\DetachMediaUsageAction;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteBlogPostAction
{
    public function __construct(
        private readonly DetachMediaUsageAction $detachMediaUsage,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, BlogPost $post): void
    {
        if ($post->status !== 'trashed') {
            throw ValidationException::withMessages([
                'confirm_delete' => 'Move this post to trash before permanent deletion.',
            ]);
        }

        DB::transaction(function () use ($actor, $post): void {
            $metadata = [
                'post_id' => $post->id,
                'slug' => $post->slug,
                'status' => $post->status,
            ];

            $this->detachMediaUsage->handle($actor, [
                'module' => 'blog',
                'usage_context' => 'blog_studio',
                'slot' => 'featured_media_id',
                'usable_type' => BlogPost::class,
                'usable_id' => (string) $post->id,
            ]);

            $post->tags()->detach();
            $post->delete();

            $this->audit->record('blog_post.permanently_deleted', $actor, null, $metadata, [
                'module' => 'blog',
                'action' => 'Permanently delete blog post',
                'target_type' => BlogPost::class,
                'target_id' => $metadata['post_id'],
            ]);
        });
    }
}
