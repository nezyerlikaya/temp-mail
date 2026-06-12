<x-admin.layout title="Plans & Memberships" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Growth"
        title="Plans & Memberships"
        description="Manage manual billing plans and product limits without changing admin roles or permissions."
    />

    @if(session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    @if($errors->any())
        <x-admin.alert variant="danger" class="mb-6" role="alert">
            <p class="font-extrabold">Review the plan configuration.</p>
            <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </x-admin.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            @forelse($plans as $plan)
                <x-billing.plan-card
                    :plan="$plan"
                    :impact="$impact[$plan->id]"
                    :can-update="$canUpdate"
                    :can-update-limits="$canUpdateLimits"
                    :can-toggle="$canToggle"
                />
            @empty
                <x-billing.empty-state />
            @endforelse
        </div>
        <aside class="space-y-6">
            <x-admin.card title="MVP billing boundary" description="Manual billing is the only provider enabled in this foundation. Checkout, subscriptions, invoices, taxes, and coupons arrive later." />
            <x-admin.card title="Roles stay separate" description="Premium or Business membership never grants owner, admin, editor, moderator, or author access. Admin permissions remain controlled only by roles." />
        </aside>
    </div>
</x-admin.layout>
