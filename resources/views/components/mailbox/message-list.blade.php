@props(['mailbox', 'messages', 'canViewContent' => false])
<section aria-labelledby="message-list-title" class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 px-4 py-4 sm:px-5">
        <div><h2 id="message-list-title" class="text-base font-extrabold text-stone-950">Inbox messages</h2><p class="mt-1 text-sm text-stone-600">Safe metadata previews only. Open a message to view private content.</p></div>
        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-extrabold text-stone-700">{{ $messages->total() }} messages</span>
    </div>
    @forelse($messages as $message)
        <x-mailbox.message-row :mailbox="$mailbox" :message="$message" :can-view-content="$canViewContent" />
    @empty
        <x-mailbox.empty-state title="Inbox is empty" description="New inbound messages will appear here after mail delivery is connected." />
    @endforelse
    @if($messages->hasPages())<div class="border-t border-stone-200 p-4"><x-admin.pagination :paginator="$messages" /></div>@endif
</section>
