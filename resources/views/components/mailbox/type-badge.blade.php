@props(['type'])
@php
    $styles = ['guest' => 'border-sky-200 bg-sky-50 text-sky-800', 'registered' => 'border-teal-200 bg-teal-50 text-teal-800', 'premium' => 'border-violet-200 bg-violet-50 text-violet-800', 'system' => 'border-stone-300 bg-stone-100 text-stone-800'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-md border px-2.5 text-xs font-extrabold '.($styles[$type] ?? $styles['guest'])]) }}>{{ str($type)->headline() }}</span>
