@props(['canEdit'])

<div {{ $attributes->merge(['class' => 'sticky bottom-4 z-20 flex flex-col gap-3 rounded-lg border border-stone-300 bg-white/95 p-4 shadow-xl backdrop-blur sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        <p class="text-sm font-extrabold text-stone-950" x-text="dirty ? 'Unsaved translation changes' : 'All visible changes saved'"></p>
        <p class="mt-1 text-xs font-semibold text-stone-500">Saving changed copy returns it to Draft for review.</p>
    </div>
    <button
        type="submit"
        formaction="{{ route('admin.translation-center.translations.save') }}"
        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60"
        @disabled(! $canEdit)
        x-bind:disabled="submitting"
    >
        <span x-show="! submitting">Save translations</span>
        <span x-cloak x-show="submitting">Saving...</span>
    </button>
</div>
