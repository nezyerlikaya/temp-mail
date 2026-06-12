@props(['readiness'])

<x-admin.card title="SEO readiness" description="FAQ schema is conditional and quality-aware.">
    @if (! ($readiness['applies'] ?? false))
        <p class="text-sm text-stone-600">{{ $readiness['message'] ?? 'No schema rules apply to this section.' }}</p>
    @else
        <div class="space-y-4">
            <x-admin.alert :variant="($readiness['schema_allowed'] ?? false) ? 'success' : 'warning'" :title="($readiness['schema_allowed'] ?? false) ? 'FAQ schema allowed' : 'FAQ schema disabled'">
                {{ $readiness['message'] ?? 'FAQ schema readiness pending.' }}
            </x-admin.alert>
            <dl class="grid gap-3 text-sm sm:grid-cols-3">
                <div class="rounded-lg border border-stone-200 p-3">
                    <dt class="text-xs font-bold uppercase text-stone-500">Active FAQ</dt>
                    <dd class="mt-1 font-extrabold text-stone-950">{{ $readiness['active_count'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg border border-stone-200 p-3">
                    <dt class="text-xs font-bold uppercase text-stone-500">Ideal</dt>
                    <dd class="mt-1 font-extrabold text-stone-950">6-8</dd>
                </div>
                <div class="rounded-lg border border-stone-200 p-3">
                    <dt class="text-xs font-bold uppercase text-stone-500">Max recommended</dt>
                    <dd class="mt-1 font-extrabold text-stone-950">{{ $readiness['max_recommended'] ?? 12 }}</dd>
                </div>
            </dl>
            @if (($readiness['warnings'] ?? []) !== [])
                <ul class="space-y-2 text-sm font-bold text-amber-900">
                    @foreach ($readiness['warnings'] as $warning)
                        <li class="rounded-lg border border-amber-200 bg-amber-50 p-3">{{ $warning }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</x-admin.card>
