@props(['avatar', 'size' => 'lg'])

@php
    $sizes = ['sm' => 'size-10 text-sm', 'lg' => 'size-16 text-lg', 'xl' => 'size-24 text-2xl'];
@endphp

<div
    {{ $attributes->merge(['class' => 'relative grid shrink-0 place-items-center rounded-full font-extrabold text-white ring-4 ring-white shadow-sm '.($sizes[$size] ?? $sizes['lg'])]) }}
    style="background-color: {{ $avatar['color'] }}"
    role="img"
    aria-label="{{ $avatar['label'] }}"
>
    {{ $avatar['initials'] }}
    @if ($avatar['has_media'])
        <span class="absolute -bottom-1 -right-1 grid size-6 place-items-center rounded-full bg-emerald-600 text-white ring-2 ring-white" title="Media reference connected">
            <i data-lucide="check" class="size-3.5" aria-hidden="true"></i>
        </span>
    @endif
</div>
