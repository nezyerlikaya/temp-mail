@props(['settings'])

<div class="space-y-5">
    <label class="flex items-start justify-between gap-4 rounded-md border border-amber-200 bg-amber-50 p-4">
        <span>
            <span class="block text-sm font-extrabold text-amber-950">Application maintenance mode</span>
            <span class="mt-1 block text-sm leading-6 text-amber-900">Public requests receive a polished maintenance response. Login and admin routes remain available to prevent lockout.</span>
        </span>
        <span class="relative mt-0.5 inline-flex shrink-0">
            <input name="enabled" type="checkbox" value="1" class="peer sr-only" @checked(old('enabled', $settings['enabled']))>
            <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-amber-700 peer-focus-visible:ring-4 peer-focus-visible:ring-amber-700/25"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
        </span>
    </label>

    <div>
        <label for="maintenance-message" class="block text-sm font-bold text-stone-900">Maintenance message</label>
        <textarea id="maintenance-message" name="message" rows="5" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}" aria-describedby="maintenance-message-help {{ $errors->has('message') ? 'maintenance-message-error' : '' }}">{{ old('message', $settings['message']) }}</textarea>
        <p id="maintenance-message-help" class="mt-2 text-sm text-stone-600">Shown publicly. Do not include internal infrastructure details.</p>
        @error('message')<p id="maintenance-message-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="allowed-admin-ips" class="block text-sm font-bold text-stone-900">Allowed public bypass IPs</label>
        <textarea id="allowed-admin-ips" name="allowed_admin_ips" rows="5" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 font-mono text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('allowed_admin_ips') || $errors->has('allowed_admin_ips.*') ? 'true' : 'false' }}" aria-describedby="allowed-admin-ips-help {{ $errors->has('allowed_admin_ips') || $errors->has('allowed_admin_ips.*') ? 'allowed-admin-ips-error' : '' }}">{{ old('allowed_admin_ips', implode("\n", $settings['allowed_admin_ips'])) }}</textarea>
        <p id="allowed-admin-ips-help" class="mt-2 text-sm text-stone-600">One IPv4 or IPv6 address per line. Admin authentication remains available regardless.</p>
        @if ($errors->has('allowed_admin_ips') || $errors->has('allowed_admin_ips.*'))<p id="allowed-admin-ips-error" class="mt-2 text-sm font-semibold text-red-700">{{ $errors->first('allowed_admin_ips') ?: $errors->first('allowed_admin_ips.*') }}</p>@endif
    </div>
</div>
