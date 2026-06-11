@props(['lockStatus'])

@if ($lockStatus['locked'])
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-red-200 bg-red-50 p-5 text-red-950']) }} role="alert">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold">Update lock active</h2>
                <p class="mt-2 text-sm leading-6">{{ $lockStatus['message'] }}</p>
                @if ($lockStatus['created_at'])
                    <p class="mt-1 text-xs font-bold text-red-800">Created at {{ $lockStatus['created_at'] }}</p>
                @endif
            </div>
            <x-updates.status-badge status="locked" />
        </div>
    </div>
@endif
