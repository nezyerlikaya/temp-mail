@props(['priority'])
@php($style = match ($priority) { 'critical' => 'border-red-300 bg-red-50 text-red-900', 'high' => 'border-amber-300 bg-amber-50 text-amber-900', 'low' => 'border-stone-200 bg-stone-50 text-stone-600', default => 'border-sky-200 bg-sky-50 text-sky-800' })
<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold '.$style]) }}>{{ str($priority)->headline() }}</span>
