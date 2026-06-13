@props(['filters', 'administrators'])
<form method="GET" action="{{ route('admin.abuse-reports.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <x-form.input name="q" label="Search" :value="$filters['q']" autocomplete="off" />
        @foreach (['report_type' => ['all','phishing','spam','malware','impersonation','illegal_content','privacy_violation','copyright_dmca','abusive_mailbox','abusive_domain','other'], 'status' => ['all','new','reviewing','awaiting_information','resolved','rejected','archived'], 'priority' => ['all','low','normal','high','critical']] as $field => $options)
            <div><label for="abuse-{{ $field }}" class="block text-sm font-bold text-stone-900">{{ str($field)->replace('_', ' ')->headline() }}</label><select id="abuse-{{ $field }}" name="{{ $field }}" class="mt-2 min-h-12 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">@foreach ($options as $option)<option value="{{ $option }}" @selected(($filters[$field] ?? 'all') === $option)>{{ str($option)->replace('_', ' ')->headline() }}</option>@endforeach</select></div>
        @endforeach
        <div><label for="abuse-assignee" class="block text-sm font-bold text-stone-900">Assignee</label><select id="abuse-assignee" name="assigned_to" class="mt-2 min-h-12 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20"><option value="">All</option>@foreach ($administrators as $administrator)<option value="{{ $administrator->id }}" @selected((string) ($filters['assigned_to'] ?? '') === (string) $administrator->id)>{{ $administrator->name }}</option>@endforeach</select></div>
        <x-form.input name="date" label="Date" type="date" :value="$filters['date']" />
    </div>
    <div class="mt-4 flex gap-3"><button class="inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">Apply filters</button><a href="{{ route('admin.abuse-reports.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700">Reset</a></div>
</form>
