@props(['name', 'value' => 'immediate', 'disabled' => false])

<label class="grid gap-2">
    <span class="text-xs font-extrabold uppercase text-stone-500">Digest</span>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    <select
        name="{{ $name }}"
        @disabled($disabled)
        class="min-h-10 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100 disabled:text-stone-500"
    >
        <option value="immediate" @selected($value === 'immediate')>Immediate</option>
        <option value="daily" @selected($value === 'daily')>Daily digest</option>
    </select>
</label>
