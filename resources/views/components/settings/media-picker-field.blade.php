@props(['name', 'asset'])

<div class="rounded-md border border-stone-200 p-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <label for="{{ $name }}" class="text-sm font-extrabold text-stone-950">{{ $asset['label'] }}</label>
            <p class="mt-1 text-sm text-stone-600">{{ $asset['connected'] ? 'Media reference #'.$asset['media_id'].' connected.' : 'Using safe fallback: '.$asset['fallback'].'.' }}</p>
        </div>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $asset['connected'] ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-amber-100 text-amber-900 ring-amber-200' }}">{{ $asset['connected'] ? 'Connected' : 'Ready hook' }}</span>
    </div>
    <input id="{{ $name }}" name="{{ $name }}" type="number" min="1" value="{{ old($name, $asset['media_id']) }}" class="mt-4 block w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" placeholder="Media ID when Media Library is available" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" aria-describedby="{{ $errors->has($name) ? $name.'-error' : '' }}">
    @error($name)<p id="{{ $name }}-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
    <a href="{{ route('admin.media-library.index') }}" class="mt-3 inline-flex min-h-9 items-center justify-center rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Open Media Library hook</a>
</div>
