@props(['status'])

@php
    $styles = [
        'draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'ready' => 'border-teal-200 bg-teal-50 text-teal-800',
        'launched' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'paused' => 'border-amber-200 bg-amber-50 text-amber-900',
        'Live' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'In Review' => 'border-teal-200 bg-teal-50 text-teal-800',
        'Draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'Offline' => 'border-amber-200 bg-amber-50 text-amber-900',
        'planned' => 'border-stone-200 bg-stone-50 text-stone-700',
        'blocked' => 'border-red-200 bg-red-50 text-red-800',
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'passive' => 'border-stone-200 bg-stone-50 text-stone-700',
        'required' => 'border-teal-200 bg-teal-50 text-teal-800',
        'optional' => 'border-stone-200 bg-stone-50 text-stone-700',
        'missing' => 'border-amber-200 bg-amber-50 text-amber-900',
        'translated' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'short_text' => 'border-sky-200 bg-sky-50 text-sky-800',
        'long_text' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
        'rich_text' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-800',
        'boolean' => 'border-violet-200 bg-violet-50 text-violet-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['draft'])]) }}>
    {{ str((string) $status)->headline() }}
</span>
