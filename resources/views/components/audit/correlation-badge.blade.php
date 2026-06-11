@props(['id'])

@if ($id)
    <span {{ $attributes->merge(['class' => 'inline-flex max-w-full items-center gap-1.5 rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-bold text-stone-600']) }}>
        <i data-lucide="fingerprint" class="size-3.5 shrink-0" aria-hidden="true"></i>
        <span class="truncate">CID {{ $id }}</span>
    </span>
@else
    <span class="text-xs text-stone-500">No correlation ID</span>
@endif
