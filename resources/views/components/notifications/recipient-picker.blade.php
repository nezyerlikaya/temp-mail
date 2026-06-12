@props(['name', 'selected' => [], 'roles' => [], 'disabled' => false])

<fieldset class="space-y-2">
    <legend class="text-xs font-extrabold uppercase text-stone-500">Recipients</legend>
    <div class="flex flex-wrap gap-2">
        @foreach ($roles as $role)
            <label class="inline-flex min-h-9 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-xs font-extrabold text-stone-700">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $role }}"
                    @checked(in_array($role, $selected, true))
                    @disabled($disabled)
                    class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20 disabled:opacity-50"
                >
                <span>{{ str($role)->headline() }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
