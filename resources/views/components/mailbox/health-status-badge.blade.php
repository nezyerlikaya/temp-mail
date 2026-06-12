@props(['status'])
@php($classes = match($status) { 'healthy' => 'bg-emerald-100 text-emerald-800', 'offline' => 'bg-red-100 text-red-800', default => 'bg-amber-100 text-amber-800' })
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold {{ $classes }}">{{ str($status)->headline() }}</span>
