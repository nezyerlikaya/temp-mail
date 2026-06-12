@props(['section', 'canDelete' => false])

<x-admin.card title="Permanent delete readiness" description="Permanent deletion is separated from trash and always audited.">
    <div class="space-y-3 text-sm text-stone-600">
        <p>Only owner/admin-level access can permanently delete a trashed section. This removes child items and records an audit event.</p>

        @if ($section->status === 'trashed')
            <form method="POST" action="{{ route('admin.sections-studio.destroy', $section) }}" x-data="{ confirmed: false, submitting: false }" x-on:submit="if (submitting || ! confirmed) { $event.preventDefault(); return; } submitting = true">
                @csrf
                @method('DELETE')
                <label class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-3 text-red-900">
                    <input type="checkbox" name="confirm_delete" value="1" x-model="confirmed" class="mt-1 rounded border-red-300 text-red-700 focus:ring-red-700/20">
                    <span class="text-sm font-bold">I understand this permanently deletes "{{ $section->title }}".</span>
                </label>
                <button type="submit" class="mt-3 inline-flex min-h-10 items-center justify-center rounded-lg bg-red-700 px-4 py-2 text-sm font-extrabold text-white transition hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-700/20 disabled:cursor-not-allowed disabled:opacity-60" x-bind:disabled="! confirmed || submitting" @disabled(! $canDelete)>
                    <span x-show="! submitting">Permanently delete</span>
                    <span x-cloak x-show="submitting">Deleting...</span>
                </button>
            </form>
        @else
            <p class="rounded-lg border border-stone-200 bg-stone-50 p-3 font-bold text-stone-700">Move this section to trash before permanent delete readiness is available.</p>
        @endif
    </div>
</x-admin.card>
