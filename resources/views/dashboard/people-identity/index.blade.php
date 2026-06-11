<x-admin.layout title="People & Identity" :user="$adminUser">
    <x-admin.page-header
        eyebrow="People"
        title="People & Identity"
        description="Manage account identity, access status, profile readiness, and the separation between product membership and admin access."
    />

    <x-admin.card title="Identity directory" description="Search by identity, role, status, or account creation date.">
        <x-users.user-filter-bar :roles="$roles" :statuses="$statuses" />

        @if ($users->isEmpty())
            <x-users.empty-state class="mt-5 border-t border-stone-200" />
        @else
            <div class="mt-5 overflow-x-auto border-t border-stone-200">
                <table class="w-full min-w-[760px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-xs font-bold uppercase text-stone-500">
                            <th class="px-5 py-3 sm:px-6" scope="col">Identity</th>
                            <th class="px-4 py-3" scope="col">Role</th>
                            <th class="px-4 py-3" scope="col">Status</th>
                            <th class="hidden px-4 py-3 lg:table-cell" scope="col">Timezone</th>
                            <th class="hidden px-4 py-3 xl:table-cell" scope="col">Created</th>
                            <th class="px-5 py-3 text-right sm:px-6" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $profileUser)
                            <x-users.user-row :profile-user="$profileUser" />
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="mt-5 border-t border-stone-200 pt-5">{{ $users->links() }}</div>
            @endif
        @endif
    </x-admin.card>
</x-admin.layout>
