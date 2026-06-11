@props(['profileUser'])

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="min-w-0">{{ $slot }}</div>

    <aside class="space-y-4" aria-label="User account summary">
        <x-users.identity-card :profile-user="$profileUser" />

        <x-admin.card title="Account readiness">
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-600">Avatar media</dt>
                    <dd class="font-bold text-stone-900">{{ $profileUser->avatar_media_id ? 'Connected' : 'Ready' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-600">Public author page</dt>
                    <dd class="font-bold text-stone-900">Deferred</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-600">Audit events</dt>
                    <dd class="font-bold text-emerald-700">Enabled</dd>
                </div>
            </dl>
        </x-admin.card>
    </aside>
</div>
