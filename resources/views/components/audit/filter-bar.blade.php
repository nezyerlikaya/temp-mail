@props(['filters', 'options'])

<form method="GET" action="{{ route('admin.activity-audit-logs.index') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_1fr_auto]" novalidate>
    <x-form.input name="actor" label="Actor" :value="$filters['actor'] ?? null" autocomplete="off" placeholder="Name or email" />

    <div>
        <label for="module" class="block text-sm font-bold text-stone-900">Module</label>
        <select id="module" name="module" aria-invalid="{{ $errors->has('module') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('module') ? 'module-error' : '' }}" class="mt-2 block min-h-12 w-full rounded-lg border border-stone-300 bg-white px-3 text-base text-stone-950 focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
            <option value="">All modules</option>
            @foreach ($options['modules'] as $module)
                <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ str($module)->headline() }}</option>
            @endforeach
        </select>
        @error('module')
            <p id="module-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="action" class="block text-sm font-bold text-stone-900">Action</label>
        <select id="action" name="action" aria-invalid="{{ $errors->has('action') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('action') ? 'action-error' : '' }}" class="mt-2 block min-h-12 w-full rounded-lg border border-stone-300 bg-white px-3 text-base text-stone-950 focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
            <option value="">All actions</option>
            @foreach ($options['actions'] as $action)
                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        @error('action')
            <p id="action-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="severity" class="block text-sm font-bold text-stone-900">Severity</label>
        <select id="severity" name="severity" aria-invalid="{{ $errors->has('severity') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('severity') ? 'severity-error' : '' }}" class="mt-2 block min-h-12 w-full rounded-lg border border-stone-300 bg-white px-3 text-base text-stone-950 focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
            <option value="">All severities</option>
            @foreach ($options['severities'] as $severity)
                <option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ str($severity)->headline() }}</option>
            @endforeach
        </select>
        @error('severity')
            <p id="severity-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end gap-2">
        <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20">
            <i data-lucide="filter" class="size-4" aria-hidden="true"></i>
            Filter
        </button>
        <a href="{{ route('admin.activity-audit-logs.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-md border border-stone-300 bg-white px-4 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/15">Reset</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-2">
        <x-form.input name="date_from" label="From" type="date" :value="$filters['date_from'] ?? null" />
        <x-form.input name="date_to" label="To" type="date" :value="$filters['date_to'] ?? null" />
    </div>
</form>
