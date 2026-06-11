@props(['compatibility'])

<div class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="border-b border-stone-200 px-5 py-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-stone-950">Compatibility checklist</h2>
                <p class="mt-1 text-sm text-stone-600">Server readiness before any future install action.</p>
            </div>
            <x-updates.status-badge :status="$compatibility['compatible'] ? 'passed' : 'failed'" />
        </div>
    </div>

    <ul class="divide-y divide-stone-200">
        @foreach ($compatibility['results'] as $check)
            <li class="flex gap-4 px-5 py-4">
                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $check['status'] === 'passed' ? 'bg-emerald-500' : 'bg-red-500' }}" aria-hidden="true"></span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="font-extrabold text-stone-950">{{ $check['label'] }}</p>
                        <x-updates.status-badge :status="$check['status']" />
                    </div>
                    <p class="mt-1 text-sm leading-6 text-stone-600">{{ $check['message'] }}</p>
                    @if ($check['detail'])
                        <p class="mt-1 break-words text-xs font-bold text-stone-500">{{ $check['detail'] }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</div>
