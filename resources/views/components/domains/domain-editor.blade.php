@props(['domain' => null, 'statuses', 'canUpdate' => true])

@php
    $action = $domain ? route('admin.domains.update', $domain) : route('admin.domains.store');
@endphp

<x-admin.card :title="$domain ? 'Domain settings' : 'Create domain'" description="Manage receiving domain availability and DNS readiness metadata.">
    <form method="POST" action="{{ $action }}" class="space-y-5" x-data="{ submitting: false }" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
        @csrf
        @if ($domain)
            @method('PUT')
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Domain name</span>
                <input name="domain_name" value="{{ old('domain_name', $domain?->domain_name) }}" placeholder="example.com" autocomplete="off" aria-invalid="{{ $errors->has('domain_name') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('domain_name') ? 'domain-name-error' : '' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('domain_name')
                    <span id="domain-name-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
                @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Display name</span>
                <input name="display_name" value="{{ old('display_name', $domain?->display_name) }}" aria-invalid="{{ $errors->has('display_name') ? 'true' : 'false' }}" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @error('display_name')
                    <span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
                @enderror
            </label>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Status</span>
                <select name="status" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $domain?->status ?? 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Sort order</span>
                <input type="number" min="1" max="9999" name="sort_order" value="{{ old('sort_order', $domain?->sort_order ?? 100) }}" class="no-spinner min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            </label>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ([['is_active', 'Active'], ['is_public', 'Public availability'], ['catch_all_ready', 'Catch-all readiness'], ['is_default', 'Default domain']] as [$field, $label])
                <label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $domain?->{$field} ?? false)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div class="flex items-center justify-end border-t border-stone-200 pt-5">
            <button @disabled(! $canUpdate) x-bind:disabled="submitting || {{ $canUpdate ? 'false' : 'true' }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400">
                <span x-show="!submitting">{{ $domain ? 'Save domain' : 'Create domain' }}</span>
                <span x-cloak x-show="submitting">Saving...</span>
            </button>
        </div>
    </form>
</x-admin.card>
