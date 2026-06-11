@props(['asset', 'lifecycle', 'canDelete' => false])

<div class="space-y-4">
    <div class="rounded-lg border {{ $lifecycle['in_use'] ? 'border-red-200 bg-red-50' : 'border-stone-200 bg-stone-50' }} p-4">
        <div class="flex gap-3">
            <i data-lucide="triangle-alert" class="mt-0.5 size-5 shrink-0 {{ $lifecycle['in_use'] ? 'text-red-700' : 'text-stone-600' }}" aria-hidden="true"></i>
            <div>
                <p class="text-sm font-extrabold {{ $lifecycle['in_use'] ? 'text-red-950' : 'text-stone-950' }}">
                    {{ $lifecycle['in_use'] ? 'This asset is still in use' : 'Permanent deletion cannot be undone' }}
                </p>
                <p class="mt-1 text-sm {{ $lifecycle['in_use'] ? 'text-red-800' : 'text-stone-600' }}">
                    @if ($lifecycle['in_use'])
                        {{ $lifecycle['usage_count'] }} tracked reference(s) will be removed with this file.
                    @else
                        The database record and stored file will both be removed.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div>
        <label for="delete-confirmation-{{ $asset->id }}" class="text-sm font-extrabold text-stone-950">Type DELETE to confirm</label>
        <input
            id="delete-confirmation-{{ $asset->id }}"
            name="delete_confirmation"
            autocomplete="off"
            required
            pattern="DELETE"
            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-600/20"
            @error('delete_confirmation') aria-invalid="true" aria-describedby="delete-confirmation-error-{{ $asset->id }}" @enderror
            @disabled(! $canDelete)
        >
        @error('delete_confirmation')
            <p id="delete-confirmation-error-{{ $asset->id }}" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    @if ($lifecycle['in_use'])
        <label class="flex items-start gap-3 rounded-lg border border-red-200 p-3 text-sm text-red-950">
            <input name="confirm_in_use_delete" type="checkbox" value="1" required class="mt-0.5 size-4 rounded border-red-300 text-red-700 focus:ring-red-600">
            <span>I understand that tracked references to this media asset will be removed.</span>
        </label>
    @endif
</div>
