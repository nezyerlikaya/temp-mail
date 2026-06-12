@props(['plans', 'users', 'canGrant' => false])
<x-admin.card title="Grant membership" description="Use one-month Premium preset or custom dates. This does not change user roles.">
    <form method="POST" action="{{ route('admin.plans-memberships.memberships.grant') }}" class="space-y-4" x-data="{ preset: @js(old('preset', 'one_month')), submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()">
        @csrf
        <div class="grid gap-4 lg:grid-cols-4">
            <label class="grid gap-2 text-sm font-extrabold text-stone-950">User<select name="user_id" @disabled(! $canGrant) class="min-h-11 rounded-md border border-stone-300 bg-white px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"><option value="">Select user</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->email }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-extrabold text-stone-950">Plan<select name="plan_id" @disabled(! $canGrant) class="min-h-11 rounded-md border border-stone-300 bg-white px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-extrabold text-stone-950">Preset<select name="preset" x-model="preset" @disabled(! $canGrant) class="min-h-11 rounded-md border border-stone-300 bg-white px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"><option value="one_month">One-month Premium preset</option><option value="custom">Custom dates</option></select></label>
            <label class="grid gap-2 text-sm font-extrabold text-stone-950">Grace period<input name="grace_period_days" type="number" min="0" max="3" value="{{ old('grace_period_days', 0) }}" @disabled(! $canGrant) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"><span class="text-xs text-stone-500">0-3 days, disabled by default.</span></label>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2 text-sm font-extrabold text-stone-950">Starts at<input name="starts_at" type="datetime-local" value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" @disabled(! $canGrant) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"></label>
            <label class="grid gap-2 text-sm font-extrabold text-stone-950" x-show="preset === 'custom'">Ends at<input name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" @disabled(! $canGrant) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"></label>
        </div>
        <x-billing.grant-note-field />
        <x-billing.save-bar :can-update="$canGrant" label="Grant membership" />
    </form>
</x-admin.card>
