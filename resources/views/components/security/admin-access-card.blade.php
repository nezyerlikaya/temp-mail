@props(['settings', 'readiness', 'canUpdate' => false])

<x-admin.card title="Admin access security" description="Focused safeguards for administrator entry, session lifetime, and readiness-only requirements.">
    <form method="POST" action="{{ route('admin.security-defense-center.admin-access.update') }}" class="space-y-5" x-data="{ submitting: false }" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
        @csrf
        @method('PUT')

        <x-security.password-policy :settings="$settings" :readiness="$readiness" :can-update="$canUpdate" />
        <x-security.session-lifetime-control :value="$settings['admin_session_lifetime']" :status="$readiness['session_lifetime']" :can-update="$canUpdate" />

        <div class="grid gap-3 md:grid-cols-2">
            @foreach ([
                ['require_email_verification', 'Require email verification', 'email_verification'],
                ['login_alerts', 'Login alert readiness', 'login_alerts'],
                ['admin_ip_allowlist_ready', 'Admin IP allowlist readiness', 'admin_ip_allowlist'],
                ['require_2fa_readiness', '2FA requirement readiness', 'two_factor'],
                ['critical_notifications_ready', 'Critical notification readiness', 'critical_notifications'],
            ] as [$field, $label, $status])
                <label class="flex min-h-14 items-center justify-between gap-3 rounded-lg border border-stone-200 bg-white p-3">
                    <span class="text-sm font-bold text-stone-800">{{ $label }}</span>
                    <span class="flex items-center gap-2">
                        <x-security.status-badge :status="$readiness[$status]" />
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings[$field])) @disabled(! $canUpdate) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                    </span>
                </label>
            @endforeach
        </div>

        <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-extrabold text-teal-950">Owner and last admin protection</p>
                <x-security.status-badge :status="$readiness['owner_last_admin_protection']" />
            </div>
            <p class="mt-2 text-sm leading-6 text-teal-900">People & Identity protections remain authoritative for owner and last administrator changes.</p>
        </div>

        <x-security.save-bar label="Save admin security" :can-submit="$canUpdate">
            2FA is readiness-only in this step; no provider enrollment flow is added.
        </x-security.save-bar>
    </form>
</x-admin.card>
