@props(['card'])

<article class="rounded-md border border-stone-200 p-3">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-extrabold text-stone-950">{{ $card['locale']->language_name }}</p>
            <p class="text-xs font-bold text-stone-500">{{ strtoupper($card['locale']->locale) }} · {{ strtoupper($card['direction']) }}</p>
        </div>
        <span class="rounded-full px-2 py-1 text-xs font-extrabold ring-1 {{ $card['status'] === 'Ready' ? 'bg-emerald-50 text-emerald-900 ring-emerald-100' : 'bg-amber-50 text-amber-900 ring-amber-100' }}">{{ $card['status'] }}</span>
    </div>
    <p class="mt-2 text-xs font-semibold text-stone-600">Scripts: {{ implode(', ', array_map(fn ($script) => str($script)->replace('_', ' ')->headline()->toString(), $card['scripts'])) }}</p>
    @if ($card['missing'])
        <p class="mt-2 text-xs font-bold text-amber-800">Missing: {{ implode(', ', $card['missing']) }}</p>
    @endif
</article>
