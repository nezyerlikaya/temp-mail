@props(['template', 'canReset' => false])

<x-admin.card title="Reset to default" description="Restores trusted default body content and returns the template to draft.">
    <form method="POST" action="{{ route('admin.email-templates.reset', $template) }}" x-data="{ confirmed: false }" x-on:submit="if (! confirmed) { $event.preventDefault(); confirmed = true; }">
        @csrf
        <input type="hidden" name="confirm_reset" value="1">
        <p class="text-sm text-stone-700">Reset is owner/admin only and is audit logged. Header and footer remain controlled by the central layout.</p>
        <button type="submit" @disabled(! $canReset) class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-4 text-sm font-extrabold text-amber-900 focus:outline-none focus:ring-4 focus:ring-amber-500/20 disabled:opacity-60">
            <span x-text="confirmed ? 'Confirm reset' : 'Reset readiness'"></span>
        </button>
    </form>
</x-admin.card>
