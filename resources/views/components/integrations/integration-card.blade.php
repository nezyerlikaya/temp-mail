@props(['integration', 'environment' => 'sandbox', 'active' => false])

<a href="{{ route('admin.integrations.index', ['integration' => $integration['key'], 'category' => request('category', 'all'), 'environment' => $environment]) }}" class="block rounded-lg border bg-white p-4 shadow-sm transition focus:outline-none focus:ring-4 focus:ring-teal-700/20 {{ $active ? 'border-teal-700 ring-2 ring-teal-700/10' : 'border-stone-200 hover:border-stone-300' }}">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-extrabold text-stone-950">{{ $integration['name'] }}</h2>
            <p class="mt-1 text-sm font-semibold text-stone-600">{{ $integration['description'] }}</p>
        </div>
        <x-integrations.status-badge :active="$integration['is_active']" />
    </div>
    <div class="mt-4 flex flex-wrap items-center gap-2">
        <x-integrations.connection-badge :status="$integration['connection_status']" />
        <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-700 ring-1 ring-stone-200">{{ str($integration['category'])->replace('_', ' ')->headline() }}</span>
        @if (! $integration['configuration_complete'])
            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-extrabold text-amber-900 ring-1 ring-amber-100">Missing config</span>
        @endif
    </div>
    <p class="mt-3 text-xs font-bold text-stone-500">Owner: {{ $integration['owner'] }}</p>
</a>
