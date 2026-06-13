<x-auth.layout title="Report abuse">
    <header class="mb-7">
        <p class="text-sm font-bold uppercase text-teal-700">Trust and safety</p>
        <h1 class="mt-2 text-3xl font-bold text-stone-950">Report abuse</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">Submit a human-reviewed complaint. Do not include passwords, full mailbox message bodies, or other unnecessary private data.</p>
    </header>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900" role="status">{{ session('status') }}</div>
    @endif

    <x-error-summary />

    <form method="POST" action="{{ route('abuse-report.store') }}" class="space-y-5" x-data="{ busy: false }" x-on:submit="if (busy) $event.preventDefault(); busy = true" x-bind:aria-busy="busy.toString()">
        @csrf
        <div>
            <label for="report-type" class="block text-sm font-bold text-stone-900">Report type</label>
            <select id="report-type" name="report_type" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('report_type') ? 'true' : 'false' }}" required>
                @foreach (['phishing' => 'Phishing', 'spam' => 'Spam', 'malware' => 'Malware readiness', 'impersonation' => 'Impersonation', 'illegal_content' => 'Illegal content', 'privacy_violation' => 'Privacy violation', 'copyright_dmca' => 'Copyright / DMCA', 'abusive_mailbox' => 'Abusive mailbox', 'abusive_domain' => 'Abusive domain', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('report_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('report_type')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
        </div>
        <x-form.input name="reporter_name" label="Your name" :value="old('reporter_name')" autocomplete="name" />
        <x-form.input name="reporter_email" label="Your email" type="email" :value="old('reporter_email')" autocomplete="email" inputmode="email" />
        <x-form.input name="subject" label="Subject" :value="old('subject')" autocomplete="off" />
        <x-form.input name="related_url" label="Related URL" type="url" :value="old('related_url')" autocomplete="url" inputmode="url" help="Optional. Use a public URL only." />
        <div>
            <label for="abuse-description" class="block text-sm font-bold text-stone-900">Description</label>
            <textarea id="abuse-description" name="description" rows="7" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="abuse-description-help abuse-description-error" required>{{ old('description') }}</textarea>
            <p id="abuse-description-help" class="mt-2 text-sm text-stone-600">Explain what happened without pasting raw private mailbox content.</p>
            @error('description')<p id="abuse-description-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="inline-flex w-full min-h-12 items-center justify-center rounded-lg bg-teal-700 px-5 text-sm font-bold text-white focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:opacity-60" x-bind:disabled="busy">
            <span x-show="!busy">Submit report</span><span x-show="busy" x-cloak>Submitting...</span>
        </button>
    </form>
</x-auth.layout>
