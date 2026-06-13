@props(['family', 'providers' => [], 'canManage' => false, 'fontDisplayOptions' => []])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-base font-extrabold text-stone-950">{{ $family->name }}</h3>
                <x-typography.status-badge :active="$family->is_active" />
            </div>
            <p class="mt-1 text-xs font-bold text-stone-500">{{ $family->css_family }}</p>
        </div>
        <x-typography.provider-badge :provider="$family->provider" :providers="$providers" />
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($family->supported_scripts as $script)
            <x-typography.script-coverage-badge :script="$script" />
        @endforeach
    </div>

    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-md bg-stone-50 p-3">
            <dt class="text-xs font-bold text-stone-500">Category</dt>
            <dd class="mt-1 font-extrabold text-stone-900">{{ ucfirst($family->category) }}</dd>
        </div>
        <div class="rounded-md bg-stone-50 p-3">
            <dt class="text-xs font-bold text-stone-500">RTL</dt>
            <dd class="mt-1 font-extrabold text-stone-900">{{ $family->rtl_support ? 'Ready' : 'Limited' }}</dd>
        </div>
        <div class="rounded-md bg-stone-50 p-3">
            <dt class="text-xs font-bold text-stone-500">Weights</dt>
            <dd class="mt-1 font-extrabold text-stone-900">{{ implode(', ', $family->available_weights) }}</dd>
        </div>
        <div class="rounded-md bg-stone-50 p-3">
            <dt class="text-xs font-bold text-stone-500">Display</dt>
            <dd class="mt-1 font-extrabold text-stone-900">{{ $family->font_display }}</dd>
        </div>
    </dl>

    <x-typography.font-family-editor :family="$family" :can-manage="$canManage" :font-display-options="$fontDisplayOptions" />
</article>
