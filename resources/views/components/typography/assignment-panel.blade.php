@props(['title', 'description' => null, 'scope', 'scopeKey', 'usageScopes' => [], 'families', 'assignments', 'themes' => collect(), 'selectedTheme' => null, 'activeTheme' => null, 'canManage' => false])

<x-admin.card :title="$title" :description="$description">
    @if ($scope === 'theme' && $themes->isNotEmpty())
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($themes as $theme)
                <a href="{{ route('admin.typography-center.index', ['theme' => $theme['slug']]) }}" class="inline-flex min-h-10 items-center gap-2 rounded-md border px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20 {{ $selectedTheme === $theme['slug'] ? 'border-teal-700 bg-teal-50 text-teal-950' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}">
                    {{ $theme['name'] }}
                    @if ($activeTheme === $theme['slug'])
                        <span class="rounded-full bg-teal-100 px-2 py-0.5 text-xs text-teal-900">Active</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.typography-center.assignments.update') }}" x-data="{ busy: false }" x-on:submit="busy = true" x-bind:class="{ 'pointer-events-none opacity-70': busy }" x-bind:aria-busy="busy">
        @csrf
        @method('PUT')
        <input type="hidden" name="scope" value="{{ $scope }}">
        <input type="hidden" name="scope_key" value="{{ $scopeKey }}">

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($usageScopes as $usage => $label)
                <x-typography.font-stack-field
                    :usage="$usage"
                    :label="$label"
                    :families="$families"
                    :assignment="$assignments->get($scope.'|'.$scopeKey.'|'.$usage)"
                    :disabled="! $canManage"
                />
            @endforeach
        </div>

        <x-typography.save-bar :can-save="$canManage" />
    </form>
</x-admin.card>
