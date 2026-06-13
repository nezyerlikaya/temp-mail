@props(['canExport', 'filters'])

<x-admin.card title="CSV export" description="Exports contain aggregate rows only and are audited. No raw event payloads or private message content are included.">
    <form method="GET" action="{{ route('admin.product-analytics.export') }}" class="space-y-3">
        @foreach($filters as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <button type="submit" @disabled(! $canExport) class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
            Export aggregate CSV
        </button>
        @unless($canExport)
            <p class="text-sm font-bold text-amber-800">Only owners and admins can export analytics.</p>
        @endunless
    </form>
</x-admin.card>
