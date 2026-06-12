@props(['warnings' => [], 'digestReadiness' => null])

@if ($warnings !== [] || $digestReadiness)
    <div class="space-y-3">
        @if ($digestReadiness)
            <x-admin.alert variant="info" title="Digest readiness">
                {{ $digestReadiness['message'] }}
            </x-admin.alert>
        @endif

        @foreach ($warnings as $warning)
            <x-admin.alert variant="warning" title="{{ str($warning['event_key'])->replace('_', ' ')->headline() }}">
                {{ $warning['message'] }}
            </x-admin.alert>
        @endforeach
    </div>
@endif
