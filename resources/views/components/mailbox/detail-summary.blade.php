@props(['mailbox'])
<x-admin.card title="Mailbox summary" description="Operational metadata only. Message content is intentionally excluded from this foundation.">
    <div class="flex flex-wrap items-center gap-2"><x-mailbox.status-badge :status="$mailbox->status" /><x-mailbox.type-badge :type="$mailbox->mailbox_type" /></div>
    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
        @foreach([['Address',$mailbox->address],['Domain',$mailbox->domain->domain_name],['Owner',$mailbox->user?->email ?? 'Guest'],['Locale',$mailbox->locale?->locale ?? 'Not assigned'],['Messages',$mailbox->message_count],['Expires',$mailbox->expires_at?->toDayDateTimeString() ?? 'No expiry assigned'],['Last activity',$mailbox->last_activity_at?->toDayDateTimeString() ?? 'No activity'],['Created',$mailbox->created_at->toDayDateTimeString()]] as [$label,$value])
            <div class="border-b border-stone-200 pb-3"><dt class="text-xs font-bold text-stone-500">{{ $label }}</dt><dd class="mt-1 break-all text-sm font-extrabold text-stone-900">{{ $value }}</dd></div>
        @endforeach
    </dl>
</x-admin.card>
