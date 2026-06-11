@props(['disabled' => false])

<form id="locale-bulk-form" method="POST" action="{{ route('admin.locale-launch-center.bulk') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    @csrf
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <label for="bulk-action" class="text-xs font-extrabold uppercase text-stone-500">Bulk action</label>
            <select id="bulk-action" name="action" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled($disabled)>
                <option value="activate">Activate selected</option>
                <option value="deactivate">Deactivate selected</option>
            </select>
        </div>
        <p class="text-sm text-stone-600">Select visible locales from the cards below.</p>
        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60" @disabled($disabled)>
            Apply
        </button>
    </div>
</form>
