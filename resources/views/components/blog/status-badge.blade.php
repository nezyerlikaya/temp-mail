@props(['status'])

@php
    $styles = [
        'draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'published' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'scheduled' => 'border-sky-200 bg-sky-50 text-sky-800',
        'hidden' => 'border-amber-200 bg-amber-50 text-amber-900',
        'trashed' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold {{ $styles[$status] ?? 'border-stone-200 bg-white text-stone-700' }}">
    {{ str($status)->headline() }}
</span>
