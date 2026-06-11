@props(['retention', 'canManage' => false])

<x-admin.card title="Retention" description="Default retention is 180 days. Critical logs are preserved unless policy changes explicitly.">
    <div class="space-y-4">
        <div class="grid gap-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="font-semibold text-stone-600">Current policy</span>
                <span class="font-extrabold text-stone-950">{{ $retention['retention_days'] }} days</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="font-semibold text-stone-600">Recommended</span>
                <span class="font-extrabold text-stone-950">{{ $retention['recommended_days'] }} days</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="font-semibold text-stone-600">Expired non-critical</span>
                <span class="font-extrabold text-stone-950">{{ number_format($retention['expired_non_critical']) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="font-semibold text-stone-600">Expired critical</span>
                <span class="font-extrabold text-stone-950">{{ number_format($retention['expired_critical']) }}</span>
            </div>
        </div>

        @if ($canManage)
            <form method="POST" action="{{ route('admin.activity-audit-logs.retention.update') }}" class="space-y-4" novalidate>
                @csrf
                @method('PUT')
                <x-form.input name="retention_days" label="Retention days" type="number" :value="$retention['retention_days']" inputmode="numeric" min="30" max="3650" required />
                <label class="flex items-start justify-between gap-4 rounded-md border border-stone-200 p-4">
                    <span>
                        <span class="block text-sm font-extrabold text-stone-950">Preserve critical logs</span>
                        <span class="mt-1 block text-sm text-stone-600">Prevents cleanup from removing critical security and compliance records.</span>
                    </span>
                    <span class="relative mt-0.5 inline-flex shrink-0">
                        <input name="preserve_critical" type="checkbox" value="1" class="peer sr-only" @checked(old('preserve_critical', $retention['preserve_critical']))>
                        <span class="h-6 w-11 rounded-full bg-stone-300 peer-checked:bg-teal-700 peer-focus-visible:ring-4 peer-focus-visible:ring-teal-700/25"></span>
                        <span class="pointer-events-none absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                    </span>
                </label>
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 text-sm font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/15">
                    <i data-lucide="shield-check" class="size-4" aria-hidden="true"></i>
                    Save retention policy
                </button>
            </form>
        @else
            <div class="rounded-md border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-600">
                Retention settings are restricted to owners and administrators.
            </div>
        @endif
    </div>
</x-admin.card>
