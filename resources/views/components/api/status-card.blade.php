@props(['settings', 'canManage' => false])

<x-admin.card title="API availability" description="Global switch and plan readiness for API key creation.">
    <div class="space-y-4">
        <div class="rounded-lg border {{ $settings['api_enabled'] ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-stone-50' }} p-4">
            <p class="text-sm font-extrabold text-stone-950">{{ $settings['api_enabled'] ? 'API access active' : 'API access paused' }}</p>
            <p class="mt-1 text-sm leading-6 text-stone-600">Authentication rejects every key while the global switch is paused.</p>
        </div>

        <form method="POST" action="{{ route('admin.api-access.settings.update') }}" class="space-y-3" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('PUT')
            @foreach([
                'api_enabled' => 'Global API active',
                'free_api_enabled' => 'Free plan API',
                'premium_api_enabled' => 'Premium plan API',
                'business_api_enabled' => 'Business plan API',
            ] as $field => $label)
                <label class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 px-3 py-2 text-sm font-bold text-stone-800">
                    <span>{{ $label }}</span>
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings[$field])) @disabled(! $canManage) class="size-5 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                </label>
            @endforeach
            <button type="submit" @disabled(! $canManage) x-bind:disabled="submitting || {{ $canManage ? 'false' : 'true' }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
                <span x-show="!submitting">Save API settings</span>
                <span x-cloak x-show="submitting">Saving...</span>
            </button>
        </form>
    </div>
</x-admin.card>
