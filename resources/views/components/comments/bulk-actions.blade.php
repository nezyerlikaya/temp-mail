@props(['canBulk'])

@if ($canBulk)
    <form method="POST" action="{{ route('admin.comment-moderation.bulk') }}"
        class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm"
        x-on:submit="selected.forEach((id) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'comment_ids[]'; input.value = id; $el.appendChild(input); })">
        @csrf
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-extrabold text-stone-950">Bulk moderation</p>
                <p class="mt-1 text-sm text-stone-600"><span x-text="selected.length">0</span> selected comments</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <select name="action" class="min-h-10 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" required>
                    <option value="approve">Approve</option>
                    <option value="spam">Mark spam</option>
                    <option value="trash">Move to trash</option>
                    <option value="restore">Restore</option>
                </select>
                <button class="inline-flex min-h-10 items-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-stone-500/25" x-bind:disabled="selected.length === 0" x-bind:class="selected.length === 0 ? 'opacity-50' : ''">Apply</button>
            </div>
        </div>
    </form>
@endif
