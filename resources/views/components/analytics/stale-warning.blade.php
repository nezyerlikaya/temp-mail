@props(['freshness'])

<x-admin.alert :variant="$freshness['stale'] ? 'warning' : 'success'" :title="$freshness['stale'] ? 'Aggregation needs attention' : 'Aggregates current'">
    {{ $freshness['message'] }}
    @if($freshness['last_aggregated_at'])
        <span class="font-bold">Last update: {{ $freshness['last_aggregated_at'] }}.</span>
    @else
        <span class="font-bold">No aggregation has run yet.</span>
    @endif
</x-admin.alert>
