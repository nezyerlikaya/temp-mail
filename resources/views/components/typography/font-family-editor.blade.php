@props(['family', 'canManage' => false, 'fontDisplayOptions' => []])

@if ($canManage)
    <form method="POST" action="{{ route('admin.typography-center.families.update', $family) }}" class="mt-4 border-t border-stone-200 pt-4" x-data="{ busy: false }" x-on:submit="busy = true" x-bind:aria-busy="busy">
        @csrf
        @method('PUT')
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="grid gap-2 text-sm font-bold text-stone-700">
                <span>Font display</span>
                <select name="font_display" class="min-h-10 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                    @foreach ($fontDisplayOptions as $option)
                        <option value="{{ $option }}" @selected(old('font_display', $family->font_display) === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex min-h-10 items-center gap-2 rounded-md border border-stone-200 px-3 text-sm font-bold text-stone-700">
                <input type="checkbox" name="local_file_ready" value="1" @checked(old('local_file_ready', $family->local_file_ready)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                Self-hosted ready
            </label>
            <label class="flex min-h-10 items-center gap-2 rounded-md border border-stone-200 px-3 text-sm font-bold text-stone-700">
                <input type="checkbox" name="media_ready" value="1" @checked(old('media_ready', $family->media_ready)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                Media ready
            </label>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit" x-bind:disabled="busy" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-stone-950 px-3 py-2 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20 disabled:cursor-not-allowed disabled:opacity-60">
                <i data-lucide="save" class="size-4" aria-hidden="true"></i>
                <span x-text="busy ? 'Saving...' : 'Save'">Save</span>
            </button>
        </div>
    </form>
    <form method="POST" action="{{ $family->is_active ? route('admin.typography-center.families.deactivate', $family) : route('admin.typography-center.families.activate', $family) }}" class="mt-2">
        @csrf
        <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
            <i data-lucide="{{ $family->is_active ? 'pause-circle' : 'play-circle' }}" class="size-4" aria-hidden="true"></i>
            {{ $family->is_active ? 'Deactivate' : 'Activate' }}
        </button>
    </form>
@endif
