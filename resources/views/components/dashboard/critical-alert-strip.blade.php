@props(['alerts' => []])

<section
    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-950 {{ count($alerts) === 0 ? 'hidden' : '' }}"
    x-show="criticalAlerts.length > 0"
    x-bind:class="{ 'hidden': criticalAlerts.length === 0 }"
    aria-label="Critical alerts"
    role="status"
    aria-live="polite"
>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-3">
            <i data-lucide="siren" class="mt-0.5 size-5 shrink-0" aria-hidden="true"></i>
            <div>
                <p class="text-sm font-extrabold">
                    <span x-text="criticalAlerts.length">{{ count($alerts) }}</span>
                    critical alert<span x-show="criticalAlerts.length !== 1">s</span>
                </p>
                <p class="mt-1 text-sm font-semibold" x-text="criticalAlerts[0]?.message ?? '{{ $alerts[0]['message'] ?? 'Critical alerts will appear here.' }}'">
                    {{ $alerts[0]['message'] ?? 'Critical alerts will appear here.' }}
                </p>
            </div>
        </div>
        <template x-if="criticalAlerts[0]?.url">
            <a x-bind:href="criticalAlerts[0].url" class="inline-flex min-h-10 items-center justify-center rounded-md bg-red-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-red-800/25">Review alert</a>
        </template>
    </div>
</section>
