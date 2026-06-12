@props(['forms', 'selected' => [], 'name' => 'protected_forms'])

<fieldset class="space-y-2">
    <legend class="text-sm font-bold text-stone-700">Protected forms</legend>
    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ($forms as $value => $label)
            <label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
                <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array($value, $selected, true)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
