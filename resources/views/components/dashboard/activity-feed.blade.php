@props(['items'])

<x-admin.card title="Recent activity" description="Latest audit events across admin workflows.">
    @if (count($items) === 0)
        <x-dashboard.empty-state title="No recent activity" description="Audit events will appear here after administrators start changing system records." />
    @else
        <div class="divide-y divide-stone-200">
            @foreach ($items as $item)
                <x-dashboard.activity-item :item="$item" />
            @endforeach
        </div>
    @endif
</x-admin.card>
