<section
    x-cloak
    x-show="connectionUnavailable"
    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-950"
    role="status"
    aria-live="polite"
>
    <div class="flex items-start gap-3">
        <i data-lucide="wifi-off" class="mt-0.5 size-5 shrink-0" aria-hidden="true"></i>
        <div>
            <p class="text-sm font-extrabold">Connection unavailable</p>
            <p class="mt-1 text-sm font-semibold">The latest refresh failed. The previous dashboard values are still shown.</p>
        </div>
    </div>
</section>
