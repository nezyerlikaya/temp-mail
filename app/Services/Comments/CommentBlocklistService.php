<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class CommentBlocklistService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function blocks(Comment $comment): bool
    {
        $hashes = array_filter([
            'email' => $comment->author_email_hash,
            'ip' => $comment->ip_hash,
        ]);

        if ($hashes === []) {
            return false;
        }

        return DB::table('comment_blocklists')
            ->where(fn ($query) => collect($hashes)->each(fn (string $hash, string $type) => $query->orWhere(fn ($inner) => $inner->where('type', $type)->where('hash', $hash))))
            ->exists();
    }

    public function block(User $actor, Comment $comment, string $type): void
    {
        $hash = $type === 'ip' ? $comment->ip_hash : $comment->author_email_hash;

        if (! filled($hash)) {
            return;
        }

        DB::table('comment_blocklists')->updateOrInsert(
            ['type' => $type, 'hash' => $hash],
            [
                'label' => $type === 'ip' ? 'IP hash '.$this->short($hash) : 'Email hash '.$this->short($hash),
                'created_by' => $actor->id,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $this->audit->record('comment.author_blocked', $actor, null, [
            'comment_id' => $comment->id,
            'block_type' => $type,
            'hash_preview' => $this->short($hash),
        ], ['module' => 'content', 'target' => $comment]);
    }

    private function short(string $hash): string
    {
        return substr($hash, 0, 10);
    }
}
