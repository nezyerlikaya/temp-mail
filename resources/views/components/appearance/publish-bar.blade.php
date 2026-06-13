@props(['selectedTheme', 'setting', 'report', 'canPublish' => false])

<x-admin.card title="Publish Appearance" description="Publishing copies the current draft tokens into public runtime values for this theme only.">
    <div class="rounded-md border {{ $report['summary']['publishable'] ? 'border-teal-200 bg-teal-50 text-teal-950' : 'border-red-200 bg-red-50 text-red-950' }} p-3">
        <p class="text-sm font-extrabold">{{ $report['summary']['publishable'] ? 'Ready to publish' : 'Publish blocked' }}</p>
        <p class="mt-1 text-sm leading-6">{{ $report['summary']['critical_failures'] }} critical failures, {{ $report['summary']['warnings'] }} warnings.</p>
    </div>

    <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between gap-3"><dt class="font-bold text-stone-500">Published at</dt><dd class="font-extrabold text-stone-950">{{ $setting->published_at?->format('M j, Y H:i') ?? 'Not published yet' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="font-bold text-stone-500">Published by</dt><dd class="font-extrabold text-stone-950">{{ $setting->published_by ? 'User #'.$setting->published_by : 'None' }}</dd></div>
    </dl>

    <form method="POST" action="{{ route('admin.appearance-studio.publish') }}" class="mt-4" x-data="{ confirmed: false, submitting: false }" x-on:submit="submitting = true">
        @csrf
        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
        <label class="flex items-start gap-3 rounded-md border border-stone-200 bg-stone-50 p-3">
            <input type="checkbox" name="confirmation" value="1" x-model="confirmed" class="mt-1 size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-700/20">
            <span class="text-sm font-semibold leading-6 text-stone-700">Publish current {{ str($selectedTheme)->headline() }} draft tokens to public visitors.</span>
        </label>
        <button type="submit" x-bind:disabled="!confirmed || submitting || {{ $canPublish && $report['summary']['publishable'] ? 'false' : 'true' }}" class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:cursor-not-allowed disabled:bg-stone-400">
            <i data-lucide="send" class="size-4" aria-hidden="true"></i>
            <span x-text="submitting ? 'Publishing...' : 'Publish tokens'"></span>
        </button>
    </form>
</x-admin.card>
