@props(['plan', 'impact', 'canUpdate' => false, 'canUpdateLimits' => false, 'canToggle' => false])
<section class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
    <header class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 px-5 py-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-extrabold text-stone-950">{{ $plan->name }}</h2>
                <x-billing.plan-status-badge :active="$plan->is_active" />
                <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-700">{{ strtoupper($plan->billing_provider) }}</span>
            </div>
            <p class="mt-1 text-sm text-stone-600">{{ $plan->description }}</p>
        </div>
        <form method="POST" action="{{ route('admin.plans-memberships.status', $plan) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="state" value="{{ $plan->is_active ? 'inactive' : 'active' }}">
            <button type="submit" @disabled(! $canToggle || $plan->key === 'free') x-bind:disabled="submitting || {{ (! $canToggle || $plan->key === 'free') ? 'true' : 'false' }}" class="inline-flex min-h-10 rounded-md border border-stone-300 bg-white px-3 text-sm font-extrabold text-stone-800 hover:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:text-stone-400">
                {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
    </header>
    <div class="grid gap-6 p-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <x-billing.plan-editor :plan="$plan" :can-update="$canUpdate" />
            <form method="POST" action="{{ route('admin.plans-memberships.limits', $plan) }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }">
                @csrf
                @method('PUT')
                <h3 class="text-base font-extrabold text-stone-950">Temp Mail limits</h3>
                <x-billing.limit-row :plan-id="$plan->id" name="maximum_active_inboxes" label="Maximum active inboxes" description="Future enforcement capacity for concurrent inboxes." :value="$plan->limits->maximum_active_inboxes" suffix="inboxes" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" name="inbox_lifetime_minutes" label="Inbox lifetime" description="Default mailbox lifetime for this plan." :value="$plan->limits->inbox_lifetime_minutes" suffix="minutes" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" name="maximum_messages_per_inbox" label="Messages per inbox" description="Maximum stored messages for future retention enforcement." :value="$plan->limits->maximum_messages_per_inbox" suffix="messages" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" name="maximum_message_size_kb" label="Maximum message size" description="Inbound message size readiness." :value="$plan->limits->maximum_message_size_kb" suffix="KB" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" name="api_request_limit" label="API request limit readiness" description="Stored for future API Access enforcement." :value="$plan->limits->api_request_limit" suffix="requests" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" type="checkbox" name="custom_alias_allowed" label="Custom alias" description="Allow users to choose inbox aliases in future public flows." :value="$plan->limits->custom_alias_allowed" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" type="checkbox" name="custom_domain_allowed" label="Custom domain" description="Readiness flag for future customer-owned receiving domains." :value="$plan->limits->custom_domain_allowed" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" type="checkbox" name="api_access_allowed" label="API access" description="Readiness flag only; no API keys are issued here." :value="$plan->limits->api_access_allowed" :can-update="$canUpdateLimits" />
                <x-billing.limit-row :plan-id="$plan->id" type="checkbox" name="ads_enabled" label="Ads enabled" description="Public UI readiness flag for ad-supported plans." :value="$plan->limits->ads_enabled" :can-update="$canUpdateLimits" />
                <x-billing.save-bar :can-update="$canUpdateLimits" label="Save limits" />
            </form>
        </div>
        <x-billing.limit-impact-preview :impact="$impact" />
    </div>
</section>
