@props(['result' => null])
@if ($result)
    @php($entry = $result['entry'] ?? null)
    <div class="rounded-lg border {{ $result['decision'] === 'blocked' ? 'border-rose-200 bg-rose-50 text-rose-950' : 'border-emerald-200 bg-emerald-50 text-emerald-950' }} p-4" role="status" aria-live="polite">
        <p class="text-sm font-extrabold">{{ str($result['decision'])->replace('_', ' ')->headline() }}</p>
        <p class="mt-1 text-sm">{{ $result['message'] }}</p>
        @if ($entry)
            <p class="mt-2 text-xs font-semibold">Matched rule #{{ $entry['id'] }} · {{ $entry['display_value'] }} · {{ $entry['source'] }}</p>
        @endif
    </div>
@endif
