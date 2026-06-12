@props(['value', 'status' => 'passive', 'canUpdate' => false])

<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <div class="flex items-center justify-between gap-3">
        <label for="admin_session_lifetime" class="text-sm font-extrabold text-stone-950">Admin session lifetime</label>
        <x-security.status-badge :status="$status" />
    </div>
    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input id="admin_session_lifetime" type="number" min="15" max="1440" name="admin_session_lifetime" value="{{ old('admin_session_lifetime', $value) }}" @disabled(! $canUpdate) class="no-spinner min-h-11 w-full rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100 sm:max-w-44">
        <p class="text-sm leading-6 text-stone-600">Minutes. Shorter values reduce stale admin access on shared devices.</p>
    </div>
    @error('admin_session_lifetime')
        <p class="mt-2 text-xs font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>
