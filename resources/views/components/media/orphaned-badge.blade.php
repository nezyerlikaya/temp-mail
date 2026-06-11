@props(['count' => null])

@if ($count === null)
    <span {{ $attributes->merge(['class' => 'inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-extrabold text-amber-900']) }} x-show="count === 0">
        Orphaned
    </span>
@elseif ((int) $count === 0)
    <span {{ $attributes->merge(['class' => 'inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-extrabold text-amber-900']) }}>
        Orphaned
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-800']) }}>
        {{ $count }} uses
    </span>
@endif
