@props(['roles', 'statuses'])

<form method="GET" action="{{ route('admin.people-identity.index') }}" class="grid gap-3 lg:grid-cols-3 xl:grid-cols-[minmax(180px,1.4fr)_minmax(120px,.75fr)_minmax(120px,.75fr)_minmax(130px,.9fr)_minmax(130px,.9fr)_auto]" aria-label="Filter people">
    <div class="min-w-0">
        <label for="people-search" class="sr-only">Search name or email</label>
        <input id="people-search" name="search" type="search" value="{{ request('search') }}" placeholder="Search name, email, username..." class="block h-10 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
    </div>
    <div class="min-w-0">
        <label for="people-role" class="sr-only">Role</label>
        <select id="people-role" name="role" class="block h-10 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
            <option value="">All roles</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-0">
        <label for="people-status" class="sr-only">Status</label>
        <select id="people-status" name="status" class="block h-10 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
            <option value="">All statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-0">
        <label for="people-created-from" class="sr-only">Created from</label>
        <input id="people-created-from" name="created_from" type="date" value="{{ request('created_from') }}" class="block h-10 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
    </div>
    <div class="min-w-0">
        <label for="people-created-to" class="sr-only">Created to</label>
        <input id="people-created-to" name="created_to" type="date" value="{{ request('created_to') }}" class="block h-10 w-full rounded-md border border-stone-300 bg-white px-3 text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
    </div>
    <div class="flex min-w-0 gap-2 lg:justify-end xl:justify-start">
        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-teal-700 px-4 text-sm font-bold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25">Filter</button>
        <a href="{{ route('admin.people-identity.index') }}" class="grid size-10 place-items-center rounded-md border border-stone-300 text-stone-600 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20" aria-label="Clear filters">
            <i data-lucide="rotate-ccw" class="size-4" aria-hidden="true"></i>
        </a>
    </div>
</form>
