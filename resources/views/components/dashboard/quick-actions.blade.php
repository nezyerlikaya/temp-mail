@props(['actions'])

<x-admin.card title="Quick actions" description="Permission-aware shortcuts for common operational work.">
    @if (count($actions) === 0)
        <x-dashboard.empty-state title="No actions available" description="Actions appear when your role can run the related workflow." />
    @else
        <div class="space-y-2">
            @foreach ($actions as $action)
                @if ($action['method'] === 'POST')
                    <form method="POST" action="{{ route($action['route']) }}">
                        @csrf
                        @foreach (($action['payload'] ?? []) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" class="flex min-h-12 w-full items-center gap-3 rounded-md border border-stone-200 bg-white px-3 text-left transition hover:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                            <i data-lucide="{{ $action['icon'] }}" class="size-4 text-stone-700" aria-hidden="true"></i>
                            <span>
                                <span class="block text-sm font-extrabold text-stone-950">{{ $action['label'] }}</span>
                                <span class="block text-xs font-semibold text-stone-500">{{ $action['description'] }}</span>
                            </span>
                        </button>
                    </form>
                @else
                    <a href="{{ route($action['route']) }}" class="flex min-h-12 items-center gap-3 rounded-md border border-stone-200 bg-white px-3 transition hover:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                        <i data-lucide="{{ $action['icon'] }}" class="size-4 text-stone-700" aria-hidden="true"></i>
                        <span>
                            <span class="block text-sm font-extrabold text-stone-950">{{ $action['label'] }}</span>
                            <span class="block text-xs font-semibold text-stone-500">{{ $action['description'] }}</span>
                        </span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</x-admin.card>
