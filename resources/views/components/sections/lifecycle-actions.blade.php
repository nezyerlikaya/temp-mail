@props(['section', 'canActivate' => false, 'canHide' => false, 'canTrash' => false, 'canRestore' => false])

<x-admin.card title="Lifecycle actions" description="Move this language-specific section through rendering states.">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <form method="POST" action="{{ route('admin.sections-studio.activate', $section) }}">
            @csrf
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canActivate || $section->status === 'active' || $section->status === 'trashed')>
                Activate
            </button>
        </form>

        <form method="POST" action="{{ route('admin.sections-studio.hide', $section) }}">
            @csrf
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-stone-400/20 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canHide || $section->status === 'hidden' || $section->status === 'trashed')>
                Hide
            </button>
        </form>

        @if ($section->status === 'trashed')
            <form method="POST" action="{{ route('admin.sections-studio.restore', $section) }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-extrabold text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus:ring-4 focus:ring-emerald-600/20 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canRestore)>
                    Restore
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.sections-studio.trash', $section) }}" x-data="{ confirmed: false }" x-on:submit="if (! confirmed) $event.preventDefault()" class="space-y-2">
                @csrf
                <input type="hidden" name="confirm_trash" x-bind:value="confirmed ? '1' : ''">
                <label class="flex min-h-11 items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-900">
                    <input type="checkbox" x-model="confirmed" class="rounded border-red-300 text-red-700 focus:ring-red-700/20">
                    Confirm trash
                </label>
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-extrabold text-red-800 transition hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-700/20 disabled:cursor-not-allowed disabled:opacity-60" x-bind:disabled="! confirmed" @disabled(! $canTrash)>
                    Trash
                </button>
            </form>
        @endif
    </div>
</x-admin.card>
