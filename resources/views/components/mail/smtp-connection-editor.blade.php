@props(['connection' => null, 'domains', 'encryptionOptions'])

@php
    $editing = filled($connection);
    $action = $editing ? route('admin.imap-smtp.smtp.update', $connection) : route('admin.imap-smtp.smtp.store');
@endphp

<x-admin.card :title="$editing ? 'SMTP connection settings' : 'Create SMTP connection'" description="Configure transactional delivery for password resets, verification emails, notifications, and admin messages.">
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-red-950" role="alert" aria-labelledby="smtp-error-title">
            <p id="smtp-error-title" class="text-sm font-extrabold">SMTP settings need attention</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Connection name <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="name" value="{{ old('name', $connection?->name) }}" required autocomplete="off" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('name')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Related domain</span>
                <select name="domain_id" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="">System mail identity</option>
                    @foreach ($domains as $domain)
                        <option value="{{ $domain->id }}" @selected((string) old('domain_id', $connection?->domain_id) === (string) $domain->id)>{{ $domain->domain_name }}</option>
                    @endforeach
                </select>
                @error('domain_id')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_180px]">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">SMTP host <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="host" value="{{ old('host', $connection?->host) }}" placeholder="smtp.example.com" required autocomplete="off" inputmode="url" aria-invalid="{{ $errors->has('host') ? 'true' : 'false' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('host')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <x-mail.port-field :value="$connection?->port ?? 587" />
        </div>

        <x-mail.encryption-selector :options="$encryptionOptions" :selected="$connection?->encryption ?? 'tls'" />

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Username <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="username" value="{{ old('username', $connection?->username) }}" required autocomplete="username" autocapitalize="none" spellcheck="false" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('username')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <x-mail.secret-field :has-secret="$editing" :required="! $editing" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">From email <span class="text-red-700" aria-hidden="true">*</span></span>
                <input type="email" name="from_email" value="{{ old('from_email', $connection?->from_email) }}" required inputmode="email" autocomplete="email" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('from_email')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">From name <span class="text-red-700" aria-hidden="true">*</span></span>
                <input name="from_name" value="{{ old('from_name', $connection?->from_name ?? config('app.name')) }}" required autocomplete="organization" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('from_name')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Reply-to email</span>
                <input type="email" name="reply_to_email" value="{{ old('reply_to_email', $connection?->reply_to_email) }}" inputmode="email" autocomplete="email" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('reply_to_email')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Connection timeout (seconds) <span class="text-red-700" aria-hidden="true">*</span></span>
                <input type="number" name="connection_timeout" min="3" max="120" inputmode="numeric" value="{{ old('connection_timeout', $connection?->connection_timeout ?? 15) }}" required class="no-spinner min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('connection_timeout')<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ([['validate_certificate', 'Validate certificate', 'Recommended and enabled by default.', true], ['is_active', 'Active connection', 'Available for system delivery.', false], ['is_default', 'Default SMTP', 'Use for transactional mail.', false]] as [$field, $label, $help, $default])
                <label class="flex min-h-12 items-center gap-3 rounded-md border border-stone-200 bg-stone-50 px-4">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $connection?->{$field} ?? $default)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                    <span>
                        <span class="block text-sm font-extrabold text-stone-900">{{ $label }}</span>
                        <span class="block text-xs text-stone-500">{{ $help }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="flex items-center justify-end border-t border-stone-200 pt-5">
            <button x-bind:disabled="submitting" class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                <span x-show="!submitting">{{ $editing ? 'Save SMTP connection' : 'Create SMTP connection' }}</span>
                <span x-cloak x-show="submitting">Saving...</span>
            </button>
        </div>
    </form>
</x-admin.card>
