@props(['events', 'diffs' => []])

<div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">When</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Event</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Actor</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Source</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-extrabold uppercase text-stone-500">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @foreach ($events as $event)
                    <x-audit.feed-item :event="$event" :diff-rows="$diffs[$event->id] ?? []" />
                @endforeach
            </tbody>
        </table>
    </div>
</div>
