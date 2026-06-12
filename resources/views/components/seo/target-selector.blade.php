@props(['targets', 'selectedType' => null, 'selectedKey' => null])

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="seo-target-type" class="text-sm font-extrabold text-stone-950">Target type</label>
        <select id="seo-target-type" name="target_type" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('target_type') aria-invalid="true" aria-describedby="seo-target-type-error" @enderror>
            <option value="">Choose target type</option>
            @foreach ($targets->pluck('target_type')->unique()->values() as $type)
                <option value="{{ $type }}" @selected(old('target_type', $selectedType) === $type)>{{ str($type)->headline() }}</option>
            @endforeach
        </select>
        @error('target_type')
            <p id="seo-target-type-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="seo-target-key" class="text-sm font-extrabold text-stone-950">Target</label>
        <select id="seo-target-key" name="target_key" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('target_key') aria-invalid="true" aria-describedby="seo-target-key-error" @enderror>
            <option value="">Choose target</option>
            @foreach ($targets as $target)
                <option value="{{ $target['target_key'] }}" @selected(old('target_key', $selectedKey) === $target['target_key'])>{{ $target['label'] }} · {{ $target['locale']->locale }}</option>
            @endforeach
        </select>
        @error('target_key')
            <p id="seo-target-key-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
