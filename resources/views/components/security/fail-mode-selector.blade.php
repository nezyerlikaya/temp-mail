@props(['modes', 'selected'])

<label class="grid gap-2">
    <span class="text-sm font-bold text-stone-700">Fail mode</span>
    <select name="fail_mode" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        @foreach ($modes as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
