@props(['items' => []])

<div class="mb-5 rounded-md border border-stone-200 p-3">
    <h3 class="text-sm font-extrabold text-stone-950">Required fields</h3>
    <ul class="mt-3 space-y-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2 text-sm font-semibold {{ $item['complete'] ? 'text-emerald-800' : 'text-amber-800' }}">
                <i data-lucide="{{ $item['complete'] ? 'check-circle-2' : 'circle-alert' }}" class="size-4" aria-hidden="true"></i>
                <span>{{ $item['label'] }}{{ $item['secret'] ? ' (secret)' : '' }}</span>
            </li>
        @endforeach
    </ul>
</div>
