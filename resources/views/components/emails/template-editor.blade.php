@props(['template', 'editor', 'action', 'method' => 'POST'])

@php
    $selectedKey = old('template_key', $template->template_key ?? array_key_first($editor['templateKeys']));
    $defaultHtmlBody = '<p>Hello {{ user_name }},</p><p>{{ app_name }} has an update for you.</p>';
    $defaultPlainBody = 'Hello {{ user_name }}, {{ app_name }} has an update for you.';
@endphp

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <form method="POST" action="{{ $action }}" class="min-w-0 space-y-6" x-data="{ dirty: false, submitting: false }" x-on:input="dirty = true" x-on:change="dirty = true" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true; dirty = false" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div x-cloak x-show="dirty && ! submitting" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm font-bold text-amber-900" role="status">You have unsaved email template changes.</div>

        <x-admin.card title="Template identity" description="Each language and template key pair is independent.">
            <div class="grid gap-4 md:grid-cols-3">
                <label class="text-sm font-bold text-stone-700">
                    <span>Language</span>
                    <select name="locale_id" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('locale_id') aria-invalid="true" aria-describedby="email-locale-error" @enderror>
                        @foreach ($editor['locales'] as $locale)
                            <option value="{{ $locale->id }}" @selected((string) old('locale_id', $template->locale_id ?? '') === (string) $locale->id)>{{ $locale->language_name }}</option>
                        @endforeach
                    </select>
                    @error('locale_id') <span id="email-locale-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="text-sm font-bold text-stone-700">
                    <span>Template key</span>
                    <select name="template_key" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('template_key') aria-invalid="true" aria-describedby="email-key-error" @enderror>
                        @foreach ($editor['templateKeys'] as $key => $label)
                            <option value="{{ $key }}" @selected($selectedKey === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('template_key') <span id="email-key-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="text-sm font-bold text-stone-700">
                    <span>Status</span>
                    <select name="status" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('status') aria-invalid="true" aria-describedby="email-status-error" @enderror>
                        @foreach ($editor['statuses'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $template->status ?? 'draft') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <span id="email-status-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
            </div>
        </x-admin.card>

        <x-admin.card title="Message content" description="Use safe variables only. HTML is sanitized before storage.">
            <div class="space-y-5">
                <label class="block text-sm font-bold text-stone-700">
                    <span>Subject</span>
                    <input name="subject" value="{{ old('subject', $template->subject ?? '') }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('subject') aria-invalid="true" aria-describedby="email-subject-error" @enderror>
                    @error('subject') <span id="email-subject-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-bold text-stone-700">
                    <span>Preheader</span>
                    <input name="preheader" value="{{ old('preheader', $template->preheader ?? '') }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('preheader') aria-invalid="true" aria-describedby="email-preheader-error" @enderror>
                    @error('preheader') <span id="email-preheader-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-bold text-stone-700">
                    <span>HTML body</span>
                    <textarea name="html_body" rows="14" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-3 font-mono text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('html_body') aria-invalid="true" aria-describedby="email-html-error" @enderror>{{ old('html_body', $template->html_body ?? $defaultHtmlBody) }}</textarea>
                    @error('html_body') <span id="email-html-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="block text-sm font-bold text-stone-700">
                    <span>Plain text body</span>
                    <textarea name="plain_text_body" rows="8" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-3 font-mono text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('plain_text_body') aria-invalid="true" aria-describedby="email-plain-error" @enderror>{{ old('plain_text_body', $template->plain_text_body ?? $defaultPlainBody) }}</textarea>
                    @error('plain_text_body') <span id="email-plain-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                </label>
            </div>
        </x-admin.card>

        <div class="flex flex-wrap gap-3">
            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
                <span x-text="submitting ? 'Saving...' : 'Save email template'"></span>
            </button>
        </div>
    </form>

    <aside class="min-w-0 space-y-6">
        <x-emails.variable-picker :variables="$editor['variables']" />
        <x-emails.required-variable-warning :required="$editor['required']" />
        <x-admin.card title="Controlled layout" description="Header and footer are owned by the central system email layout.">
            <p class="text-sm text-stone-700">This editor stores body content only. Rendering wraps it in the controlled system email layout and escapes variable values.</p>
        </x-admin.card>
    </aside>
</div>
