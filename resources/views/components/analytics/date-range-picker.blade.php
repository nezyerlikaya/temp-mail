@props(['filters', 'presets'])

<div class="grid gap-3 md:grid-cols-3">
    <label class="grid gap-2">
        <span class="text-sm font-extrabold text-stone-800">Range</span>
        <select name="preset" class="min-h-11 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            @foreach($presets as $value => $label)
                <option value="{{ $value }}" @selected($filters['preset'] === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="grid gap-2">
        <span class="text-sm font-extrabold text-stone-800">From</span>
        <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="min-h-11 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
    </label>
    <label class="grid gap-2">
        <span class="text-sm font-extrabold text-stone-800">To</span>
        <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="min-h-11 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
    </label>
</div>
