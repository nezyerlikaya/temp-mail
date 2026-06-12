@props(['status'])

@if (is_array($status))
    @php($classes = $status['status'] === 'sent' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800')
    <div class="rounded-lg border p-4 {{ $classes }}" role="status" aria-live="polite">
        <p class="text-sm font-extrabold">{{ str($status['status'])->headline() }}</p>
        <p class="mt-1 text-sm font-bold">{{ $status['message'] }}</p>
    </div>
@endif
