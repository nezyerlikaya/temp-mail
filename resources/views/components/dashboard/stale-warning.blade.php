<section
    x-cloak
    x-show="stale && ! connectionUnavailable"
    class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-950"
    role="status"
    aria-live="polite"
>
    <div class="flex items-start gap-3">
        <i data-lucide="triangle-alert" class="mt-0.5 size-5 shrink-0" aria-hidden="true"></i>
        <div>
            <p class="text-sm font-extrabold">Data may be stale</p>
            <p class="mt-1 text-sm font-semibold">The last dashboard payload is older than two minutes. Existing values remain visible.</p>
        </div>
    </div>
</section>
