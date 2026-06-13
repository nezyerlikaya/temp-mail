@props(['label', 'value', 'icon' => 'check-circle-2'])

<div class="rounded-md border border-stone-200 bg-stone-50 p-3">
    <div class="flex items-center gap-2">
        <i data-lucide="{{ $icon }}" class="size-4 text-teal-700" aria-hidden="true"></i>
        <p class="text-xs font-bold uppercase text-stone-500">{{ $label }}</p>
    </div>
    <p class="mt-1 text-sm font-extrabold text-stone-950">{{ $value }}</p>
</div>
