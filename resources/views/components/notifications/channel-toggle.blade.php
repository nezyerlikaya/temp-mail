@props(['name', 'checked' => false, 'label', 'disabled' => false])

<label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
    <input type="hidden" name="{{ $name }}" value="0">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="1"
        @checked($checked)
        @disabled($disabled)
        class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20 disabled:opacity-50"
    >
    <span>{{ $label }}</span>
</label>
