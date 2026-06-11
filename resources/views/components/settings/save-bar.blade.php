@props(['group'])

<div class="sticky bottom-3 z-20 mt-6 flex flex-col gap-3 rounded-lg border border-stone-300 bg-white/95 p-3 shadow-xl shadow-stone-950/10 backdrop-blur sm:flex-row sm:items-center sm:justify-between" x-show="dirty" x-cloak role="status">
    <p class="px-1 text-sm font-semibold text-stone-700">You have unsaved {{ str($group)->headline()->lower() }} changes.</p>
    <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-5 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-wait disabled:bg-teal-900">
        <span x-show="! submitting">Save changes</span>
        <span x-cloak x-show="submitting">Saving...</span>
    </button>
</div>
