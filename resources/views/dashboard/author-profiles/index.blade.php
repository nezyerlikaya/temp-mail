<x-admin.layout title="Author Profiles" :user="$adminUser">
    <x-admin.page-header
        eyebrow="People"
        title="Author Profiles"
        description="Prepare durable author identities, public profile readiness, and avatar references without coupling them to membership plans."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-stone-950">Author readiness</h2>
            <p class="mt-1 text-sm text-stone-600">Profiles remain attributable even when public visibility is disabled or an account is suspended.</p>
        </div>
        <p class="text-sm font-semibold text-stone-500">{{ $users->total() }} identities</p>
    </div>

    @if ($users->isEmpty())
        <x-users.empty-state />
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($users as $profileUser)
                <x-users.author-card
                    :profile-user="$profileUser"
                    :summary="$profileSummaries[$profileUser->id]"
                    :avatar="$avatars[$profileUser->id]"
                    :edit-url="route('admin.author-profiles.edit', $profileUser)"
                />
            @endforeach
        </div>

        @if ($users->hasPages())
            <div class="mt-6">{{ $users->links() }}</div>
        @endif
    @endif
</x-admin.layout>
