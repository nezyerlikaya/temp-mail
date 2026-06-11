@props(['actor'])

<span {{ $attributes->merge(['class' => 'inline-flex max-w-full items-center gap-2 rounded-full border border-stone-200 bg-white px-2.5 py-1 text-xs font-bold text-stone-700']) }}>
    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-teal-700 text-[10px] font-extrabold text-white" aria-hidden="true">
        {{ $actor ? str($actor->name)->substr(0, 1)->upper() : 'S' }}
    </span>
    <span class="min-w-0 truncate">
        {{ $actor?->name ?? 'System / unknown' }}
    </span>
</span>
