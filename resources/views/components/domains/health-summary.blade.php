@props(['summary', 'readiness'])

<div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
    @foreach ([['Total domains', $summary['total']], ['Active', $summary['active']], ['Public', $summary['public']], ['DNS ready', $readiness['dns_ready']], ['Needs DNS', $readiness['needs_dns']], ['Catch-all ready', $readiness['catch_all_ready']]] as [$label, $value])
        <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-stone-500">{{ $label }}</p>
            <p class="mt-3 text-2xl font-extrabold text-stone-950">{{ number_format($value) }}</p>
        </div>
    @endforeach
</div>
