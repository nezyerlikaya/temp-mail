@props(['template', 'templateKeys', 'canUpdate' => false])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-extrabold text-stone-950">{{ $templateKeys[$template->template_key] ?? str($template->template_key)->headline() }}</p>
            <p class="mt-1 text-xs font-bold text-stone-500">{{ $template->locale?->language_name }} · updated {{ $template->updated_at?->diffForHumans() }}</p>
        </div>
        <x-emails.status-badge :status="$template->status" />
    </div>
    <p class="mt-4 line-clamp-2 text-sm font-bold text-stone-700">{{ $template->subject }}</p>
    @if ($template->preheader)
        <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $template->preheader }}</p>
    @endif
    <div class="mt-4 flex items-center justify-between gap-3">
        <p class="text-xs font-bold text-stone-500">Updated by {{ $template->updater?->name ?? 'System' }}</p>
        @if ($canUpdate)
            <a href="{{ route('admin.email-templates.edit', $template) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-3 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Edit</a>
        @endif
    </div>
</article>
