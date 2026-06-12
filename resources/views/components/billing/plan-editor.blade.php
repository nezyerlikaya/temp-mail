@props(['plan', 'canUpdate' => false])
<form method="POST" action="{{ route('admin.plans-memberships.update', $plan) }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }" class="space-y-4">
    @csrf
    @method('PUT')
    <div class="grid gap-4 lg:grid-cols-2">
        <label class="grid gap-2 text-sm font-extrabold text-stone-950">Name<input name="name" value="{{ old('name', $plan->name) }}" @disabled(! $canUpdate) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"></label>
        <x-billing.currency-field :plan="$plan" :can-update="$canUpdate" />
    </div>
    <label class="grid gap-2 text-sm font-extrabold text-stone-950">Description<textarea name="description" rows="2" @disabled(! $canUpdate) class="rounded-md border border-stone-300 px-3 py-2 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">{{ old('description', $plan->description) }}</textarea></label>
    <div class="grid gap-4 lg:grid-cols-4">
        <label class="grid gap-2 text-sm font-extrabold text-stone-950">Monthly price<input name="monthly_price" type="number" step="0.01" min="0" value="{{ old('monthly_price', $plan->monthly_price) }}" @disabled(! $canUpdate) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"></label>
        <label class="grid gap-2 text-sm font-extrabold text-stone-950">Yearly price<input name="yearly_price" type="number" step="0.01" min="0" value="{{ old('yearly_price', $plan->yearly_price) }}" @disabled(! $canUpdate) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"></label>
        <label class="grid gap-2 text-sm font-extrabold text-stone-950">Sort order<input name="sort_order" type="number" min="1" value="{{ old('sort_order', $plan->sort_order) }}" @disabled(! $canUpdate) class="min-h-11 rounded-md border border-stone-300 px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"></label>
        <label class="grid gap-2 text-sm font-extrabold text-stone-950">Billing provider<select name="billing_provider" @disabled(! $canUpdate) class="min-h-11 rounded-md border border-stone-300 bg-white px-3 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"><option value="manual" selected>Manual</option></select></label>
    </div>
    <x-billing.save-bar :can-update="$canUpdate" label="Save plan" />
</form>
