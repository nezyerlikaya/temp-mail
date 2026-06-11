<x-admin.layout :title="$profileUser->display_name ?: $profileUser->name" :user="$adminUser">
    <x-admin.page-header
        eyebrow="People & Identity"
        :title="$profileUser->display_name ?: $profileUser->name"
        description="Review identity details, account status, role classification, and profile readiness."
    >
        <x-slot:actions>
            <a href="{{ route('admin.people-identity.edit', $profileUser) }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-4 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25">Edit identity</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" title="Identity updated" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-users.profile-shell :profile-user="$profileUser">
        <x-admin.card title="Identity details" description="Core account information used across admin and member experiences.">
            <dl class="grid gap-x-8 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-bold uppercase text-stone-500">Full name</dt>
                    <dd class="mt-1 text-sm font-semibold text-stone-950">{{ $profileUser->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-stone-500">Display name</dt>
                    <dd class="mt-1 text-sm font-semibold text-stone-950">{{ $profileUser->display_name ?: 'Not set' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-stone-500">Username</dt>
                    <dd class="mt-1 text-sm font-semibold text-stone-950">{{ $profileUser->username ? '@'.$profileUser->username : 'Not set' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-stone-500">Email</dt>
                    <dd class="mt-1 break-all text-sm font-semibold text-stone-950">{{ $profileUser->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-stone-500">Timezone</dt>
                    <dd class="mt-1 text-sm font-semibold text-stone-950">{{ $profileUser->timezone }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase text-stone-500">Language preference</dt>
                    <dd class="mt-1 text-sm font-semibold uppercase text-stone-950">{{ $profileUser->language_preference }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold uppercase text-stone-500">Website</dt>
                    <dd class="mt-1 text-sm font-semibold text-stone-950">{{ $profileUser->website ?: 'Not set' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold uppercase text-stone-500">Bio</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $profileUser->bio ?: 'No profile biography has been added.' }}</dd>
                </div>
            </dl>
        </x-admin.card>
    </x-users.profile-shell>
</x-admin.layout>
