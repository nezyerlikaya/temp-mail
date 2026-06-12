@props(['settings', 'providers', 'forms', 'failModes', 'canUpdate' => false, 'canReveal' => false])

<x-admin.card title="Bot provider" description="Cloudflare Turnstile is recommended for the first production configuration.">
    <form method="POST" action="{{ route('admin.security-defense-center.bot.update') }}" class="space-y-5" x-data="{ provider: @js($settings['provider']), submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
        @csrf
        @method('PUT')

        <div class="grid gap-3 lg:grid-cols-3">
            @foreach ($providers as $value => $provider)
                <label class="rounded-lg border p-4 transition" x-bind:class="provider === '{{ $value }}' ? 'border-teal-500 bg-teal-50 ring-4 ring-teal-600/10' : 'border-stone-200 bg-white'">
                    <input type="radio" name="provider" value="{{ $value }}" x-model="provider" class="sr-only">
                    <span class="flex items-center justify-between gap-3">
                        <span class="font-extrabold text-stone-950">{{ $provider['label'] }}</span>
                        @if ($provider['recommended'])
                            <span class="rounded-full bg-teal-100 px-2 py-1 text-xs font-extrabold text-teal-900">Recommended</span>
                        @endif
                    </span>
                    <span class="mt-2 block text-sm leading-6 text-stone-600">{{ $provider['description'] }}</span>
                </label>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-security.secret-input name="site_key" label="Site key" :masked="$settings['secrets']['site_key'] ?? null" :can-reveal="$canReveal" :reveal-url="route('admin.security-defense-center.secret.reveal', ['bot_protection', 'site_key'])" />
            <x-security.secret-input name="secret_key" label="Secret key" :masked="$settings['secrets']['secret_key'] ?? null" :can-reveal="$canReveal" :reveal-url="route('admin.security-defense-center.secret.reveal', ['bot_protection', 'secret_key'])" />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">reCAPTCHA mode</span>
                <select name="recaptcha_mode" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="v2_checkbox" @selected($settings['recaptcha_mode'] === 'v2_checkbox')>v2 checkbox readiness</option>
                    <option value="v3_score" @selected($settings['recaptcha_mode'] === 'v3_score')>v3 score readiness</option>
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Minimum score</span>
                <input type="number" step="0.1" min="0" max="1" name="minimum_score" value="{{ $settings['minimum_score'] }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            </label>
            <x-security.fail-mode-selector :modes="$failModes" :selected="$settings['fail_mode']" />
        </div>

        <x-security.protected-form-list :forms="$forms" :selected="$settings['protected_forms'] ?? []" />

        <label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked($settings['is_active']) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
            <span>Active</span>
        </label>

        <button type="submit" @disabled(! $canUpdate) class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
            Save bot protection
        </button>
    </form>
</x-admin.card>
