@props(['canRun' => false])

<x-admin.card title="Run Health Check" description="Run safe shared-hosting checks without exposing secrets or sending test emails.">
    @if ($canRun)
        <form method="POST" action="{{ route('admin.backups-health.health-check.run') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20 disabled:cursor-not-allowed disabled:opacity-70">
                <i data-lucide="activity" class="size-4" aria-hidden="true"></i>
                <span x-show="! submitting">Run health check</span>
                <span x-cloak x-show="submitting">Checking...</span>
            </button>
        </form>
    @else
        <div class="rounded-md border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-600">
            Running checks requires administrator authorization.
        </div>
    @endif
    <p class="mt-4 text-xs leading-5 text-stone-500">Critical health issues record notification readiness and audit metadata.</p>
</x-admin.card>
