@props(['canManage' => false, 'hasDomains' => false])

<div class="grid min-h-64 place-items-center px-6 py-10 text-center">
    <div class="max-w-md">
        <span class="mx-auto grid size-12 place-items-center rounded-lg bg-stone-100 text-stone-700">
            <i data-lucide="server-cog" class="size-6" aria-hidden="true"></i>
        </span>
        <h2 class="mt-4 text-base font-extrabold text-stone-950">No inbound connections yet</h2>
        <p class="mt-2 text-sm leading-6 text-stone-600">
            {{ $hasDomains ? 'Connect a receiving domain to an IMAP-compatible provider and verify its readiness.' : 'Create a receiving domain first, then return here to configure inbound mail access.' }}
        </p>
        @if ($canManage)
            <a href="{{ $hasDomains ? route('admin.imap-smtp.create') : route('admin.domains.create') }}" class="mt-5 inline-flex min-h-10 items-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                {{ $hasDomains ? 'Add inbound connection' : 'Create domain' }}
            </a>
        @endif
    </div>
</div>
