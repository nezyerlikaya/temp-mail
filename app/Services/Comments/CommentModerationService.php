<?php

namespace App\Services\Comments;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Locale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CommentModerationService
{
    /** @param array<string, mixed> $filters */
    public function queue(array $filters): LengthAwarePaginator
    {
        return Comment::query()
            ->with(['post.locale', 'user', 'approver'])
            ->when(($filters['status'] ?? 'pending') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['post_id'] ?? null), fn (Builder $query) => $query->where('blog_post_id', $filters['post_id']))
            ->when(filled($filters['locale_id'] ?? null), fn (Builder $query) => $query->where('locale_id', $filters['locale_id']))
            ->when(($filters['spam_score'] ?? 'all') === 'high', fn (Builder $query) => $query->where('spam_score', '>=', 70))
            ->when(($filters['spam_score'] ?? 'all') === 'low', fn (Builder $query) => $query->where('spam_score', '<', 70))
            ->when(($filters['has_links'] ?? 'all') === 'yes', fn (Builder $query) => $query->where('content', 'like', '%http%'))
            ->when(($filters['has_links'] ?? 'all') === 'no', fn (Builder $query) => $query->where('content', 'not like', '%http%'))
            ->when(($filters['akismet'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('provider_decision', $filters['akismet']))
            ->when(filled($filters['date'] ?? null), fn (Builder $query) => $query->whereDate('created_at', $filters['date']))
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $needle = (string) $filters['q'];
                $query->where(fn (Builder $inner) => $inner
                    ->where('author_name', 'like', '%'.$needle.'%')
                    ->orWhere('content_excerpt', 'like', '%'.$needle.'%'));
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    /** @return array{pending: int, approved: int, spam: int, trashed: int, today: int} */
    public function summary(): array
    {
        return [
            'pending' => Comment::query()->where('status', 'pending')->count(),
            'approved' => Comment::query()->where('status', 'approved')->count(),
            'spam' => Comment::query()->where('status', 'spam')->count(),
            'trashed' => Comment::query()->where('status', 'trashed')->count(),
            'today' => Comment::query()->whereDate('created_at', today())->count(),
        ];
    }

    public function posts(): array
    {
        return BlogPost::query()->orderBy('title')->pluck('title', 'id')->all();
    }

    public function locales(): array
    {
        return Locale::query()->orderBy('sort_order')->pluck('language_name', 'id')->all();
    }
}
