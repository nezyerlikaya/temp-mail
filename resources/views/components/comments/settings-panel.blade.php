@props(['settings', 'canUpdateSettings'])

@if ($canUpdateSettings)
    <form method="POST" action="{{ route('admin.comment-moderation.settings') }}" class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-base font-extrabold text-stone-950">Comment policies</h2>
                <p class="mt-1 text-sm text-stone-600">Global moderation rules. Akismet keys remain in Security Defense Center.</p>
            </div>
            <button class="inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">Save settings</button>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                'comments_active' => 'Comments active',
                'guest_comments_allowed' => 'Guest comments',
                'approval_required' => 'Approval required',
                'verified_email_required' => 'Verified email readiness',
                'replies_active' => 'Replies active',
                'notify_pending_admins' => 'Notify pending admins',
            ] as $field => $label)
                <label class="flex min-h-11 items-center gap-3 rounded-lg border border-stone-200 px-3 text-sm font-bold text-stone-800">
                    <input type="checkbox" name="{{ $field }}" value="1" class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/25" @checked($settings[$field] ?? false)>
                    {{ $label }}
                </label>
            @endforeach
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-form.input name="auto_close_days" label="Auto-close days" type="number" :value="$settings['auto_close_days']" min="0" max="3650" />
            <x-form.input name="minimum_length" label="Minimum length" type="number" :value="$settings['minimum_length']" min="1" max="1000" />
            <x-form.input name="maximum_length" label="Maximum length" type="number" :value="$settings['maximum_length']" min="10" max="10000" />
            <x-form.input name="maximum_links" label="Maximum links" type="number" :value="$settings['maximum_links']" min="0" max="20" />
        </div>
        <div class="mt-5">
            <label for="comment-blocked-words" class="block text-sm font-bold text-stone-900">Blocked words</label>
            <textarea id="comment-blocked-words" name="blocked_words" rows="3" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ implode("\n", $settings['blocked_words'] ?? []) }}</textarea>
        </div>
    </form>
@endif
