@props(['alerts'])

<x-admin.card title="Attention queue" description="Work that benefits from an operator review.">
    @if (count($alerts) === 0)
        <x-dashboard.empty-state title="No urgent attention" description="New alerts will appear here when system, security, content, or launch signals need review." />
    @else
        <div class="space-y-3">
            @foreach ($alerts as $alert)
                <a href="{{ route($alert['route']) }}" class="block rounded-md border border-stone-200 bg-white p-3 transition hover:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-extrabold text-stone-950">{{ $alert['title'] }}</p>
                        <span class="rounded-full bg-stone-100 px-2 py-1 text-xs font-extrabold text-stone-700">{{ str($alert['severity'])->headline() }}</span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-stone-600">{{ $alert['message'] }}</p>
                </a>
            @endforeach
        </div>
    @endif
</x-admin.card>
