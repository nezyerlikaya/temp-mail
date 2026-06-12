@props(['status'])
@php($classes = match($status) { 'active' => 'bg-emerald-100 text-emerald-800', 'expiring' => 'bg-amber-100 text-amber-800', 'expired' => 'bg-red-100 text-red-800', 'canceled' => 'bg-stone-100 text-stone-700', default => 'bg-stone-100 text-stone-700' })
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold {{ $classes }}">{{ str($status)->headline() }}</span>
