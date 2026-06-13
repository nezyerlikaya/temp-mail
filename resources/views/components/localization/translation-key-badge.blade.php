@props(['keyName'])

<span {{ $attributes->merge(['class' => 'inline-flex max-w-full items-center rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1 font-mono text-xs font-bold text-stone-800']) }}>
    <span class="truncate">{{ $keyName }}</span>
</span>
