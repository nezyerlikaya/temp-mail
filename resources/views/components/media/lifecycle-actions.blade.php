@props([
    'asset',
    'lifecycle',
    'canUpdate' => false,
    'canTrash' => false,
    'canRestore' => false,
    'canDelete' => false,
])

<section
    class="rounded-lg border border-stone-200 bg-white shadow-sm"
    x-data="{ deleteOpen: {{ $errors->has('delete_confirmation') || $errors->has('confirm_in_use_delete') ? 'true' : 'false' }}, submitting: false }"
    x-on:keydown.escape.window="deleteOpen = false"
    aria-labelledby="media-lifecycle-title"
>
    <header class="border-b border-stone-200 px-5 py-4">
        <h2 id="media-lifecycle-title" class="text-base font-extrabold text-stone-950">Lifecycle controls</h2>
        <p class="mt-1 text-sm text-stone-600">Control visibility, trash, restore, and permanent deletion.</p>
    </header>

    <div class="space-y-4 p-5">
        @if ($asset->status !== 'trashed')
            <form method="POST" action="{{ route('admin.media-library.status.update', $asset) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <label for="media-lifecycle-status" class="text-sm font-extrabold text-stone-950">Visibility</label>
                <div class="flex gap-2">
                    <select id="media-lifecycle-status" name="status" class="min-h-11 min-w-0 flex-1 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canUpdate)>
                        <option value="active" @selected($asset->status === 'active')>Active</option>
                        <option value="hidden" @selected($asset->status === 'hidden')>Hidden</option>
                    </select>
                    <button type="submit" class="min-h-11 rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-800 hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60" @disabled(! $canUpdate)>Update</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.media-library.trash', $asset) }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-extrabold text-red-800 hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-600/20 disabled:opacity-60" @disabled(! $canTrash)>
                    <i data-lucide="trash-2" class="size-4" aria-hidden="true"></i>
                    Move to trash
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.media-library.restore', $asset) }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:opacity-60" @disabled(! $canRestore)>
                    <i data-lucide="rotate-ccw" class="size-4" aria-hidden="true"></i>
                    Restore asset
                </button>
            </form>

            <button
                type="button"
                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-red-300 px-4 text-sm font-extrabold text-red-800 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-600/20 disabled:opacity-60"
                x-on:click="deleteOpen = true; $nextTick(() => $refs.deleteDialog.focus())"
                @disabled(! $canDelete)
            >
                <i data-lucide="shield-alert" class="size-4" aria-hidden="true"></i>
                Delete permanently
            </button>
        @endif
    </div>

    <template x-teleport="body">
        <div x-cloak x-show="deleteOpen" class="fixed inset-0 z-50 grid place-items-center bg-stone-950/60 p-4" role="presentation" x-on:click.self="deleteOpen = false">
            <section
                x-ref="deleteDialog"
                tabindex="-1"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="delete-media-title"
                aria-describedby="delete-media-description"
                class="w-full max-w-lg rounded-lg bg-white shadow-2xl focus:outline-none"
            >
                <header class="border-b border-stone-200 px-5 py-4">
                    <h2 id="delete-media-title" class="text-lg font-extrabold text-stone-950">Delete media permanently?</h2>
                    <p id="delete-media-description" class="mt-1 text-sm text-stone-600">This action removes the stored file and cannot be reversed.</p>
                </header>

                <form
                    method="POST"
                    action="{{ route('admin.media-library.destroy', $asset) }}"
                    class="space-y-5 p-5"
                    x-on:submit="if (submitting) { $event.preventDefault(); return } submitting = true"
                    x-bind:aria-busy="submitting"
                    x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
                >
                    @csrf
                    @method('DELETE')
                    <x-media.delete-warning :asset="$asset" :lifecycle="$lifecycle" :can-delete="$canDelete" />

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" class="min-h-11 rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-stone-400/20" x-on:click="deleteOpen = false">Cancel</button>
                        <button type="submit" class="min-h-11 rounded-lg bg-red-700 px-4 text-sm font-extrabold text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-600/25 disabled:opacity-60" @disabled(! $canDelete)>
                            <span x-show="! submitting">Delete permanently</span>
                            <span x-cloak x-show="submitting">Deleting...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </template>
</section>
