@props(['name', 'label', 'selected' => null, 'assets' => [], 'ready' => false, 'canSelect' => false, 'canUpload' => false])

@if ($ready)
    <x-media.picker
        :name="$name"
        :label="$label"
        :selected="$selected"
        :assets="$assets"
        type="seo"
        :can-select="$canSelect"
        :can-upload="$canUpload"
    />
@else
    <div>
        <label for="{{ str($name)->slug() }}" class="text-sm font-extrabold text-stone-950">{{ $label }}</label>
        <input id="{{ str($name)->slug() }}" name="{{ $name }}" value="{{ old($name) }}" inputmode="numeric" autocomplete="off" placeholder="Media asset ID" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
    </div>
@endif
