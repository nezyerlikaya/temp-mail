@props(['preview'])
<x-admin.card title="Retention preview" description="Human-readable effects of the currently saved values.">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach([['Guest expiry',$preview['guest_expires']],['Registered expiry',$preview['registered_expires']],['Premium readiness',$preview['premium_expires']],['Example guest expiry',$preview['example_guest_expiry']]] as [$label,$value])<div><p class="text-xs font-bold text-stone-500">{{ $label }}</p><p class="mt-1 text-sm font-extrabold text-stone-950">{{ $value }}</p></div>@endforeach</div>
    <p class="mt-5 border-t border-stone-200 pt-4 text-sm font-bold text-stone-700">{{ $preview['purge_timing'] }}</p>
</x-admin.card>
