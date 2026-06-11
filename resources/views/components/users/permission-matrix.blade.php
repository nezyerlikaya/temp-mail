@props(['matrix', 'roles'])

<div class="overflow-x-auto">
    <table class="w-full min-w-[920px] border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-stone-200 bg-stone-50 text-xs font-bold uppercase text-stone-500">
                <th scope="col" class="px-5 py-3 sm:px-6">Module ability</th>
                @foreach ($roles as $role)
                    <th scope="col" class="px-3 py-3 text-center">{{ $role->label() }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-200">
            @foreach ($matrix as $row)
                <tr>
                    <th scope="row" class="px-5 py-3 font-semibold text-stone-900 sm:px-6">
                        <span class="block">{{ $row['label'] }}</span>
                        <span class="mt-0.5 block text-xs font-medium text-stone-500">{{ $row['group'] }}</span>
                    </th>
                    @foreach ($roles as $role)
                        <td class="px-3 py-3 text-center">
                            @if ($row['grants'][$role->value])
                                <span class="inline-flex size-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-800" aria-label="Allowed">
                                    <i data-lucide="check" class="size-4" aria-hidden="true"></i>
                                </span>
                            @else
                                <span class="inline-flex size-7 items-center justify-center rounded-full bg-stone-100 text-stone-400" aria-label="Not allowed">
                                    <i data-lucide="minus" class="size-4" aria-hidden="true"></i>
                                </span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
