@props(['alerts'])

@if (count($alerts) > 0)
    <section class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-950" aria-label="Critical attention" role="status">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3">
                <i data-lucide="triangle-alert" class="mt-0.5 size-5 shrink-0" aria-hidden="true"></i>
                <div>
                    <p class="text-sm font-extrabold">{{ count($alerts) }} operational signals need attention</p>
                    <p class="mt-1 text-sm font-semibold">{{ $alerts[0]['message'] }}</p>
                </div>
            </div>
            <a href="{{ route($alerts[0]['route']) }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-amber-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-amber-800/25">Review first signal</a>
        </div>
    </section>
@endif
