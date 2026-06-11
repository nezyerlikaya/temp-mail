@props(['status'])

@php
    $styles = ['ready' => 'bg-emerald-100 text-emerald-800', 'attention' => 'bg-amber-100 text-amber-900', 'blocked' => 'bg-red-100 text-red-800'];
@endphp
<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <h3 class="text-sm font-extrabold text-stone-950">{{ $status['label'] }}</h3>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $styles[$status['status']] }}">{{ str($status['status'])->headline() }}</span>
    </div>
    <p class="mt-3 break-words text-sm leading-6 text-stone-600">{{ $status['detail'] }}</p>
</article>
