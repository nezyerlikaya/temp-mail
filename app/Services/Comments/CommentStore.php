<?php

namespace App\Services\Comments;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentStore
{
    public function __construct(
        private readonly CommentContentSanitizer $sanitizer,
        private readonly CommentSpamCheckService $spam,
    ) {}

    /** @param array<string, mixed> $payload */
    public function create(BlogPost $post, array $payload, Request $request, ?User $user = null): Comment
    {
        $content = $this->sanitizer->sanitize((string) $payload['content']);
        $spam = $this->spam->check($post, [...$payload, 'content' => $content], $request);
        $email = $user?->email ?: ($payload['author_email'] ?? null);

        return Comment::query()->create([
            'blog_post_id' => $post->id,
            'parent_id' => $payload['parent_id'] ?? null,
            'user_id' => $user?->id,
            'locale_id' => $post->locale_id,
            'author_name' => $user?->name ?: (string) $payload['author_name'],
            'author_email' => $email,
            'author_email_hash' => filled($email) ? hash('sha256', strtolower((string) $email)) : null,
            'ip_hash' => filled($request->ip()) ? hash('sha256', (string) $request->ip()) : null,
            'user_agent_metadata' => ['hash' => hash('sha256', (string) $request->userAgent()), 'length' => strlen((string) $request->userAgent())],
            'content' => $content,
            'content_excerpt' => Str::limit(strip_tags($content), 180, ''),
            'status' => $spam['status'],
            'spam_score' => $spam['spam_score'],
            'spam_provider' => $spam['spam_provider'],
            'provider_decision' => $spam['provider_decision'],
        ]);
    }
}
