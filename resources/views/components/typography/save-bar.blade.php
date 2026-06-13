@props(['canSave' => false])

<div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-4">
    <p class="text-sm font-semibold text-stone-600">Changes apply to public theme typography resolution after save.</p>
    <button type="submit" @disabled(! $canSave) x-bind:disabled="busy || {{ $canSave ? 'false' : 'true' }}" class="inline-flex min-h-11 items-center gap-2 rounded-md bg-stone-950 px-4 py-2 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20 disabled:cursor-not-allowed disabled:opacity-60">
        <i data-lucide="save" class="size-4" aria-hidden="true"></i>
        <span x-text="busy ? 'Saving...' : 'Save assignment'">Save assignment</span>
    </button>
</div>
