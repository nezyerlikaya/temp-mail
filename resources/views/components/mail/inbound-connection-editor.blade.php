@props(['connection' => null, 'domains', 'encryptionOptions'])

@php
    $editing = filled($connection);
    $action = $editing ? route('admin.imap-smtp.update', $connection) : route('admin.imap-smtp.store');
@endphp

<x-admin.card :title="$editing ? 'Inbound connection settings' : 'Create inbound connection'" description="Configure read-only IMAP readiness access for one receiving domain.">
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-red-950" role="alert" aria-labelledby="inbound-error-title">
            <p id="inbound-error-title" class="text-sm font-extrabold">Connection settings need attention</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Connection name <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="name" value="{{ old('name', $connection?->name) }}" required autocomplete="off" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('name') ? 'connection-name-error' : '' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('name')<span id="connection-name-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Receiving domain <span class="text-red-700" aria-hidden="true">*</span></span>
                <select name="domain_id" required aria-invalid="{{ $errors->has('domain_id') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('domain_id') ? 'connection-domain-error' : '' }}" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="">Select domain</option>
                    @foreach ($domains as $domain)
                        <option value="{{ $domain->id }}" @selected((string) old('domain_id', $connection?->domain_id) === (string) $domain->id)>{{ $domain->domain_name }}</option>
                    @endforeach
                </select>
                @error('domain_id')<span id="connection-domain-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_180px]">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Inbound host <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="host" value="{{ old('host', $connection?->host) }}" placeholder="imap.example.com" required autocomplete="off" inputmode="url" aria-invalid="{{ $errors->has('host') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('host') ? 'connection-host-error' : '' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('host')<span id="connection-host-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <x-mail.port-field :value="$connection?->port ?? 993" />
        </div>

        <x-mail.encryption-selector :options="$encryptionOptions" :selected="$connection?->encryption ?? 'ssl'" />

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Username <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="username" value="{{ old('username', $connection?->username) }}" required autocomplete="username" autocapitalize="none" spellcheck="false" aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('username') ? 'connection-username-error' : '' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('username')<span id="connection-username-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <x-mail.secret-field :has-secret="$editing" :required="! $editing" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Mailbox / folder <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="mailbox" value="{{ old('mailbox', $connection?->mailbox ?? 'INBOX') }}" required autocomplete="off" aria-invalid="{{ $errors->has('mailbox') ? 'true' : 'false' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('mailbox')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Connection timeout (seconds) <span class="text-red-700" aria-hidden="true">*</span></span>
                <input type="number" name="connection_timeout" min="3" max="120" inputmode="numeric" value="{{ old('connection_timeout', $connection?->connection_timeout ?? 15) }}" required class="no-spinner min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('connection_timeout')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <label class="flex min-h-12 items-center gap-3 rounded-md border border-stone-200 bg-stone-50 px-4">
                <input type="hidden" name="validate_certificate" value="0">
                <input type="checkbox" name="validate_certificate" value="1" @checked(old('validate_certificate', $connection?->validate_certificate ?? true)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                <span>
                    <span class="block text-sm font-extrabold text-stone-900">Validate server certificate</span>
                    <span class="block text-xs text-stone-500">Recommended and enabled by default.</span>
                </span>
            </label>
            <label class="flex min-h-12 items-center gap-3 rounded-md border border-stone-200 bg-stone-50 px-4">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $connection?->is_active ?? false)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                <span>
                    <span class="block text-sm font-extrabold text-stone-900">Active connection</span>
                    <span class="block text-xs text-stone-500">Activation does not start automatic polling.</span>
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end border-t border-stone-200 pt-5">
            <button x-bind:disabled="submitting" class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                <span x-show="!submitting">{{ $editing ? 'Save connection' : 'Create connection' }}</span>
                <span x-cloak x-show="submitting">Saving...</span>
            </button>
        </div>
    </form>
</x-admin.card>
