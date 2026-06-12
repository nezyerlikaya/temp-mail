@props(['impact'])
<x-admin.card title="Limit impact preview" description="Prepared effect summary for support and future public plan copy.">
    <dl class="space-y-3">
        @foreach($impact as $item)
            <div>
                <dt class="text-xs font-bold text-stone-500">{{ $item['label'] }}</dt>
                <dd class="mt-1 text-sm font-extrabold text-stone-900">{{ $item['value'] }}</dd>
            </div>
        @endforeach
    </dl>
</x-admin.card>
