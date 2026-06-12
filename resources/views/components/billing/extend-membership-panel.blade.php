@props(['membership', 'canExtend' => false])
<form method="POST" action="{{ route('admin.plans-memberships.memberships.extend', $membership) }}" x-data="{ preset: 'one_month' }" class="grid gap-3 md:grid-cols-[160px_minmax(0,1fr)_120px_auto]">
    @csrf @method('PUT')
    <select name="preset" x-model="preset" @disabled(! $canExtend) class="min-h-10 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-teal-600/20"><option value="one_month">+1 month</option><option value="custom">Custom</option></select>
    <input name="ends_at" type="datetime-local" x-show="preset === 'custom'" @disabled(! $canExtend) class="min-h-10 rounded-md border border-stone-300 px-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-teal-600/20">
    <input name="grace_period_days" type="number" min="0" max="3" value="{{ $membership->grace_period_days }}" @disabled(! $canExtend) class="min-h-10 rounded-md border border-stone-300 px-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-teal-600/20" aria-label="Grace period days">
    <button type="submit" @disabled(! $canExtend) class="min-h-10 rounded-md border border-stone-300 px-3 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:text-stone-400">Extend</button>
</form>
