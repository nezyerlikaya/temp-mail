@props(['required'])

<div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
    <p class="text-sm font-extrabold text-amber-900">Required variable readiness</p>
    <p class="mt-1 text-sm text-amber-800">Critical active templates are checked server-side for their required variables.</p>
    <div class="mt-3 max-h-40 space-y-1 overflow-y-auto pr-1">
        @foreach ($required as $key => $variables)
            <p class="text-xs font-bold text-amber-900">{{ str($key)->replace('_', ' ')->headline() }}: {{ collect($variables)->map(fn ($variable) => '{{ '.$variable.' }}')->join(', ') }}</p>
        @endforeach
    </div>
</div>
