@props(['notification'])

@unless ($notification->isArchived())
    <div class="flex flex-wrap gap-2">
        @foreach (['1_hour' => 'Snooze 1 hour', '1_day' => 'Snooze 1 day'] as $duration => $label)
            <form method="POST" action="{{ route('admin.notifications.snooze', $notification) }}">
                @csrf
                <input type="hidden" name="duration" value="{{ $duration }}">
                <button type="submit" class="inline-flex min-h-9 items-center rounded-md border border-stone-300 px-3 text-xs font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    {{ $label }}
                </button>
            </form>
        @endforeach
    </div>
@endunless
