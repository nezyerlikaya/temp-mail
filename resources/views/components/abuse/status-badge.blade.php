@props(['status'])
@php($style = match ($status) { 'new' => 'border-teal-200 bg-teal-50 text-teal-800', 'reviewing' => 'border-sky-200 bg-sky-50 text-sky-800', 'awaiting_information' => 'border-amber-200 bg-amber-50 text-amber-900', 'resolved' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'rejected' => 'border-red-200 bg-red-50 text-red-800', default => 'border-stone-200 bg-stone-100 text-stone-700' })
<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold '.$style]) }}>{{ str($status)->replace('_', ' ')->headline() }}</span>
