@props(['canForce' => false])

<x-admin.card title="Force logout sessions" description="Invalidate authenticated database sessions without displaying identifiers or tokens.">
    <form method="POST" action="{{ route('admin.security-defense-center.force-logout') }}" class="space-y-4" x-data="{ confirmation: '', submitting: false }" x-on:submit="if (submitting || confirmation !== 'LOG OUT SESSIONS') { $event.preventDefault(); return; } submitting = true">
        @csrf

        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950">
            This action logs out authenticated database sessions except the current browser session. It requires explicit confirmation.
        </div>

        <label class="grid gap-2">
            <span class="text-sm font-bold text-stone-700">Type LOG OUT SESSIONS</span>
            <input name="confirmation" x-model="confirmation" @disabled(! $canForce) autocomplete="off" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
            @error('confirmation')
                <span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <button type="submit" @disabled(! $canForce) x-bind:disabled="confirmation !== 'LOG OUT SESSIONS' || submitting || {{ $canForce ? 'false' : 'true' }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-red-700 px-4 text-sm font-extrabold text-white transition hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-700/20 disabled:cursor-not-allowed disabled:bg-stone-400">
            <span x-show="!submitting">Force logout sessions</span>
            <span x-cloak x-show="submitting">Working...</span>
        </button>
    </form>
</x-admin.card>
