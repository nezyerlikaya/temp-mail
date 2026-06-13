@props(['apiKey', 'canManage' => false])

@php($status = $apiKey->revoked_at ? 'revoked' : ($apiKey->expires_at?->isPast() ? 'expired' : $apiKey->status))

<article class="border-b border-stone-200 py-4 last:border-b-0">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-sm font-extrabold text-stone-950">{{ $apiKey->name }}</h3>
                <x-api.environment-badge :environment="$apiKey->environment" />
                <x-api.key-status-badge :status="$status" />
            </div>
            <p class="mt-1 text-sm text-stone-600">{{ $apiKey->user->email }} · Prefix {{ $apiKey->key_prefix }}</p>
            <p class="mt-1 text-xs font-bold text-stone-500">Last used {{ $apiKey->last_used_at?->diffForHumans() ?? 'never' }} · Expires {{ $apiKey->expires_at?->toDayDateTimeString() ?? 'never' }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($apiKey->scopes as $scope)
                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-bold text-stone-700">{{ $scope }}</span>
                @endforeach
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <form method="POST" action="{{ route('admin.api-access.keys.regenerate', $apiKey) }}">
                @csrf
                <input type="hidden" name="confirmation" value="REGENERATE">
                <button type="submit" @disabled(! $canManage || $status === 'revoked') class="inline-flex min-h-10 w-full items-center justify-center rounded-lg border border-stone-300 px-3 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-50">Regenerate</button>
            </form>
            <form method="POST" action="{{ route('admin.api-access.keys.revoke', $apiKey) }}">
                @csrf
                <input type="hidden" name="confirmation" value="REVOKE">
                <button type="submit" @disabled(! $canManage || $status === 'revoked') class="inline-flex min-h-10 w-full items-center justify-center rounded-lg border border-red-200 px-3 text-sm font-extrabold text-red-700 focus:outline-none focus:ring-4 focus:ring-red-600/20 disabled:opacity-50">Revoke</button>
            </form>
        </div>
    </div>
</article>
