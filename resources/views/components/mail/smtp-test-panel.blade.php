@props(['connection', 'canTest' => false, 'canSendTest' => false])

<x-admin.card title="SMTP readiness" description="Connection tests verify SMTP reachability and authentication. Test delivery sends one transactional message to a validated recipient.">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <x-mail.connection-status-badge :status="$connection->status" />
            <p class="mt-2 text-xs font-semibold text-stone-500">{{ $connection->last_tested_at ? 'Last tested '.$connection->last_tested_at->diffForHumans() : 'This connection has not been tested.' }}</p>
        </div>
        <form method="POST" action="{{ route('admin.imap-smtp.smtp.test', $connection) }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
            @csrf
            <button @disabled(! $canTest) x-bind:disabled="submitting || {{ $canTest ? 'false' : 'true' }}" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                <i data-lucide="plug-zap" class="size-4" aria-hidden="true"></i>
                <span x-show="!submitting">Test SMTP</span>
                <span x-cloak x-show="submitting">Testing...</span>
            </button>
        </form>
    </div>

    @if ($connection->last_test_result)
        <div class="mt-5 divide-y divide-stone-200 border-y border-stone-200">
            @foreach ($connection->last_test_result['checks'] ?? [] as $name => $check)
                <div class="grid gap-1 py-3 sm:grid-cols-[170px_minmax(0,1fr)]">
                    <p class="text-sm font-extrabold text-stone-900">{{ str($name)->headline() }}</p>
                    <p class="text-sm leading-6 {{ $check['status'] === 'passed' ? 'text-emerald-700' : ($check['status'] === 'failed' ? 'text-red-700' : 'text-stone-500') }}">
                        <span class="font-bold">{{ str($check['status'])->headline() }}.</span> {{ $check['message'] }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.imap-smtp.smtp.send-test', $connection) }}" class="mt-5 grid gap-3 border-t border-stone-200 pt-5" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
        @csrf
        <label class="grid gap-2">
            <span class="text-sm font-bold text-stone-700">Test recipient</span>
            <input type="email" name="recipient" inputmode="email" autocomplete="email" required value="{{ old('recipient') }}" aria-invalid="{{ $errors->has('recipient') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('recipient') ? 'smtp-recipient-error' : 'smtp-recipient-help' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            <span id="smtp-recipient-help" class="text-xs text-stone-500">Use a mailbox you control. This sends one transactional readiness message.</span>
            @error('recipient')<span id="smtp-recipient-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
        </label>
        <button @disabled(! $canSendTest) x-bind:disabled="submitting || {{ $canSendTest ? 'false' : 'true' }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 bg-white px-4 text-sm font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400">
            <span x-show="!submitting">Send test email</span>
            <span x-cloak x-show="submitting">Sending...</span>
        </button>
    </form>
</x-admin.card>
