@props(['status'])
@php($classes = ['active' => 'bg-emerald-50 text-emerald-800 ring-emerald-200', 'inactive' => 'bg-stone-100 text-stone-700 ring-stone-200', 'expired' => 'bg-amber-50 text-amber-900 ring-amber-200'][$status] ?? 'bg-stone-100 text-stone-700 ring-stone-200')
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 '.$classes]) }}>{{ str($status)->headline() }}</span>
