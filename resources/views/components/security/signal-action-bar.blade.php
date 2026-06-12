@props(['signal', 'canReview' => false, 'canResolve' => false])

<div class="flex flex-wrap items-center gap-2">
    @if ($signal->safe_action_link)
        <a href="{{ $signal->safe_action_link['url'] }}" class="inline-flex min-h-9 items-center rounded-md border border-stone-300 bg-white px-3 text-xs font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            {{ $signal->safe_action_link['label'] }}
        </a>
    @endif

    @foreach ([['reviewing', 'Review', $canReview], ['resolved', 'Resolve', $canResolve], ['ignored', 'Ignore', $canResolve]] as [$status, $label, $allowed])
        @if ($signal->status !== $status)
            <form method="POST" action="{{ route('admin.security-defense-center.signals.status', $signal) }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $status }}">
                <button type="submit" @disabled(! $allowed) x-bind:disabled="submitting || {{ $allowed ? 'false' : 'true' }}" class="inline-flex min-h-9 items-center rounded-md px-3 text-xs font-extrabold transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-500 {{ $status === 'ignored' ? 'border border-stone-300 bg-white text-stone-700 hover:bg-stone-50 focus:ring-stone-500/20' : ($status === 'resolved' ? 'bg-emerald-700 text-white hover:bg-emerald-800 focus:ring-emerald-600/20' : 'bg-stone-950 text-white hover:bg-stone-800 focus:ring-teal-600/20') }}">
                    {{ $label }}
                </button>
            </form>
        @endif
    @endforeach
</div>
