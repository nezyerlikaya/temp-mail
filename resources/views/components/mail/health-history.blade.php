@props(['history' => []])

<x-admin.card title="Health history" description="The ten latest readiness checks, stored without credentials or provider error details.">
    @if (count($history))
        <ol class="divide-y divide-stone-200">
            @foreach ($history as $entry)
                <li class="grid gap-2 py-3 first:pt-0 last:pb-0 sm:grid-cols-[120px_minmax(0,1fr)_auto] sm:items-start">
                    <x-mail.connection-status-badge :status="$entry['status']" />
                    <p class="text-sm leading-6 text-stone-700">{{ $entry['message'] }}</p>
                    <time class="text-xs font-bold text-stone-500">{{ \Illuminate\Support\Carbon::parse($entry['tested_at'])->diffForHumans() }}</time>
                </li>
            @endforeach
        </ol>
    @else
        <p class="text-sm text-stone-600">No connection tests have been recorded yet.</p>
    @endif
</x-admin.card>
