@props(['events'])
<section aria-labelledby="case-timeline-title" class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3"><div><h2 id="case-timeline-title" class="text-base font-extrabold text-stone-950">Case timeline</h2><p class="mt-1 text-sm text-stone-600">A concise operational history. Sensitive descriptions and evidence contents are excluded.</p></div><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">{{ $events->count() }} events</span></div>
    <ol class="mt-5 space-y-4">
        @forelse ($events as $event)
            <li class="relative border-l-2 border-teal-200 pl-4">
                <span class="absolute -left-[5px] top-1.5 size-2 rounded-full bg-teal-700"></span>
                <div class="flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-extrabold text-stone-900">{{ $event->summary }}</p><time class="text-xs font-semibold text-stone-500">{{ $event->created_at->format('M j, Y H:i') }}</time></div>
                <p class="mt-1 text-xs text-stone-600">{{ $event->actor?->name ?? 'System' }} · {{ str($event->event_type)->replace('_', ' ')->headline() }}</p>
            </li>
        @empty
            <li class="rounded-lg border border-dashed border-stone-300 px-4 py-6 text-center text-sm text-stone-600">The timeline will populate as reviewers work this case.</li>
        @endforelse
    </ol>
</section>
