@props(['timeline'])
<x-admin.card title="Activity timeline" description="Administrative and lifecycle events without private message content.">
    @if(count($timeline))<ol class="space-y-4">@foreach($timeline as $entry)<li class="grid grid-cols-[12px_minmax(0,1fr)] gap-3"><span class="mt-1.5 size-2.5 rounded-full bg-teal-600"></span><div><p class="text-sm font-extrabold text-stone-900">{{ $entry['label'] }}</p><p class="mt-1 text-sm text-stone-600">{{ $entry['detail'] }}</p><time class="mt-1 block text-xs font-bold text-stone-500">{{ \Illuminate\Support\Carbon::parse($entry['occurred_at'])->diffForHumans() }}</time></div></li>@endforeach</ol>@else<p class="text-sm text-stone-600">No lifecycle events recorded.</p>@endif
</x-admin.card>
