@props(['comment', 'canDelete'])

@if ($canDelete && $comment->status === 'trashed')
    <div x-data="{ confirmDelete: false }" class="rounded-lg border border-red-200 bg-white p-3">
        <p class="text-sm font-extrabold text-red-950">Permanent deletion</p>
        <p class="mt-1 text-xs font-semibold text-red-800">Owner/admin only. This keeps an audit record and removes the comment.</p>
        <form method="POST" action="{{ route('admin.comment-moderation.destroy', $comment) }}" class="mt-3 space-y-2">
            @csrf
            @method('DELETE')
            <label class="flex items-start gap-2 text-xs font-bold text-stone-700">
                <input type="checkbox" name="confirm_delete" value="1" class="mt-0.5 rounded border-stone-300 text-red-700 focus:ring-red-600/25" x-model="confirmDelete">
                <span>I understand this permanently deletes the comment.</span>
            </label>
            <button class="inline-flex min-h-9 items-center rounded-lg bg-red-700 px-3 text-xs font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-red-600/25" x-bind:disabled="! confirmDelete" x-bind:class="! confirmDelete ? 'opacity-50' : ''">Delete permanently</button>
        </form>
    </div>
@endif
