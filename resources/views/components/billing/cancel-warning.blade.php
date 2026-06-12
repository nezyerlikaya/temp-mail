@props(['membership', 'canCancel' => false])
<div class="grid gap-3 md:grid-cols-2">
    <form method="POST" action="{{ route('admin.plans-memberships.memberships.cancel', $membership) }}" x-data="{ confirmation: '' }" x-on:submit="if (confirmation !== 'CANCEL') $event.preventDefault()" class="grid gap-2">
        @csrf
        <input type="hidden" name="reason" value="Manual cancellation">
        <label class="text-xs font-bold text-stone-600">Type CANCEL<input name="confirmation" x-model="confirmation" @disabled(! $canCancel) class="mt-1 min-h-10 w-full rounded-md border border-red-300 px-3 focus:outline-none focus:ring-4 focus:ring-red-600/20"></label>
        <button x-bind:disabled="confirmation !== 'CANCEL' || {{ $canCancel ? 'false' : 'true' }}" class="min-h-10 rounded-md bg-red-700 px-3 text-sm font-extrabold text-white disabled:bg-stone-400">Cancel</button>
    </form>
    <form method="POST" action="{{ route('admin.plans-memberships.memberships.downgrade', $membership) }}" x-data="{ confirmation: '' }" x-on:submit="if (confirmation !== 'DOWNGRADE') $event.preventDefault()" class="grid gap-2">
        @csrf
        <label class="text-xs font-bold text-stone-600">Type DOWNGRADE<input name="confirmation" x-model="confirmation" @disabled(! $canCancel) class="mt-1 min-h-10 w-full rounded-md border border-amber-300 px-3 focus:outline-none focus:ring-4 focus:ring-amber-600/20"></label>
        <button x-bind:disabled="confirmation !== 'DOWNGRADE' || {{ $canCancel ? 'false' : 'true' }}" class="min-h-10 rounded-md bg-amber-600 px-3 text-sm font-extrabold text-white disabled:bg-stone-400">Downgrade to Free</button>
    </form>
</div>
