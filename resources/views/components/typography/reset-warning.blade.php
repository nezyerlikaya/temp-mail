@props(['locale', 'canReset' => false])

<div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-extrabold text-amber-950">Reset locale override</h3>
            <p class="mt-1 text-sm font-semibold text-amber-900">Remove {{ strtoupper($locale->locale) }} font overrides and inherit theme/global assignments.</p>
        </div>
        <form method="POST" action="{{ route('admin.typography-center.locales.reset', ['locale' => $locale->locale]) }}" onsubmit="return confirm('Reset this locale typography override?')">
            @csrf
            <button type="submit" @disabled(! $canReset) class="inline-flex min-h-10 items-center gap-2 rounded-md border border-amber-300 bg-white px-3 py-2 text-sm font-extrabold text-amber-950 transition hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-700/20 disabled:cursor-not-allowed disabled:opacity-60">
                <i data-lucide="rotate-ccw" class="size-4" aria-hidden="true"></i>
                Reset
            </button>
        </form>
    </div>
</div>
