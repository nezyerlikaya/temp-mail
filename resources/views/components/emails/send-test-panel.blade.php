@props(['template', 'deliverability', 'canSend' => false])

<x-admin.card title="Send test email" description="Owner/admin only. Test messages are marked with a TEST subject prefix.">
    <form method="POST" action="{{ route('admin.email-templates.send-test', $template) }}" class="space-y-3" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting.toString()">
        @csrf
        <label class="block text-sm font-bold text-stone-700">
            <span>Recipient email</span>
            <input name="recipient" value="{{ old('recipient') }}" inputmode="email" autocomplete="email" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('recipient') aria-invalid="true" aria-describedby="email-test-recipient-error" @enderror>
            @error('recipient') <span id="email-test-recipient-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
        </label>
        <button type="submit" @disabled(! $canSend || ! $deliverability['ready']) x-bind:disabled="submitting || {{ ($canSend && $deliverability['ready']) ? 'false' : 'true' }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
            <span x-text="submitting ? 'Sending...' : 'Send test'"></span>
        </button>
    </form>
</x-admin.card>
