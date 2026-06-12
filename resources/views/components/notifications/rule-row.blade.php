@props(['rule', 'index', 'label', 'module', 'roles', 'canUpdate'])

@php($critical = $rule->severity === 'critical')

<div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <input type="hidden" name="rules[{{ $index }}][event_key]" value="{{ $rule->event_key }}">

    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-notifications.severity-badge :severity="$rule->severity" />
                <span class="rounded-full bg-stone-100 px-2 py-1 text-xs font-bold text-stone-600">{{ str($module)->replace('-', ' ')->headline() }}</span>
                @if ($critical)
                    <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-extrabold text-red-800">Protected</span>
                @endif
            </div>
            <h3 class="mt-3 text-base font-extrabold text-stone-950">{{ $label }}</h3>
            <p class="mt-1 text-sm leading-6 text-stone-600">
                {{ $critical ? 'Critical events bypass digest and quiet hours.' : 'Tune channels, recipients, digest, and quiet-hours readiness.' }}
            </p>
        </div>

        <div class="grid min-w-0 gap-3 xl:w-[620px]">
            <div class="flex flex-wrap gap-2">
                <x-notifications.channel-toggle name="rules[{{ $index }}][is_active]" label="Active" :checked="$rule->is_active" :disabled="! $canUpdate || $critical" />
                <x-notifications.channel-toggle name="rules[{{ $index }}][in_app_enabled]" label="In-app" :checked="$rule->in_app_enabled" :disabled="! $canUpdate || $critical" />
                <x-notifications.channel-toggle name="rules[{{ $index }}][email_enabled]" label="Email" :checked="$rule->email_enabled" :disabled="! $canUpdate" />
                <x-notifications.channel-toggle name="rules[{{ $index }}][quiet_hours_enabled]" label="Quiet hours" :checked="$rule->quiet_hours_enabled" :disabled="! $canUpdate || $critical" />
            </div>

            <div class="grid gap-3 md:grid-cols-[1fr_220px]">
                <x-notifications.recipient-picker name="rules[{{ $index }}][recipient_roles]" :selected="$rule->recipient_roles ?? []" :roles="$roles" :disabled="! $canUpdate || $critical" />
                <x-notifications.digest-control name="rules[{{ $index }}][digest_mode]" :value="$rule->digest_mode" :disabled="! $canUpdate || $critical" />
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-extrabold uppercase text-stone-500">Quiet start</span>
                    <input type="time" name="rules[{{ $index }}][quiet_hours_start]" value="{{ $rule->quiet_hours_start }}" @disabled(! $canUpdate || $critical) class="min-h-10 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100 disabled:text-stone-500">
                </label>
                <label class="grid gap-2">
                    <span class="text-xs font-extrabold uppercase text-stone-500">Quiet end</span>
                    <input type="time" name="rules[{{ $index }}][quiet_hours_end]" value="{{ $rule->quiet_hours_end }}" @disabled(! $canUpdate || $critical) class="min-h-10 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100 disabled:text-stone-500">
                </label>
            </div>
        </div>
    </div>
</div>
