@props(['items'])

<x-admin.card title="Infrastructure health" description="Latest readiness state from core operational modules.">
    <div class="divide-y divide-stone-200">
        @foreach ($items as $item)
            <x-dashboard.health-item :item="$item" />
        @endforeach
    </div>
</x-admin.card>
