@props(['usages', 'summary'])

<section class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <header class="flex items-start justify-between gap-4 border-b border-stone-200 px-5 py-4">
        <div>
            <h2 class="text-base font-extrabold text-stone-950">Used by</h2>
            <p class="mt-1 text-sm text-stone-600">Generic module-safe usage tracking for future content workflows.</p>
        </div>
        <x-media.orphaned-badge :count="$summary['total']" />
    </header>

    <div class="p-5">
        <div class="mb-5 grid gap-3 sm:grid-cols-2">
            @foreach ($summary['readiness'] as $item)
                <div class="rounded-lg border border-stone-200 bg-stone-50 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-extrabold text-stone-950">{{ $item['label'] }}</p>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $item['count'] > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-stone-200 bg-white text-stone-600' }}">{{ $item['count'] }}</span>
                    </div>
                    <p class="mt-1 text-xs text-stone-500">{{ $item['count'] > 0 ? 'Usage tracked.' : 'Ready for future module hooks.' }}</p>
                </div>
            @endforeach
        </div>

        @if ($usages->count() > 0)
            <div class="overflow-hidden rounded-lg border border-stone-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Usage</th>
                                <th scope="col" class="px-4 py-3">Module</th>
                                <th scope="col" class="px-4 py-3">Target</th>
                                <th scope="col" class="px-4 py-3">Attached</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usages as $usage)
                                <x-media.usage-row :usage="$usage" />
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950">
                <p class="font-extrabold">This asset is currently orphaned.</p>
                <p class="mt-1">It is safe readiness information only; no delete or trash action is introduced in this step.</p>
            </div>
        @endif
    </div>
</section>
