@props(['mailbox', 'message', 'canDelete' => false])
<x-admin.card title="Delete message" description="Attachment recovery is not available in this release.">
    <form method="POST" action="{{ route('admin.mailbox-operations.messages.action', [$mailbox, $message]) }}" x-data="{ confirmation: '', submitting: false }" x-on:submit="if (submitting || confirmation !== 'DELETE') { $event.preventDefault(); return; } submitting = true" class="space-y-3">
        @csrf<input type="hidden" name="action" value="delete">
        <label class="grid gap-2 text-sm font-extrabold text-stone-900">Type DELETE to confirm<input name="confirmation" x-model="confirmation" autocomplete="off" class="min-h-11 rounded-md border border-red-300 px-3 focus:outline-none focus:ring-4 focus:ring-red-600/20"></label>
        <button type="submit" x-bind:disabled="confirmation !== 'DELETE' || submitting || {{ $canDelete ? 'false' : 'true' }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-red-700 px-4 text-sm font-extrabold text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-700/20 disabled:cursor-not-allowed disabled:bg-stone-400">Delete message</button>
    </form>
</x-admin.card>
