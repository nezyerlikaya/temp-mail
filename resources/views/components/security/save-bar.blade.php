@props(['label' => 'Save changes', 'canSubmit' => true])

<div class="flex flex-col gap-3 border-t border-stone-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm font-semibold text-stone-600">{{ $slot }}</p>
    <button
        type="submit"
        @disabled(! $canSubmit)
        x-bind:disabled="submitting || {{ $canSubmit ? 'false' : 'true' }}"
        class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400"
    >
        <span x-show="!submitting">{{ $label }}</span>
        <span x-cloak x-show="submitting">Saving...</span>
    </button>
</div>
