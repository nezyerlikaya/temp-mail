@props(['manualSteps', 'canUpload'])

<div class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="border-b border-stone-200 px-5 py-4">
        <h2 class="text-base font-extrabold text-stone-950">Manual update mode</h2>
        <p class="mt-1 text-sm text-stone-600">Shared-hosting fallback for verifying an uploaded package before manual steps.</p>
    </div>

    <div class="space-y-5 p-5">
        <form
            method="POST"
            action="{{ route('admin.update-center.manual-upload') }}"
            enctype="multipart/form-data"
            x-data="{ submitting: false }"
            x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
            x-bind:aria-busy="submitting"
            x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
            class="space-y-4"
        >
            @csrf
            <div>
                <label for="manual_package" class="text-sm font-extrabold text-stone-950">Update package</label>
                <input id="manual_package" name="package" type="file" accept=".zip,application/zip" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-stone-950 file:px-3 file:py-2 file:text-sm file:font-bold file:text-white focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('package') aria-invalid="true" aria-describedby="manual-package-error" @enderror>
                @error('package')
                    <p id="manual-package-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="expected_checksum" class="text-sm font-extrabold text-stone-950">Expected SHA-256 checksum</label>
                <input id="expected_checksum" name="expected_checksum" value="{{ old('expected_checksum') }}" inputmode="text" autocomplete="off" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 font-mono text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('expected_checksum') aria-invalid="true" aria-describedby="expected-checksum-error" @enderror>
                @error('expected_checksum')
                    <p id="expected-checksum-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="manual_signature" class="text-sm font-extrabold text-stone-950">Signature</label>
                <textarea id="manual_signature" name="signature" rows="3" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 font-mono text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('signature') aria-invalid="true" aria-describedby="manual-signature-error" @enderror>{{ old('signature') }}</textarea>
                @error('signature')
                    <p id="manual-signature-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" x-bind:disabled="submitting || {{ $canUpload ? 'false' : 'true' }}" class="inline-flex w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-70">
                <span x-show="! submitting">Verify manual package</span>
                <span x-cloak x-show="submitting">Verifying package...</span>
            </button>
        </form>

        <ol class="space-y-2 text-sm text-stone-700">
            @foreach ($manualSteps as $step)
                <li class="flex gap-2"><span class="font-extrabold text-stone-950">{{ $loop->iteration }}.</span><span>{{ $step }}</span></li>
            @endforeach
        </ol>
    </div>
</div>
