@props(['manifest'])

@php
    $notes = $manifest['release_notes'] ?? null;
    $changelog = $manifest['changelog'] ?? [];
    $severityStyles = [
        'security' => 'border-red-200 bg-red-50 text-red-800',
        'breaking' => 'border-amber-200 bg-amber-50 text-amber-900',
        'feature' => 'border-teal-200 bg-teal-50 text-teal-800',
        'fix' => 'border-sky-200 bg-sky-50 text-sky-800',
    ];
@endphp

<div class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="border-b border-stone-200 px-5 py-4">
        <h2 class="text-base font-extrabold text-stone-950">Release notes</h2>
        <p class="mt-1 text-sm text-stone-600">Manifest-provided notes for review before install planning.</p>
    </div>

    <div class="space-y-5 p-5">
        @if ($notes)
            <p class="text-sm leading-6 text-stone-700">{{ $notes }}</p>
        @else
            <p class="text-sm leading-6 text-stone-600">No release notes have been checked yet.</p>
        @endif

        @if (is_array($changelog) && count($changelog) > 0)
            <ul class="space-y-3">
                @foreach ($changelog as $item)
                    @php
                        $severity = is_array($item) ? (string) ($item['severity'] ?? 'feature') : 'feature';
                        $message = is_array($item) ? (string) ($item['message'] ?? '') : (string) $item;
                    @endphp
                    <li class="rounded-lg border border-stone-200 p-4">
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold {{ $severityStyles[$severity] ?? $severityStyles['feature'] }}">{{ str($severity)->headline() }}</span>
                        <p class="mt-2 text-sm leading-6 text-stone-700">{{ $message }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
