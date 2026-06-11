@props(['filters' => [], 'canExport' => false])

<x-admin.card title="Export" description="Download a sanitized CSV for compliance review. Export actions are audit logged.">
    @if ($canExport)
        <form method="GET" action="{{ route('admin.activity-audit-logs.export') }}" class="space-y-4">
            @foreach ($filters as $key => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20">
                <i data-lucide="download" class="size-4" aria-hidden="true"></i>
                Export filtered CSV
            </button>
            <p class="text-xs leading-5 text-stone-500">Secrets remain masked in the downloaded file.</p>
        </form>
    @else
        <div class="rounded-md border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-600">
            Export is restricted to owners and administrators.
        </div>
    @endif
</x-admin.card>
