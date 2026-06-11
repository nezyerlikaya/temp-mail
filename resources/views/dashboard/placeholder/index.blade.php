<x-admin.layout :title="$navigationItem['label']" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Module foundation"
        :title="$navigationItem['label']"
        description="This workspace is connected to the admin navigation and ready for its dedicated module implementation."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Draft" />
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :title="$navigationItem['label']" description="The route, authorization boundary, and shared admin shell are ready.">
        <x-admin.empty-state
            title="Module workspace coming next"
            description="Features and operational data for this module are intentionally deferred to its dedicated implementation step."
        />
    </x-admin.card>
</x-admin.layout>
