@props(['module'])

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-full border border-stone-200 bg-stone-50 px-2.5 text-xs font-extrabold text-stone-700']) }}>
    {{ str($module ?: 'system')->headline() }}
</span>
