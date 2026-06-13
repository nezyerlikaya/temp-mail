@props(['canUpdate' => false])

<div class="sticky bottom-3 z-20 mt-6 flex flex-col gap-3 rounded-lg border border-stone-300 bg-white/95 p-3 shadow-xl shadow-stone-950/10 backdrop-blur sm:flex-row sm:items-center sm:justify-between" x-show="dirty" x-cloak role="status">
    <p class="text-sm font-bold text-stone-600">Unsaved appearance draft changes affect only the selected public theme.</p>
    <button type="submit" x-bind:disabled="submitting || {{ $canUpdate ? 'false' : 'true' }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-stone-950 px-5 text-sm font-extrabold text-white hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:cursor-not-allowed disabled:bg-stone-400">
        <i data-lucide="save" class="size-4" aria-hidden="true"></i>
        <span x-text="submitting ? 'Saving...' : 'Save draft tokens'"></span>
    </button>
</div>
