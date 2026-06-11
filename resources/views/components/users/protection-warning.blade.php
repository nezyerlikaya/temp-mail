@props(['title' => 'Protected access change'])

<div {{ $attributes->merge(['class' => 'rounded-md border border-amber-200 bg-amber-50 p-4 text-amber-950']) }} role="note">
    <div class="flex gap-3">
        <i data-lucide="triangle-alert" class="mt-0.5 size-5 shrink-0" aria-hidden="true"></i>
        <div>
            <p class="text-sm font-extrabold">{{ $title }}</p>
            <div class="mt-1 text-sm leading-6 text-amber-900">{{ $slot }}</div>
        </div>
    </div>
</div>
