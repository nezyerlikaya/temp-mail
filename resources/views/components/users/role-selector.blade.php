@props(['profileUser', 'roleOptions', 'actorRole'])

@php
    $formId = 'role-form-'.$profileUser->id;
    $roleId = 'role-'.$profileUser->id;
    $confirmId = 'confirm-critical-change-'.$profileUser->id;
    $isProtectedOwner = $profileUser->role === 'owner' && $actorRole !== 'owner';
@endphp

<form
    id="{{ $formId }}"
    method="POST"
    action="{{ route('admin.roles-permissions.update', $profileUser) }}"
    class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_200px_240px_auto] lg:items-center"
    x-data="{ submitting: false, role: @js($profileUser->role) }"
    x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true"
    x-bind:aria-busy="submitting.toString()"
>
    @csrf
    @method('PATCH')

    <div class="flex min-w-0 items-center gap-3">
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-teal-100 text-sm font-extrabold text-teal-900" aria-hidden="true">
            {{ str($profileUser->display_name ?: $profileUser->name)->substr(0, 1)->upper() }}
        </span>
        <div class="min-w-0">
            <p class="truncate font-bold text-stone-950">{{ $profileUser->display_name ?: $profileUser->name }}</p>
            <p class="truncate text-sm text-stone-500">{{ $profileUser->email }}</p>
        </div>
    </div>

    <div>
        <label for="{{ $roleId }}" class="sr-only">Role for {{ $profileUser->name }}</label>
        <select
            id="{{ $roleId }}"
            name="role"
            x-model="role"
            @disabled($isProtectedOwner)
            class="block h-10 w-full rounded-md border border-stone-300 bg-white px-3 text-sm font-semibold text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15 disabled:cursor-not-allowed disabled:bg-stone-100"
            aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
            aria-describedby="{{ $formId }}-help"
        >
            @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}" @selected($profileUser->role === $value) @disabled($value === 'owner' && $actorRole !== 'owner')>{{ $label }}</option>
            @endforeach
        </select>
        <p id="{{ $formId }}-help" class="sr-only">Membership plans do not grant these permissions.</p>
    </div>

    <label class="flex min-h-10 items-start gap-2 text-sm text-stone-700" x-show="role !== @js($profileUser->role)" x-cloak>
        <input id="{{ $confirmId }}" name="confirm_critical_change" type="checkbox" value="1" class="mt-0.5 size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-700">
        <span>I understand this changes admin access.</span>
    </label>

    <button type="submit" x-bind:disabled="submitting || role === @js($profileUser->role)" class="inline-flex h-10 items-center justify-center rounded-md bg-teal-700 px-4 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-not-allowed disabled:bg-stone-300 disabled:text-stone-600">
        <span x-show="! submitting">Update role</span>
        <span x-cloak x-show="submitting">Updating...</span>
    </button>
</form>
