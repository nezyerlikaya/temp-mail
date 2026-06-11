@props(['profileUser', 'avatar', 'editable' => true])

<x-admin.card title="Avatar" description="Media Library-ready reference with an accessible initials fallback.">
    <form
        method="POST"
        action="{{ route('admin.people-identity.avatar.update', $profileUser) }}"
        x-data="{ submitting: false }"
        x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true"
        x-bind:aria-busy="submitting.toString()"
    >
        @csrf
        @method('PATCH')

        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <x-users.avatar-preview :avatar="$avatar" size="xl" />
            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-stone-950">{{ $avatar['has_media'] ? 'Media reference #'.$avatar['media_id'] : 'Initials fallback active' }}</p>
                <p class="mt-1 text-sm leading-6 text-stone-600">The Media Library picker will connect here without changing the user identity contract.</p>
                @if ($editable)
                    <a href="{{ route('admin.media-library.index') }}" class="mt-3 inline-flex min-h-9 items-center justify-center rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Open Media Library hook</a>
                @endif
            </div>
        </div>

        <input type="hidden" name="avatar_media_id" value="{{ $profileUser->avatar_media_id }}">

        <div class="mt-5 border-t border-stone-200 pt-5">
            <label for="avatar-color" class="block text-sm font-bold text-stone-900">Fallback color</label>
            <div class="mt-2 flex items-center gap-3">
                <input id="avatar-color" name="avatar_color" type="color" value="{{ old('avatar_color', $avatar['color']) }}" class="h-10 w-14 cursor-pointer rounded-md border border-stone-300 bg-white p-1 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:cursor-not-allowed disabled:opacity-60" aria-invalid="{{ $errors->has('avatar_color') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('avatar_color') ? 'avatar-color-error' : 'avatar-color-help' }}" @disabled(! $editable)>
                <p id="avatar-color-help" class="text-sm text-stone-600">Used whenever no media asset is available.</p>
            </div>
            @error('avatar_color')<p id="avatar-color-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
        </div>

        @if ($avatar['has_media'] && $editable)
            <label class="mt-4 flex items-start gap-2 text-sm text-stone-700">
                <input name="remove_avatar" type="checkbox" value="1" class="mt-0.5 size-4 rounded border-stone-300 text-red-700 focus:ring-red-700">
                <span>Remove the current media reference and use initials.</span>
            </label>
        @endif

        @if ($editable)
            <div class="mt-5 flex justify-end border-t border-stone-200 pt-5">
                <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-4 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-wait disabled:bg-teal-900">
                    <span x-show="! submitting">Save avatar</span>
                    <span x-cloak x-show="submitting">Saving...</span>
                </button>
            </div>
        @endif
    </form>
</x-admin.card>
