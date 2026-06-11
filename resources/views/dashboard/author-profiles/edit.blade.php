<x-admin.layout title="Edit author profile" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Author Profiles"
        title="Edit author profile"
        description="Manage public author identity and avatar readiness while preserving role and membership boundaries."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <form
                method="POST"
                action="{{ route('admin.author-profiles.update', $profileUser) }}"
                x-data="{ submitting: false }"
                x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true"
                x-bind:aria-busy="submitting.toString()"
                x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
                novalidate
            >
                @csrf
                @method('PUT')

                <x-admin.card title="Public author identity" description="Public visibility can be disabled without deleting attribution data.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.input name="display_name" label="Display name" :value="$profileUser->display_name" autocomplete="nickname" />
                        <x-form.input name="public_author_slug" label="Public author slug" :value="$profileUser->public_author_slug" autocomplete="off" help="Used by the future public author route." />
                        <x-form.input name="website" label="Website" type="url" :value="$profileUser->website" autocomplete="url" inputmode="url" />

                        <div class="sm:col-span-2">
                            <label for="author-bio" class="block text-sm font-bold text-stone-900">Author bio</label>
                            <textarea id="author-bio" name="author_bio" rows="7" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('author_bio') ? 'true' : 'false' }}" aria-describedby="author-bio-help {{ $errors->has('author_bio') ? 'author-bio-error' : '' }}">{{ old('author_bio', $profileUser->author_bio) }}</textarea>
                            <p id="author-bio-help" class="mt-2 text-sm text-stone-600">Up to 3,000 characters. Preserved for archived attribution.</p>
                            @error('author_bio')<p id="author-bio-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>

                        @foreach ($socialPlatforms as $key => $label)
                            @php($errorKey = 'social_links.'.$key)
                            <div>
                                <label for="social-{{ $key }}" class="block text-sm font-bold text-stone-900">{{ $label }}</label>
                                <input id="social-{{ $key }}" name="social_links[{{ $key }}]" type="url" value="{{ old($errorKey, data_get($profileUser->social_links, $key)) }}" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" inputmode="url" autocomplete="url" aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $errors->has($errorKey) ? 'social-'.$key.'-error' : '' }}">
                                @error($errorKey)<p id="social-{{ $key }}-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                            </div>
                        @endforeach

                        <div class="sm:col-span-2">
                            <x-users.public-profile-toggle :checked="old('author_profile_active', $profileUser->author_profile_active)" :disabled="$profileUser->status !== 'active'" />
                            @error('author_profile_active')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-start gap-3 rounded-md border border-stone-200 p-4 sm:col-span-2">
                            <input name="featured_author" type="checkbox" value="1" class="mt-0.5 size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-700" @checked(old('featured_author', $profileUser->featured_author))>
                            <span>
                                <span class="block text-sm font-extrabold text-stone-950">Featured author readiness</span>
                                <span class="mt-1 block text-sm text-stone-600">Marks this identity for future editorial placement. It does not publish content.</span>
                            </span>
                        </label>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-stone-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('admin.author-profiles.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 px-4 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Back to authors</a>
                        <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-5 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-wait disabled:bg-teal-900">
                            <span x-show="! submitting">Save author profile</span>
                            <span x-cloak x-show="submitting">Saving...</span>
                        </button>
                    </div>
                </x-admin.card>
            </form>

            <x-users.avatar-uploader :profile-user="$profileUser" :avatar="$avatar" />
        </div>

        <aside class="space-y-6" aria-label="Author readiness summary">
            <x-users.author-card :profile-user="$profileUser" :summary="$authorSummary" :avatar="$avatar" />
            <x-users.membership-card :membership="$membership" />
        </aside>
    </div>
</x-admin.layout>
