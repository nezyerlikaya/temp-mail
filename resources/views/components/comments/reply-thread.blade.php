@props(['comment'])

@if ($comment->replies->isNotEmpty())
    <div class="space-y-3 border-l-2 border-teal-200 pl-4">
        @foreach ($comment->replies as $reply)
            <article class="rounded-lg border border-stone-200 bg-stone-50 p-3">
                <div class="flex flex-wrap items-center gap-2">
                    <x-comments.status-badge :status="$reply->status" />
                    <span class="text-xs font-bold text-stone-500">{{ $reply->created_at?->format('M j, Y H:i') }}</span>
                </div>
                <p class="mt-2 text-sm font-extrabold text-stone-950">{{ $reply->author_name }}</p>
                <div class="prose prose-sm mt-2 max-w-none text-stone-700">{!! $reply->content !!}</div>
            </article>
        @endforeach
    </div>
@endif
