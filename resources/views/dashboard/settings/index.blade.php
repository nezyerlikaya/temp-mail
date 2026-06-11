<x-admin.layout title="System Settings Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="System"
        title="System Settings Center"
        description="Manage global identity, localization defaults, maintenance behavior, legal references, and system readiness."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <x-settings.settings-layout :active-group="$activeGroup">
        @if ($activeGroup === 'general')
            <form method="POST" action="{{ route('admin.settings.general.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-settings.setting-card title="General" description="Global product identity, contact channels, and formatting defaults.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.input name="site_name" label="Site name" :value="$settings['general']['site_name']" autocomplete="organization" required />
                        <x-form.input name="site_tagline" label="Site tagline" :value="$settings['general']['site_tagline']" />
                        <x-settings.email-field name="admin_email" label="Admin email" :value="$settings['general']['admin_email']" required />
                        <x-settings.email-field name="support_email" label="Support email" :value="$settings['general']['support_email']" required />
                        <x-settings.email-field name="abuse_email" label="Abuse email" :value="$settings['general']['abuse_email']" required help="Required for abuse reporting and trust workflows." />
                        <x-settings.language-select name="default_language" label="Default language" :value="$settings['general']['default_language']" :languages="$languages" />
                        <x-settings.timezone-select :value="$settings['general']['default_timezone']" :timezones="$timezones" />
                        <div>
                            <label for="date_format" class="block text-sm font-bold text-stone-900">Date format</label>
                            <select id="date_format" name="date_format" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                                @foreach (['M j, Y' => 'Jun 11, 2026', 'Y-m-d' => '2026-06-11', 'd/m/Y' => '11/06/2026', 'm/d/Y' => '06/11/2026'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('date_format', $settings['general']['date_format']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="time_format" class="block text-sm font-bold text-stone-900">Time format</label>
                            <select id="time_format" name="time_format" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                                <option value="H:i" @selected(old('time_format', $settings['general']['time_format']) === 'H:i')>24 hour (18:30)</option>
                                <option value="h:i A" @selected(old('time_format', $settings['general']['time_format']) === 'h:i A')>12 hour (06:30 PM)</option>
                            </select>
                        </div>
                    </div>
                </x-settings.setting-card>
                <x-settings.save-bar group="general" />
            </form>
        @elseif ($activeGroup === 'brand')
            <form method="POST" action="{{ route('admin.settings.brand.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-settings.setting-card title="Brand" description="Global brand identity references. Theme styling remains in its own modules.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.input name="public_site_name" label="Public site name" :value="$settings['brand']['public_site_name']" required />
                        <x-form.input name="footer_brand_text" label="Footer brand text" :value="$settings['brand']['footer_brand_text']" required />
                    </div>
                    <div class="mt-6 grid gap-4 lg:grid-cols-3">
                        <x-settings.media-picker-field name="logo_media_id" :asset="$brandAssets['logo']" />
                        <x-settings.media-picker-field name="favicon_media_id" :asset="$brandAssets['favicon']" />
                        <x-settings.media-picker-field name="app_icon_media_id" :asset="$brandAssets['app_icon']" />
                    </div>
                </x-settings.setting-card>
                <x-settings.save-bar group="brand" />
            </form>
        @elseif ($activeGroup === 'localization')
            <form method="POST" action="{{ route('admin.settings.localization.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-settings.setting-card title="Localization Defaults" description="Fallback behavior only. Locale lifecycle remains in Locale Launch Center.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-settings.language-select name="default_locale" label="Default locale" :value="$settings['localization']['default_locale']" :languages="$languages" />
                        <x-settings.language-select name="fallback_locale" label="Fallback locale" :value="$settings['localization']['fallback_locale']" :languages="$languages" />
                        <label class="flex items-start justify-between gap-4 rounded-md border border-stone-200 p-4 sm:col-span-2">
                            <span><span class="block text-sm font-extrabold">RTL auto-detection</span><span class="mt-1 block text-sm text-stone-600">Automatically use direction metadata from an active locale.</span></span>
                            <span class="relative mt-0.5 inline-flex shrink-0"><input name="rtl_auto_detection" type="checkbox" value="1" class="peer sr-only" @checked(old('rtl_auto_detection', $settings['localization']['rtl_auto_detection']))><span class="h-6 w-11 rounded-full bg-stone-300 peer-checked:bg-teal-700 peer-focus-visible:ring-4 peer-focus-visible:ring-teal-700/25"></span><span class="pointer-events-none absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span></span>
                        </label>
                        <div class="sm:col-span-2">
                            <label for="missing_locale_behavior" class="block text-sm font-bold text-stone-900">Missing locale behavior</label>
                            <select id="missing_locale_behavior" name="missing_locale_behavior" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                                <option value="fallback" @selected(old('missing_locale_behavior', $settings['localization']['missing_locale_behavior']) === 'fallback')>Use fallback locale</option>
                                <option value="source" @selected(old('missing_locale_behavior', $settings['localization']['missing_locale_behavior']) === 'source')>Show canonical source</option>
                                <option value="not_found" @selected(old('missing_locale_behavior', $settings['localization']['missing_locale_behavior']) === 'not_found')>Return not found</option>
                            </select>
                        </div>
                    </div>
                </x-settings.setting-card>
                <x-settings.save-bar group="localization" />
            </form>
        @elseif ($activeGroup === 'maintenance')
            <form method="POST" action="{{ route('admin.settings.maintenance.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-settings.setting-card title="Maintenance" description="Protect public traffic without locking authenticated administrators out.">
                    <x-settings.maintenance-panel :settings="$settings['maintenance']" />
                </x-settings.setting-card>
                <x-settings.save-bar group="maintenance" />
            </form>
        @elseif ($activeGroup === 'legal')
            <form method="POST" action="{{ route('admin.settings.legal.update') }}" novalidate>
                @csrf
                @method('PUT')
                <x-settings.setting-card title="Legal" description="References only. Legal page content remains owned by Page Studio.">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($legalPages as $key => $page)
                            <x-settings.legal-page-picker :name="$key.'_page_id'" :page="$page" />
                        @endforeach
                    </div>
                </x-settings.setting-card>
                <x-settings.save-bar group="legal" />
            </form>
        @else
            <section aria-labelledby="system-readiness-title">
                <div class="mb-4"><h2 id="system-readiness-title" class="text-lg font-extrabold text-stone-950">System readiness</h2><p class="mt-1 text-sm text-stone-600">Operational visibility without exposing secrets or editable infrastructure credentials.</p></div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($systemStatuses as $status)
                        <x-settings.system-status-card :status="$status" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($activeGroup !== 'system')
            <form method="POST" action="{{ route('admin.settings.reset', $activeGroup) }}" class="mt-6 flex justify-end" x-on:submit="if (! confirm('Reset this settings group to its safe defaults?')) { $event.preventDefault() }">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-red-200 bg-white px-4 text-sm font-bold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-700/15">Reset group to defaults</button>
            </form>
        @endif
    </x-settings.settings-layout>
</x-admin.layout>
