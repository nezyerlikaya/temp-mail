@props(['hreflang'])

<x-admin.card title="Hreflang readiness" description="Language-aware coverage by target family and canonical readiness.">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="text-xs font-extrabold uppercase text-stone-500">
                <tr>
                    <th class="py-2 pr-3">Target</th>
                    @foreach ($hreflang['locales'] as $locale)
                        <th class="px-3 py-2">{{ $locale->locale }}</th>
                    @endforeach
                    <th class="py-2 pl-3">Ready</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hreflang['rows']->take(8) as $row)
                    <tr class="border-t border-stone-100">
                        <td class="py-3 pr-3 font-bold text-stone-800">{{ $row['label'] }}</td>
                        @foreach ($hreflang['locales'] as $locale)
                            <td class="px-3 py-3">
                                <span class="inline-flex size-3 rounded-full {{ $row['coverage'][$locale->locale] ? 'bg-emerald-500' : 'bg-stone-300' }}"></span>
                            </td>
                        @endforeach
                        <td class="py-3 pl-3 text-xs font-extrabold text-stone-500">{{ $row['ready_count'] }}/{{ $row['total_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
