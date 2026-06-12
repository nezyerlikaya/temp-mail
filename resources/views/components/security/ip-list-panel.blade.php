@props(['settings', 'readiness', 'canUpdate' => false])

<x-admin.card title="IP access controls" description="Prepare allowlist, blocklist, and temporary block readiness without exposing session data.">
    <form method="POST" action="{{ route('admin.security-defense-center.ip-access.update') }}" class="space-y-5" x-data="{ submitting: false }" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-75': submitting }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 bg-stone-50 p-4">
            <p class="text-sm font-semibold leading-6 text-stone-600">{{ $readiness['message'] }}</p>
            <x-security.status-badge :status="$readiness['status']" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ([['allowlist', 'Admin IP allowlist'], ['blocklist', 'IP blocklist']] as [$field, $label])
                <label class="grid gap-2">
                    <span class="text-sm font-bold text-stone-700">{{ $label }}</span>
                    <textarea name="{{ $field }}" rows="5" @disabled(! $canUpdate) aria-invalid="{{ $errors->has($field.'.*') ? 'true' : 'false' }}" class="rounded-md border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">{{ old($field, implode("\n", $settings[$field])) }}</textarea>
                    <span class="text-xs font-semibold text-stone-500">One IP per line.</span>
                    @error($field.'.*')
                        <span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
                    @enderror
                </label>
            @endforeach
        </div>

        <label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
            <input type="hidden" name="temporary_block_ready" value="0">
            <input type="checkbox" name="temporary_block_ready" value="1" @checked(old('temporary_block_ready', $settings['temporary_block_ready'])) @disabled(! $canUpdate) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
            <span>Temporary block readiness</span>
        </label>

        <x-security.save-bar label="Save IP controls" :can-submit="$canUpdate">
            IP lists are stored as policy configuration; external firewall integration remains out of scope.
        </x-security.save-bar>
    </form>
</x-admin.card>
