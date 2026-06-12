@props(['mailbox', 'canExpire' => false, 'canLock' => false, 'canEmpty' => false])
<x-admin.card title="Lifecycle actions" description="Administrative controls are audited and enforced by mailbox state.">
    <div class="space-y-4">
        @if($mailbox->status === 'locked')
            <form method="POST" action="{{ route('admin.mailbox-operations.unlock', $mailbox) }}">@csrf<button type="submit" @disabled(! $canLock) class="min-h-11 w-full rounded-md bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-400">Unlock mailbox</button></form>
        @elseif($mailbox->status === 'active')
            <form method="POST" action="{{ route('admin.mailbox-operations.lock', $mailbox) }}">@csrf<button type="submit" @disabled(! $canLock) class="min-h-11 w-full rounded-md border border-stone-300 bg-white px-4 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:text-stone-400">Lock mailbox</button></form>
        @endif

        @if($mailbox->status !== 'expired')
            <form method="POST" action="{{ route('admin.mailbox-operations.expire', $mailbox) }}" x-data="{ confirmation: '' }" x-on:submit="if (confirmation !== 'EXPIRE') $event.preventDefault()" class="space-y-2">
                @csrf<label class="grid gap-2 text-sm font-extrabold text-stone-900">Type EXPIRE<input name="confirmation" x-model="confirmation" autocomplete="off" class="min-h-10 rounded-md border border-amber-300 px-3 focus:outline-none focus:ring-4 focus:ring-amber-500/20"></label>
                <button type="submit" x-bind:disabled="confirmation !== 'EXPIRE' || {{ $canExpire ? 'false' : 'true' }}" class="min-h-10 w-full rounded-md bg-amber-600 px-3 text-sm font-extrabold text-white disabled:bg-stone-400">Expire now</button>
            </form>
        @endif

        <form method="POST" action="{{ route('admin.mailbox-operations.empty', $mailbox) }}" x-data="{ confirmation: '' }" x-on:submit="if (confirmation !== 'EMPTY') $event.preventDefault()" class="space-y-2 border-t border-stone-200 pt-4">
            @csrf<label class="grid gap-2 text-sm font-extrabold text-stone-900">Type EMPTY<input name="confirmation" x-model="confirmation" autocomplete="off" class="min-h-10 rounded-md border border-red-300 px-3 focus:outline-none focus:ring-4 focus:ring-red-600/20"></label>
            <button type="submit" x-bind:disabled="confirmation !== 'EMPTY' || {{ $canEmpty ? 'false' : 'true' }}" class="min-h-10 w-full rounded-md bg-red-700 px-3 text-sm font-extrabold text-white disabled:bg-stone-400">Empty inbox</button>
        </form>
    </div>
</x-admin.card>
