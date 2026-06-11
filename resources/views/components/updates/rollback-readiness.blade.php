@props(['readiness'])

<div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-stone-950">Rollback readiness</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">{{ $readiness['message'] }}</p>
        </div>
        <x-updates.status-badge :status="$readiness['status']" />
    </div>

    <form method="POST" action="{{ route('admin.update-center.rollback-readiness') }}" class="mt-5">
        @csrf
        <input type="hidden" name="confirm_readiness" value="1">
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-3 text-sm font-extrabold text-stone-950 shadow-sm transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
            Review rollback readiness
        </button>
    </form>
</div>
