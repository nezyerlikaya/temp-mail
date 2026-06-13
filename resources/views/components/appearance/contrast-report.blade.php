@props(['report'])

<section class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-stone-950">Contrast Checks</h2>
            <p class="mt-1 text-sm leading-6 text-stone-600">Critical failures block publishing. Warnings can be published, but should be improved.</p>
        </div>
        <x-appearance.contrast-badge :status="$report['summary']['publishable'] ? 'pass' : 'fail'" />
    </div>
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        @foreach ($report['checks'] as $check)
            <article class="rounded-md border border-stone-200 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-extrabold text-stone-950">{{ $check['name'] }}</p>
                        <p class="mt-1 text-xs font-bold text-stone-500">Ratio {{ $check['ratio'] }}:1 {{ $check['critical'] ? '· Critical' : '· Advisory' }}</p>
                    </div>
                    <x-appearance.contrast-badge :status="$check['status']" />
                </div>
                <p class="mt-3 text-sm leading-6 text-stone-600">{{ $check['message'] }}</p>
            </article>
        @endforeach
    </div>
</section>
