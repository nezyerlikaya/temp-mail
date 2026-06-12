@props(['section', 'statuses'])

<form method="POST" action="{{ route('admin.sections-studio.items.store', $section) }}" class="space-y-4 rounded-lg border border-stone-200 bg-white p-4 shadow-sm" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()">
    @csrf
    <div>
        <label for="faq-new-question" class="text-sm font-extrabold text-stone-800">Question</label>
        <input id="faq-new-question" name="title" value="{{ old('title') }}" type="text" autocomplete="off" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('title') aria-invalid="true" aria-describedby="faq-new-question-error" @enderror>
        @error('title') <p id="faq-new-question-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="faq-new-answer" class="text-sm font-extrabold text-stone-800">Answer</label>
        <textarea id="faq-new-answer" name="content" rows="5" class="mt-2 block w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('content') aria-invalid="true" aria-describedby="faq-new-answer-error" @enderror>{{ old('content') }}</textarea>
        @error('content') <p id="faq-new-answer-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-sections.item-status-toggle id="faq-new-status" status="active" />
        <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
            <span x-text="submitting ? 'Adding...' : 'Add FAQ item'"></span>
        </button>
    </div>
</form>
