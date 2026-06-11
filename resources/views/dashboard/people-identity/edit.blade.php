<x-admin.layout title="Edit identity" :user="$adminUser">
    <x-admin.page-header
        eyebrow="People & Identity"
        title="Edit identity"
        description="Update account identity and profile readiness. Role changes use the protected Roles & Permissions workflow."
    />

    <x-error-summary />

    <x-users.profile-shell :profile-user="$profileUser">
        <form
            method="POST"
            action="{{ route('admin.people-identity.update', $profileUser) }}"
            x-data="{ submitting: false }"
            x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true"
            x-bind:aria-busy="submitting.toString()"
            x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
            novalidate
        >
            @csrf
            @method('PUT')

            <x-admin.card title="Identity profile" description="Fields marked by validation errors must be corrected before saving.">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="name" label="Full name" :value="$profileUser->name" autocomplete="name" />
                    <x-form.input name="display_name" label="Display name" :value="$profileUser->display_name" autocomplete="nickname" />
                    <x-form.input name="username" label="Username" :value="$profileUser->username" autocomplete="username" help="Use letters, numbers, dashes, and underscores." />
                    <x-form.input name="email" label="Email" type="email" :value="$profileUser->email" autocomplete="email" inputmode="email" />

                    <div>
                        <label for="status" class="block text-sm font-bold text-stone-900">Account status</label>
                        <select id="status" name="status" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('status') ? 'status-error' : 'status-help' }}">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $profileUser->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p id="status-help" class="mt-2 text-sm text-stone-600">Suspended accounts cannot sign in.</p>
                        @error('status')<p id="status-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-bold text-stone-900">Timezone</label>
                        <select id="timezone" name="timezone" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('timezone') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('timezone') ? 'timezone-error' : '' }}">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', $profileUser->timezone) === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                        @error('timezone')<p id="timezone-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="language_preference" class="block text-sm font-bold text-stone-900">Language preference</label>
                        <select id="language_preference" name="language_preference" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('language_preference') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('language_preference') ? 'language-preference-error' : '' }}">
                            @foreach ($languages as $value => $label)
                                <option value="{{ $value }}" @selected(old('language_preference', $profileUser->language_preference) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('language_preference')<p id="language-preference-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <x-form.input name="website" label="Website" type="url" :value="$profileUser->website" autocomplete="url" inputmode="url" />

                    <div class="sm:col-span-2">
                        <label for="bio" class="block text-sm font-bold text-stone-900">Bio</label>
                        <textarea id="bio" name="bio" rows="6" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('bio') ? 'true' : 'false' }}" aria-describedby="bio-help {{ $errors->has('bio') ? 'bio-error' : '' }}">{{ old('bio', $profileUser->bio) }}</textarea>
                        <p id="bio-help" class="mt-2 text-sm text-stone-600">Profile-ready biography, maximum 2,000 characters.</p>
                        @error('bio')<p id="bio-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-7 flex flex-col-reverse gap-3 border-t border-stone-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.people-identity.show', $profileUser) }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 px-4 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Cancel</a>
                    <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-5 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-wait disabled:bg-teal-900">
                        <span x-show="! submitting">Save identity</span>
                        <span x-cloak x-show="submitting">Saving identity...</span>
                    </button>
                </div>
            </x-admin.card>
        </form>
    </x-users.profile-shell>
</x-admin.layout>
