@props(['selectedTheme', 'canReset' => false])

<x-admin.card title="Reset Tokens" description="Reset all draft and published appearance values for the selected theme back to immutable defaults.">
    <form method="POST" action="{{ route('admin.appearance-studio.reset') }}" x-data="{ confirmed: false, submitting: false }" x-on:submit="submitting = true">
        @csrf
        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
        <label class="flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 p-3">
            <input type="checkbox" name="confirmation" value="1" x-model="confirmed" class="mt-1 size-4 rounded border-amber-300 text-amber-700 focus:ring-4 focus:ring-amber-700/20">
            <span class="text-sm font-semibold leading-6 text-amber-950">Reset {{ str($selectedTheme)->headline() }} appearance to theme defaults.</span>
        </label>
        <button type="submit" x-bind:disabled="!confirmed || submitting || {{ $canReset ? 'false' : 'true' }}" class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-amber-300 bg-white px-4 py-2.5 text-sm font-extrabold text-amber-950 transition hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-700/20 disabled:cursor-not-allowed disabled:opacity-60">
            <i data-lucide="rotate-ccw" class="size-4" aria-hidden="true"></i>
            <span x-text="submitting ? 'Resetting...' : 'Reset all tokens'"></span>
        </button>
    </form>
</x-admin.card>
