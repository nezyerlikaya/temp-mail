@props(['role'])

@php
    $styles = [
        'admin' => 'bg-violet-100 text-violet-800 ring-violet-200',
        'moderator' => 'bg-amber-100 text-amber-900 ring-amber-200',
        'author' => 'bg-cyan-100 text-cyan-800 ring-cyan-200',
        'member' => 'bg-stone-100 text-stone-700 ring-stone-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset '.($styles[$role] ?? $styles['member'])]) }}>
    {{ str($role)->headline() }}
</span>
