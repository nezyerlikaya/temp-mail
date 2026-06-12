@props(['health', 'canRun' => false])

<x-admin.card title="Mail infrastructure health" description="Combined readiness across domains, inbound IMAP, outbound SMTP, and runtime extensions.">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase text-stone-500">Overall</p>
            <p class="mt-1 text-2xl font-black text-stone-950">{{ str($health['overall'])->headline() }}</p>
            <p class="mt-1 text-sm text-stone-600">{{ $health['healthy'] }} healthy · {{ $health['warning'] }} warning · {{ $health['failed'] }} failed</p>
        </div>
        <form method="POST" action="{{ route('admin.imap-smtp.run-all-checks') }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()">
            @csrf
            <button @disabled(! $canRun) x-bind:disabled="submitting || {{ $canRun ? 'false' : 'true' }}" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                <i data-lucide="activity" class="size-4" aria-hidden="true"></i>
                <span x-show="!submitting">Run all checks</span>
                <span x-cloak x-show="submitting">Checking...</span>
            </button>
        </form>
    </div>

    <div class="mt-5 grid gap-3 xl:grid-cols-4">
        @foreach ($health['cards'] as $card)
            <x-mail.infrastructure-health-card :card="$card" />
        @endforeach
    </div>
</x-admin.card>
