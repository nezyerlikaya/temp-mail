@props(['rows' => []])

<details {{ $attributes->merge(['class' => 'group rounded-md border border-stone-200 bg-white']) }}>
    <summary class="cursor-pointer list-none px-3 py-2 text-sm font-bold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-700/15">
        Before / after diff
        <span class="text-xs font-semibold text-stone-500 group-open:hidden">{{ count($rows) > 0 ? count($rows).' fields' : 'empty' }}</span>
        <span class="hidden text-xs font-semibold text-stone-500 group-open:inline">hide</span>
    </summary>

    @if (count($rows) > 0)
        <div class="overflow-x-auto border-t border-stone-200">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-left">Field</th>
                        <th scope="col" class="px-3 py-2 text-left">Before</th>
                        <th scope="col" class="px-3 py-2 text-left">After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach ($rows as $row)
                        <tr>
                            <td class="px-3 py-2 font-bold text-stone-800">{{ str($row['field'])->headline() }}</td>
                            <td class="max-w-56 px-3 py-2 text-stone-600"><span class="block truncate">{{ $row['before'] }}</span></td>
                            <td class="max-w-56 px-3 py-2 text-stone-900"><span class="block truncate font-semibold">{{ $row['after'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="border-t border-stone-200 px-3 py-3 text-sm text-stone-500">No structured before/after diff was recorded for this event.</p>
    @endif
</details>
