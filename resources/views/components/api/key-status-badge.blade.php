@props(['status'])

@php
    $styles = [
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'revoked' => 'border-red-200 bg-red-50 text-red-800',
        'expired' => 'border-amber-200 bg-amber-50 text-amber-800',
    ];
@endphp

<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold {{ $styles[$status] ?? 'border-stone-200 bg-stone-50 text-stone-700' }}">
    {{ str($status)->headline() }}
</span>
