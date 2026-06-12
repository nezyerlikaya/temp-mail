@props(['mailbox', 'message', 'canViewContent' => false])
<article class="grid gap-3 border-b border-stone-200 px-4 py-4 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            @if($message->isUnread())<span class="size-2.5 rounded-full bg-teal-600" aria-label="Unread"></span>@endif
            <p class="truncate text-sm font-extrabold text-stone-950">{{ $message->sender_name ?: $message->sender_email }}</p>
            <span class="text-xs font-bold text-stone-500">{{ $message->received_at->diffForHumans() }}</span>
        </div>
        <p class="mt-1 truncate text-sm font-bold text-stone-800">{{ $message->subject ?: 'No subject' }}</p>
        <p class="mt-1 line-clamp-2 text-sm text-stone-600">{{ $message->preview_text ?: 'No safe text preview available.' }}</p>
        <p class="mt-2 text-xs font-bold text-stone-500">{{ number_format($message->message_size / 1024, 1) }} KB · {{ $message->attachment_count }} attachments</p>
    </div>
    @if($canViewContent)
        <a href="{{ route('admin.mailbox-operations.messages.show', [$mailbox, $message]) }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 bg-white px-3 text-sm font-extrabold text-stone-800 transition hover:border-teal-600 hover:text-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Review message</a>
    @else
        <span class="text-xs font-bold text-stone-500">Content access restricted</span>
    @endif
</article>
