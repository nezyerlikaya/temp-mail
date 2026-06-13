@props(['selectedTheme', 'activeTheme'])

<x-admin.card title="Theme Isolation" description="Switching selected themes loads that theme's independent saved or default appearance values.">
    <div class="rounded-md border {{ $selectedTheme === $activeTheme ? 'border-teal-200 bg-teal-50 text-teal-950' : 'border-amber-200 bg-amber-50 text-amber-950' }} p-3">
        <p class="text-sm font-extrabold">{{ $selectedTheme === $activeTheme ? 'Editing active theme' : 'Editing inactive theme' }}</p>
        <p class="mt-1 text-sm leading-6">{{ str($selectedTheme)->headline() }} appearance stays separate from {{ str($activeTheme)->headline() }}.</p>
    </div>
</x-admin.card>
