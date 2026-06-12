@props(['readiness'])

@php
    $ready = ($readiness['renderable'] ?? false) === true;
@endphp

<x-admin.card title="Render readiness" description="Public themes render only active sections that have usable content.">
    <div class="space-y-4">
        <x-admin.alert :variant="$ready ? 'success' : 'warning'" :title="$ready ? 'Renderable' : 'Not rendered'">
            {{ $readiness['message'] ?? 'Rendering readiness unavailable.' }}
        </x-admin.alert>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-lg border border-stone-200 p-3">
                <dt class="text-xs font-bold uppercase text-stone-500">State</dt>
                <dd class="mt-1 font-extrabold text-stone-950">{{ str($readiness['state'] ?? 'pending')->headline() }}</dd>
            </div>
            <div class="rounded-lg border border-stone-200 p-3">
                <dt class="text-xs font-bold uppercase text-stone-500">Fallback</dt>
                <dd class="mt-1 font-extrabold text-stone-950">No empty placeholders</dd>
            </div>
        </dl>
    </div>
</x-admin.card>
