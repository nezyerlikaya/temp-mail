@props(['activeGroup'])

<div
    x-data="{ dirty: false, submitting: false }"
    x-on:input="dirty = true"
    x-on:change="dirty = true"
    x-on:submit="submitting = true; dirty = false"
    x-on:beforeunload.window="if (dirty && ! submitting) { $event.preventDefault(); $event.returnValue = '' }"
>
    <x-settings.group-tabs :active-group="$activeGroup" />
    <div class="mt-6">{{ $slot }}</div>
</div>
