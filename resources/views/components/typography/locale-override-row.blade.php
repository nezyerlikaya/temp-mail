@props(['locale', 'usageScopes' => [], 'families', 'assignments', 'warnings' => [], 'canManage' => false])

<div class="rounded-lg border border-stone-200 bg-white p-4">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-extrabold text-stone-950">{{ $locale->language_name }} typography</h3>
            <p class="mt-1 text-sm font-semibold text-stone-600">{{ $locale->native_name }} · {{ strtoupper($locale->locale) }} · {{ strtoupper($locale->direction) }}</p>
        </div>
        @if ($locale->direction === 'rtl')
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-900">RTL readiness</span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.typography-center.assignments.update') }}" x-data="{ busy: false }" x-on:submit="busy = true" x-bind:class="{ 'pointer-events-none opacity-70': busy }" x-bind:aria-busy="busy">
        @csrf
        @method('PUT')
        <input type="hidden" name="scope" value="locale">
        <input type="hidden" name="scope_key" value="{{ $locale->locale }}">

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($usageScopes as $usage => $label)
                <x-typography.font-stack-field
                    :usage="$usage"
                    :label="$label"
                    :families="$families"
                    :assignment="$assignments->get('locale|'.$locale->locale.'|'.$usage)"
                    :warnings="$warnings"
                    :disabled="! $canManage"
                />
            @endforeach
        </div>

        <x-typography.save-bar :can-save="$canManage" />
    </form>
</div>
