@props(['status'])

@php
    $styles = [
        'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'missing' => 'border-amber-200 bg-amber-50 text-amber-900',
        'noindex' => 'border-red-200 bg-red-50 text-red-800',
        'excluded' => 'border-stone-200 bg-stone-50 text-stone-700',
        'draft' => 'border-stone-200 bg-white text-stone-700',
    ];
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold {{ $styles[$status] ?? $styles['draft'] }}">
    {{ str($status)->headline() }}
</span>
