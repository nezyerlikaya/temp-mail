@props(['connection', 'canTest' => false, 'extension'])

<x-admin.card title="Connection readiness" description="Read-only checks verify reachability and mailbox access without importing or modifying messages.">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <x-mail.connection-status-badge :status="$connection->status" />
            <p class="mt-2 text-xs font-semibold text-stone-500">
                {{ $connection->last_tested_at ? 'Last tested '.$connection->last_tested_at->diffForHumans() : 'This connection has not been tested.' }}
            </p>
        </div>
        <form method="POST" action="{{ route('admin.imap-smtp.test', $connection) }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()">
            @csrf
            <button @disabled(! $canTest || ! $extension['ready']) x-bind:disabled="submitting || {{ $canTest && $extension['ready'] ? 'false' : 'true' }}" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                <i data-lucide="plug-zap" class="size-4" aria-hidden="true"></i>
                <span x-show="!submitting">Test connection</span>
                <span x-cloak x-show="submitting">Testing...</span>
            </button>
        </form>
    </div>

    @if ($connection->last_test_result)
        <div class="mt-5 divide-y divide-stone-200 border-y border-stone-200">
            @foreach ($connection->last_test_result['checks'] ?? [] as $name => $check)
                <div class="grid gap-1 py-3 sm:grid-cols-[150px_minmax(0,1fr)]">
                    <p class="text-sm font-extrabold text-stone-900">{{ str($name)->headline() }}</p>
                    <p class="text-sm leading-6 {{ $check['status'] === 'passed' ? 'text-emerald-700' : ($check['status'] === 'failed' ? 'text-red-700' : 'text-stone-500') }}">
                        <span class="font-bold">{{ str($check['status'])->headline() }}.</span> {{ $check['message'] }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.card>
