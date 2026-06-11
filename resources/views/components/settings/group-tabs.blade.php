@props(['activeGroup'])

@php
    $groups = ['general' => 'General', 'brand' => 'Brand', 'localization' => 'Localization', 'maintenance' => 'Maintenance', 'legal' => 'Legal', 'system' => 'System'];
@endphp

<nav class="settings-tabs-scroll overflow-x-auto border-b border-stone-200" aria-label="Settings groups">
    <div class="flex min-w-max gap-1">
        @foreach ($groups as $key => $label)
            <a href="{{ route('admin.settings.index', ['group' => $key]) }}" class="inline-flex min-h-11 items-center border-b-2 px-4 text-sm font-bold transition focus:outline-none focus:ring-4 focus:ring-inset focus:ring-teal-700/15 {{ $activeGroup === $key ? 'border-teal-700 text-teal-800' : 'border-transparent text-stone-500 hover:border-stone-300 hover:text-stone-950' }}" @if ($activeGroup === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </div>
</nav>
