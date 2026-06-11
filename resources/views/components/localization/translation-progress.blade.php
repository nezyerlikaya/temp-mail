@props(['score'])

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <div class="flex items-center justify-between gap-3 text-xs font-bold text-stone-500">
        <span>Readiness</span>
        <span>{{ $score }}%</span>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-stone-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $score }}">
        <div class="h-full rounded-full {{ $score >= 100 ? 'bg-emerald-500' : ($score >= 50 ? 'bg-teal-500' : 'bg-amber-500') }}" style="width: {{ $score }}%"></div>
    </div>
</div>
