@props(['integration', 'environment', 'canTest' => false])

<form method="POST" action="{{ route('admin.integrations.test', $integration['key']) }}" x-data="{ busy: false }" x-on:submit="busy = true" x-bind:aria-busy="busy">
    @csrf
    <input type="hidden" name="environment" value="{{ $environment }}">
    <button
        type="submit"
        @disabled(! $canTest)
        x-bind:disabled="busy || {{ $canTest ? 'false' : 'true' }}"
        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-md bg-stone-950 px-3 text-sm font-extrabold text-white transition focus:outline-none focus:ring-4 focus:ring-stone-400 disabled:cursor-not-allowed disabled:bg-stone-300"
    >
        <i data-lucide="refresh-cw" class="size-4" aria-hidden="true" x-bind:class="{ 'animate-spin': busy }"></i>
        <span x-text="busy ? 'Testing' : 'Retry Test'">Retry Test</span>
    </button>
</form>
