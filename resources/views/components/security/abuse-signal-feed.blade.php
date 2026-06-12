@props(['signals', 'canReview' => false, 'canResolve' => false])

<section aria-labelledby="abuse-signal-feed-title">
    <div class="mb-4">
        <h2 id="abuse-signal-feed-title" class="text-lg font-extrabold text-stone-950">Abuse signal queue</h2>
        <p class="mt-1 text-sm leading-6 text-stone-600">Repeated identical events are grouped into a single operational signal.</p>
    </div>

    <div class="space-y-3">
        @forelse ($signals as $signal)
            <x-security.abuse-signal-card :signal="$signal" :can-review="$canReview" :can-resolve="$canResolve" />
        @empty
            <x-security.empty-state />
        @endforelse
    </div>

    @if ($signals->hasPages())
        <div class="mt-5">{{ $signals->links() }}</div>
    @endif
</section>
