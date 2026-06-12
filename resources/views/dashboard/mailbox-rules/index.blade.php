<x-admin.layout title="Mailbox Rules" :user="$adminUser">
    <x-admin.page-header eyebrow="Mail Infrastructure" title="Mailbox Rules" description="Control mailbox lifetime, capacity, cleanup, and delivery readiness from one focused policy surface." />

    @if(session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    @if($errors->any())
        <x-admin.alert variant="danger" class="mb-6" role="alert"><p class="font-extrabold">Review the highlighted mailbox rules.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-admin.alert>
    @endif

    <div class="space-y-6">
        <x-mailbox.health-summary :health="$health" :latest-health="$latestHealth" :history="$healthHistory" :can-run="$canRunHealth" />

        <form method="POST" action="{{ route('admin.mailbox-rules.update') }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }" class="space-y-6">
            @csrf @method('PUT')
            <x-mailbox.rules-panel :rules="$rules" :can-update="$canUpdate" />
            <x-mailbox.retention-preview :preview="$retention" />
            <x-mailbox.save-bar :can-update="$canUpdate" />
        </form>

        <x-mailbox.cleanup-warning :rules="$rules" :can-cleanup="$canCleanup" />
    </div>
</x-admin.layout>
