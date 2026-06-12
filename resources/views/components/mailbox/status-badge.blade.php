@props(['status'])
@php
    $styles = ['active' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'expired' => 'border-amber-200 bg-amber-50 text-amber-800', 'locked' => 'border-red-200 bg-red-50 text-red-800', 'trashed' => 'border-stone-300 bg-stone-100 text-stone-700'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-md border px-2.5 text-xs font-extrabold '.($styles[$status] ?? $styles['trashed'])]) }}>{{ str($status)->headline() }}</span>
