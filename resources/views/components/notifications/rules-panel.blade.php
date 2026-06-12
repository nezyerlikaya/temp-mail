@props(['rules', 'labels', 'modules', 'roles', 'warnings', 'digestReadiness', 'canUpdate'])

<x-admin.card title="Notification rules" description="Manage operational event delivery without turning notifications into marketing automation.">
    <div class="space-y-5">
        <x-notifications.dependency-warning :warnings="$warnings" :digest-readiness="$digestReadiness" />

        <form method="POST" action="{{ route('admin.notifications.rules.update') }}" class="space-y-3" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <x-admin.alert variant="danger" title="Rules need attention">
                    Review the highlighted notification rule settings and try again.
                </x-admin.alert>
            @endif

            @foreach ($rules as $rule)
                <x-notifications.rule-row
                    :rule="$rule"
                    :index="$loop->index"
                    :label="$labels[$rule->event_key] ?? str($rule->event_key)->replace('_', ' ')->headline()"
                    :module="$modules[$rule->event_key] ?? 'system'"
                    :roles="$roles"
                    :can-update="$canUpdate"
                />
            @endforeach

            <x-notifications.save-bar :disabled="! $canUpdate" />
        </form>
    </div>
</x-admin.card>
