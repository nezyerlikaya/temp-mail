@props(['modes' => [], 'selected' => 'desktop'])

<label class="grid gap-2 text-sm font-bold text-stone-700">
    <span>Device</span>
    <select name="preview_mode" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
        @foreach ($modes as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
