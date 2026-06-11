@props([
    'name',
    'asset',
    'assets' => [],
    'canSelect' => false,
    'canUpload' => false,
])

<div class="rounded-md border border-stone-200 p-4">
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-extrabold text-stone-950">{{ $asset['label'] }}</p>
            <p class="mt-1 text-sm text-stone-600">{{ $asset['connected'] ? 'Media asset connected.' : 'Using safe fallback: '.$asset['fallback'].'.' }}</p>
        </div>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $asset['connected'] ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-amber-100 text-amber-900 ring-amber-200' }}">{{ $asset['connected'] ? 'Connected' : 'Ready hook' }}</span>
    </div>

    <x-media.picker
        :name="$name"
        :label="$asset['label'].' media'"
        :selected="$asset['selected']"
        :assets="$assets"
        type="image"
        :can-select="$canSelect"
        :can-upload="$canUpload"
    />
</div>
