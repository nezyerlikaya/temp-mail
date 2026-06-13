@props(['selectedTheme', 'activeTheme', 'defaultTokens', 'cssVariables'])

<x-admin.card title="Theme Defaults" description="Immutable preset values for the selected public theme.">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-extrabold text-stone-950">{{ str($selectedTheme)->headline() }}</p>
        @if ($selectedTheme === $activeTheme)
            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold text-teal-800 ring-1 ring-inset ring-teal-200">Active theme</span>
        @else
            <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-700 ring-1 ring-inset ring-stone-200">Inactive theme</span>
        @endif
    </div>

    <div class="mt-4 grid grid-cols-4 gap-2" aria-label="Default color swatches">
        @foreach (array_slice($defaultTokens, 0, 7) as $name => $value)
            <div class="h-10 rounded-md border border-stone-200" style="background-color: {{ $value }}" title="{{ str($name)->headline() }}"></div>
        @endforeach
    </div>

    <dl class="mt-4 space-y-2 text-xs">
        @foreach ($cssVariables as $name => $value)
            <div class="flex justify-between gap-3 rounded-md bg-stone-50 px-3 py-2">
                <dt class="font-bold text-stone-500">{{ $name }}</dt>
                <dd class="truncate font-extrabold text-stone-900">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
</x-admin.card>
