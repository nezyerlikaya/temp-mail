@props(['settings', 'canUpdate' => false, 'canReveal' => false])

<x-admin.card title="Akismet comment spam" description="Akismet configuration belongs here; moderation outcomes stay in Comment Moderation.">
    <form method="POST" action="{{ route('admin.security-defense-center.akismet.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 lg:grid-cols-2">
            <x-security.secret-input name="api_key" label="API key" :masked="$settings['secrets']['api_key'] ?? null" :can-reveal="$canReveal" :reveal-url="route('admin.security-defense-center.secret.reveal', ['akismet', 'api_key'])" />
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Site URL</span>
                <input type="url" name="site_url" value="{{ $settings['site_url'] }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            </label>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Mode</span>
                <select name="mode" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="hold_suspicious" @selected($settings['mode'] === 'hold_suspicious')>Hold suspicious</option>
                    <option value="trash_spam" @selected($settings['mode'] === 'trash_spam')>Trash spam</option>
                    <option value="log_only" @selected($settings['mode'] === 'log_only')>Log only</option>
                </select>
            </label>
            @foreach (['is_active' => 'Active', 'protected_comments' => 'Protected comments', 'contact_form_readiness' => 'Contact form readiness'] as $field => $label)
                <label class="mt-7 inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked($settings[$field]) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <button type="submit" @disabled(! $canUpdate) class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
            Save Akismet
        </button>
    </form>
</x-admin.card>
