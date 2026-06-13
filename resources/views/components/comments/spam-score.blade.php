@props(['score'])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <div class="flex items-center justify-between text-xs font-bold text-stone-500">
        <span>Spam score</span>
        <span>{{ $score }}/100</span>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-stone-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $score }}">
        <div class="h-full rounded-full {{ $score >= 70 ? 'bg-red-500' : ($score >= 35 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $score }}%"></div>
    </div>
</div>
