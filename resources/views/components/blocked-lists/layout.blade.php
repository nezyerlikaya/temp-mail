@props(['summary', 'notificationReadiness' => null])
<div class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-4">
        @foreach ([['Active', $summary['active'], 'text-emerald-800'], ['Inactive', $summary['inactive'], 'text-stone-700'], ['Expired', $summary['expired'], 'text-amber-800'], ['Manual', $summary['manual'], 'text-teal-800']] as [$label, $value, $class])
            <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-stone-500">{{ $label }}</p><p class="mt-2 text-2xl font-extrabold {{ $class }}">{{ $value }}</p></div>
        @endforeach
    </div>
    @if ($notificationReadiness)
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-950">
            <span class="font-extrabold">{{ $notificationReadiness['label'] }}:</span>
            {{ $notificationReadiness['message'] }}
        </div>
    @endif
    {{ $slot }}
</div>
