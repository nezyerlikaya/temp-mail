@props(['users', 'scopes', 'canCreate' => false, 'canManageGlobally' => false])

<x-admin.card title="Create API key" description="Generate a scoped test or live key. The secret appears once after submission.">
    <form method="POST" action="{{ route('admin.api-access.keys.store') }}" class="grid gap-4 lg:grid-cols-2" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <div>
            <label for="api-key-name" class="text-sm font-extrabold text-stone-800">Key name</label>
            <input id="api-key-name" name="name" value="{{ old('name') }}" required maxlength="80" @disabled(! $canCreate) aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('name') ? 'api-key-name-error' : '' }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
            @error('name')<p id="api-key-name-error" class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="api-key-user" class="text-sm font-extrabold text-stone-800">Owner</label>
            <select id="api-key-user" name="user_id" @disabled(! $canCreate || ! $canManageGlobally) class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', auth()->id()) === (string) $user->id)>{{ $user->email }}</option>
                @endforeach
            </select>
            @error('user_id')<p class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="api-key-environment" class="text-sm font-extrabold text-stone-800">Environment</label>
            <select id="api-key-environment" name="environment" @disabled(! $canCreate) class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
                <option value="test" @selected(old('environment') === 'test')>Test - tm_test_</option>
                <option value="live" @selected(old('environment') === 'live')>Live - tm_live_</option>
            </select>
            @error('environment')<p class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
        </div>

        <x-api.expiration-field :disabled="! $canCreate" />
        <x-api.ip-allowlist-field :disabled="! $canCreate" />
        <x-api.scope-list :scopes="$scopes" :disabled="! $canCreate" class="lg:col-span-2" />

        <div class="lg:col-span-2">
            <button type="submit" @disabled(! $canCreate) x-bind:disabled="submitting || {{ $canCreate ? 'false' : 'true' }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60 sm:w-auto">
                <span x-show="!submitting">Create key</span>
                <span x-cloak x-show="submitting">Creating...</span>
            </button>
            @unless($canCreate)
                <p class="mt-2 text-sm font-bold text-amber-800">API access must be globally active and permitted by the owner's plan before a key can be created.</p>
            @endunless
        </div>
    </form>
</x-admin.card>
