@props(['report', 'notes', 'canAdd'])
<section aria-labelledby="internal-notes-title" class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <h2 id="internal-notes-title" class="text-base font-extrabold text-stone-950">Internal notes</h2>
    <p class="mt-1 text-sm text-stone-600">Visible only to authorized case reviewers and never included in reporter messages.</p>
    @if ($canAdd)
        <form method="POST" action="{{ route('admin.abuse-reports.notes.store', $report) }}" class="mt-4" x-data="{ busy: false }" x-on:submit="if (busy) $event.preventDefault(); busy = true" :aria-busy="busy">
            @csrf
            <label for="case-note" class="text-sm font-bold text-stone-900">Investigation note <span class="text-rose-700">*</span></label>
            <textarea id="case-note" name="body" rows="4" required maxlength="5000" aria-describedby="case-note-help @error('body') case-note-error @enderror" aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('body') }}</textarea>
            <p id="case-note-help" class="mt-1 text-xs text-stone-500">Do not paste passwords, tokens, raw message bodies, or full evidence.</p>
            @error('body')<p id="case-note-error" role="alert" class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
            <button type="submit" :disabled="busy" class="mt-3 inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:opacity-60"><span x-show="!busy">Add note</span><span x-cloak x-show="busy">Adding…</span></button>
        </form>
    @endif
    <div class="mt-5 space-y-3">
        @forelse ($notes as $note)<article class="rounded-lg bg-stone-50 px-4 py-3"><div class="flex justify-between gap-3 text-xs font-semibold text-stone-500"><span>{{ $note->author?->name ?? 'Former administrator' }}</span><time>{{ $note->created_at->format('M j, Y H:i') }}</time></div><p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-800">{{ $note->body }}</p></article>@empty<p class="text-sm text-stone-500">No internal notes yet.</p>@endforelse
    </div>
</section>
