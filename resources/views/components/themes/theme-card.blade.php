@props(['theme', 'canActivate' => false, 'activationLocked' => false])

<article class="flex min-h-[420px] flex-col rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-extrabold uppercase text-stone-500">Public Theme</p>
            <h2 class="mt-1 text-xl font-extrabold text-stone-950">{{ $theme['name'] }}</h2>
        </div>
        <x-themes.status-badge :status="$theme['status']" />
    </div>

    <p class="mt-4 min-h-16 text-sm leading-6 text-stone-600">{{ $theme['description'] }}</p>

    <div class="mt-5 grid gap-3">
        <x-themes.readiness-pill label="Version readiness" :value="$theme['version'].' · '.$theme['version_readiness']" icon="badge-check" />
        <x-themes.readiness-pill label="Preview readiness" :value="$theme['preview_readiness']" icon="monitor-check" />
        <x-themes.readiness-pill label="Last activated" :value="$theme['last_activated_at'] ? $theme['last_activated_at']->format('M j, Y H:i') : 'Not activated yet'" icon="clock-3" />
    </div>

    <div class="mt-auto pt-5">
        @if ($theme['is_active'])
            <div class="rounded-md border border-teal-200 bg-teal-50 p-3 text-sm font-bold text-teal-900" role="status">
                Active public theme. Activate another theme to replace it safely.
            </div>
        @elseif (! $canActivate)
            <div class="rounded-md border border-stone-200 bg-stone-50 p-3 text-sm font-bold text-stone-600">
                You can review this theme, but activation is owner/admin only.
            </div>
        @else
            <form
                method="POST"
                action="{{ route('admin.theme-launch-center.activate') }}"
                x-data="{ confirmed: false, submitting: false }"
                x-on:submit="submitting = true"
                x-bind:aria-busy="submitting.toString()"
                x-bind:class="submitting ? 'pointer-events-none opacity-75' : ''"
                novalidate
            >
                @csrf
                <input type="hidden" name="theme" value="{{ $theme['slug'] }}">

                <label class="flex items-start gap-3 rounded-md border border-stone-200 bg-stone-50 p-3">
                    <input
                        type="checkbox"
                        name="confirmation"
                        value="1"
                        x-model="confirmed"
                        class="mt-1 size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-700/20"
                        aria-describedby="confirm-{{ $theme['slug'] }}"
                    >
                    <span id="confirm-{{ $theme['slug'] }}" class="text-sm font-semibold leading-6 text-stone-700">I understand this will switch the public website to {{ $theme['name'] }}.</span>
                </label>

                <button
                    type="submit"
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-not-allowed disabled:bg-stone-300"
                    x-bind:disabled="!confirmed || submitting || {{ $activationLocked ? 'true' : 'false' }}"
                >
                    <i data-lucide="rocket" class="size-4" aria-hidden="true"></i>
                    <span x-text="submitting ? 'Activating...' : 'Set Active'"></span>
                </button>
            </form>
        @endif
    </div>
</article>
