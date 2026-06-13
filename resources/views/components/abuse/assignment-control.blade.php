@props(['report', 'administrators', 'canAssign'])
@if ($canAssign)
    <form method="POST" action="{{ route('admin.abuse-reports.assign', $report) }}" class="space-y-3">
        @csrf @method('PUT')
        <label for="case-assignee" class="block text-sm font-bold text-stone-900">Assigned administrator</label>
        <select id="case-assignee" name="assigned_to" class="min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            <option value="">Unassigned</option>
            @foreach ($administrators as $administrator)
                <option value="{{ $administrator->id }}" @selected($report->assigned_to === $administrator->id)>{{ $administrator->name }} ({{ str($administrator->role)->headline() }})</option>
            @endforeach
        </select>
        <button class="inline-flex min-h-10 items-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-stone-500/25">Update assignment</button>
    </form>
@endif
