@props(['signal', 'canReview' => false, 'canResolve' => false])

<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-security.signal-severity-badge :severity="$signal->severity" />
                <x-security.signal-status-badge :status="$signal->status" />
                <span class="text-xs font-extrabold uppercase text-stone-500">{{ str($signal->source_module)->replace('-', ' ')->headline() }}</span>
            </div>
            <h3 class="mt-3 text-base font-extrabold text-stone-950">{{ str($signal->signal_type)->replace('_', ' ')->headline() }}</h3>
            <p class="mt-1 text-sm leading-6 text-stone-600">
                {{ number_format($signal->occurrence_count) }} occurrences · first {{ $signal->first_seen_at->diffForHumans() }} · last {{ $signal->last_seen_at->diffForHumans() }}
            </p>
        </div>
        @if ($signal->ip_hash)
            <span class="max-w-full truncate rounded-md bg-stone-100 px-2 py-1 font-mono text-xs font-bold text-stone-600" title="Anonymized IP fingerprint">
                IP {{ substr($signal->ip_hash, 0, 12) }}…
            </span>
        @endif
    </div>

    <dl class="mt-4 grid gap-3 border-y border-stone-200 py-4 text-sm sm:grid-cols-3">
        <div>
            <dt class="text-xs font-extrabold uppercase text-stone-500">Target readiness</dt>
            <dd class="mt-1 font-semibold text-stone-800">{{ $signal->target_reference ?: 'No target reference' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-extrabold uppercase text-stone-500">Actor readiness</dt>
            <dd class="mt-1 font-semibold text-stone-800">{{ $signal->actor?->display_name ?: $signal->actor?->name ?: 'Anonymous or system' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-extrabold uppercase text-stone-500">Reviewed by</dt>
            <dd class="mt-1 font-semibold text-stone-800">{{ $signal->reviewer?->display_name ?: $signal->reviewer?->name ?: 'Not reviewed' }}</dd>
        </div>
    </dl>

    <div class="mt-4">
        <x-security.signal-action-bar :signal="$signal" :can-review="$canReview" :can-resolve="$canResolve" />
    </div>
</article>
