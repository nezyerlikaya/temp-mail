@props(['headers'])
<x-admin.card title="Raw headers" description="Normalized header values displayed as inert text.">
    @if(count($headers))
        <dl class="divide-y divide-stone-200">@foreach($headers as $name => $value)<div class="grid gap-1 py-3 sm:grid-cols-[160px_minmax(0,1fr)]"><dt class="break-all text-xs font-extrabold text-stone-600">{{ $name }}</dt><dd class="break-all font-mono text-xs leading-5 text-stone-800">{{ $value }}</dd></div>@endforeach</dl>
    @else<p class="text-sm text-stone-600">No raw headers were retained for this message.</p>@endif
</x-admin.card>
