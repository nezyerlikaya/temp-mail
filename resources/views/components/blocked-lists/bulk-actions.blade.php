@props(['entries', 'canBulkModify'])
@if ($canBulkModify && $entries->count() > 0)
    <section class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm" x-data="{ selected: [] }">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase text-teal-800">Bulk actions</p>
                <p class="mt-1 text-sm text-stone-600">Select visible rules for activate, deactivate, expire, or expiration update readiness.</p>
            </div>
            <form method="POST" action="{{ route('admin.blocked-lists.expire') }}">@csrf<button class="inline-flex min-h-10 items-center rounded-lg border border-amber-300 px-4 text-sm font-extrabold text-amber-800">Process expired rules</button></form>
        </div>
        <form method="POST" action="{{ route('admin.blocked-lists.bulk') }}" class="mt-4 space-y-3">
            @csrf
            <div class="grid gap-2 md:grid-cols-2">
                @foreach ($entries as $entry)
                    <label class="flex items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-sm text-stone-700">
                        <input type="checkbox" name="entry_ids[]" value="{{ $entry->id }}" x-model="selected" class="rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                        <span class="min-w-0 truncate">#{{ $entry->id }} {{ $entry->display_value }}</span>
                    </label>
                @endforeach
            </div>
            <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                <select name="bulk_action" class="min-h-10 rounded-lg border border-stone-300 px-3 text-sm">
                    <option value="deactivate">Deactivate</option>
                    <option value="activate">Activate</option>
                    <option value="expire">Mark expired</option>
                    <option value="update_expiration">Update expiration</option>
                </select>
                <input type="date" name="expires_at" class="min-h-10 rounded-lg border border-stone-300 px-3 text-sm">
                <button class="inline-flex min-h-10 items-center justify-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white disabled:opacity-60" x-bind:disabled="selected.length === 0">Apply</button>
            </div>
        </form>
    </section>
@endif
