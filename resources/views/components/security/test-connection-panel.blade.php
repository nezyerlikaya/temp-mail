@props(['target', 'status', 'message', 'history' => [], 'canTest' => false])

<x-admin.card title="Connection readiness" description="Test configuration without exposing provider secrets.">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <x-security.status-badge :status="$status" />
            <span class="text-sm font-semibold text-stone-600">{{ $message }}</span>
        </div>

        <form method="POST" action="{{ route('admin.security-defense-center.test') }}">
            @csrf
            <input type="hidden" name="target" value="{{ $target }}">
            <button type="submit" @disabled(! $canTest) class="inline-flex min-h-10 items-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                Test connection
            </button>
        </form>

        <x-security.connection-history :history="$history" />
    </div>
</x-admin.card>
