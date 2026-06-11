@props(['checked' => false, 'disabled' => false])

<label class="flex items-start justify-between gap-4 rounded-md border border-stone-200 bg-stone-50 p-4">
    <span>
        <span class="block text-sm font-extrabold text-stone-950">Public author profile</span>
        <span class="mt-1 block text-sm leading-5 text-stone-600">Disabling this hides the future public profile while preserving author attribution.</span>
    </span>
    <span class="relative mt-0.5 inline-flex shrink-0">
        <input name="author_profile_active" type="checkbox" value="1" class="peer sr-only" @checked($checked) @disabled($disabled)>
        <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-teal-700 peer-focus-visible:ring-4 peer-focus-visible:ring-teal-700/25 peer-disabled:opacity-50"></span>
        <span class="pointer-events-none absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
    </span>
</label>
