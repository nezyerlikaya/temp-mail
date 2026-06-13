@props(['environments' => [], 'activeEnvironment' => 'sandbox', 'activeCategory' => 'all', 'selected' => null])

<form method="GET" action="{{ route('admin.integrations.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <input type="hidden" name="category" value="{{ $activeCategory }}">
    @if ($selected)
        <input type="hidden" name="integration" value="{{ $selected }}">
    @endif
    <label class="grid gap-2 text-sm font-bold text-stone-700">
        <span>Environment</span>
        <select name="environment" onchange="this.form.submit()" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
            @foreach ($environments as $key => $label)
                <option value="{{ $key }}" @selected($activeEnvironment === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</form>
