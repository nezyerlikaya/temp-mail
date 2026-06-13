@props(['reports', 'canViewSensitive'])
<div class="space-y-4">
    @foreach ($reports as $report)<x-abuse.case-card :report="$report" :can-view-sensitive="$canViewSensitive" />@endforeach
</div>
