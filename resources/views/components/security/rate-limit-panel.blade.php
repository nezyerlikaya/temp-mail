@props(['policies', 'strategies', 'readiness', 'status' => 'passive', 'canUpdate' => false])

<x-admin.card title="Rate limit policies" description="Configure safe request limits for authentication, mailbox, comment, contact, and API readiness.">
    <form method="POST" action="{{ route('admin.security-defense-center.rate-limits.update') }}" class="space-y-4" x-data="{ submitting: false }" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 bg-white p-4">
            <div>
                <p class="text-sm font-extrabold text-stone-950">Configured Laravel limiters</p>
                <p class="mt-1 text-sm leading-6 text-stone-600">Invalid zero values are rejected before they can disable protection.</p>
            </div>
            <x-security.status-badge :status="$status" />
        </div>

        <div class="space-y-3">
            @foreach ($policies as $policy)
                <x-security.rate-limit-row :policy="$policy" :strategies="$strategies" :can-update="$canUpdate" />
            @endforeach
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($readiness as $item)
                <div class="rounded-lg border border-stone-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-stone-900">{{ $item['label'] }}</p>
                        <x-security.status-badge :status="$item['status']" />
                    </div>
                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ $item['message'] }}</p>
                </div>
            @endforeach
        </div>

        <x-security.save-bar label="Save rate limits" :can-submit="$canUpdate">
            Applies to named Laravel rate limiters used by auth and future protected workflows.
        </x-security.save-bar>
    </form>
</x-admin.card>
