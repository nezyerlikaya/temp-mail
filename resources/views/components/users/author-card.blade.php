@props(['profileUser', 'summary', 'avatar', 'editUrl' => null])

<article {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-start gap-4">
        <x-users.avatar-preview :avatar="$avatar" />
        <div class="min-w-0 flex-1">
            <p class="mb-1 text-xs font-bold uppercase text-teal-800">Author Profile</p>
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-base font-extrabold text-stone-950">{{ $summary['attribution_name'] }}</h3>
                <x-users.status-badge :status="$summary['public_state'] === 'active' ? 'active' : 'hidden'" />
                @if ($profileUser->featured_author)
                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900 ring-1 ring-inset ring-amber-200">Featured</span>
                @endif
            </div>
            <p class="mt-1 truncate text-sm text-stone-500">{{ $profileUser->public_author_slug ? '/authors/'.$profileUser->public_author_slug : 'Public slug not set' }}</p>
            <p class="mt-3 line-clamp-3 text-sm leading-6 text-stone-600">{{ $profileUser->author_bio ?: 'Author biography is not ready yet.' }}</p>
        </div>
    </div>
    <div class="mt-5 flex items-center justify-between gap-4 border-t border-stone-200 pt-4">
        <div class="text-sm">
            <span class="font-extrabold text-stone-950">{{ $summary['completion'] }}%</span>
            <span class="text-stone-500"> ready</span>
        </div>
        @if ($editUrl)
            <a href="{{ $editUrl }}" class="inline-flex min-h-9 items-center justify-center rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Edit profile</a>
        @endif
    </div>
</article>
