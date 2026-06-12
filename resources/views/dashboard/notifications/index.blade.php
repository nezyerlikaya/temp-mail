<x-admin.layout title="Notifications" :user="$adminUser">
    <x-admin.page-header
        eyebrow="System"
        title="Notifications"
        description="Operational inbox for product, security, infrastructure, content, billing, and system events."
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    Mark all read
                </button>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['label' => 'Unread', 'value' => $summary['unread']], ['label' => 'Critical open', 'value' => $summary['critical']], ['label' => 'Archived', 'value' => $summary['archived']], ['label' => 'Total visible', 'value' => $summary['total']]] as $stat)
            <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-extrabold uppercase text-stone-500">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-black text-stone-950">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <x-admin.card class="mb-6" title="Feed filters" description="Focus the inbox without exposing notifications outside each administrator's permissions.">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="grid gap-4 md:grid-cols-4">
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Status</span>
                <select name="status" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    @foreach (['open' => 'Open', 'unread' => 'Unread', 'read' => 'Read', 'archived' => 'Archived'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Severity</span>
                <select name="severity" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    @foreach (['all' => 'All severities', 'info' => 'Info', 'warning' => 'Warning', 'critical' => 'Critical'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['severity'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold text-stone-700">Module</span>
                <select name="module" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    @foreach (['all' => 'All modules', 'content' => 'Content', 'trust' => 'Trust', 'mail-infrastructure' => 'Mail infrastructure', 'system' => 'System', 'billing' => 'Billing'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['module'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end">
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-md border border-stone-300 px-4 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    Apply filters
                </button>
            </div>
        </form>
    </x-admin.card>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="min-w-0 space-y-4">
            <x-notifications.feed :notifications="$notifications" :selected-notification="$selectedNotification" />
            <x-admin.pagination :paginator="$notifications" />
        </div>
        <aside class="min-w-0">
            <x-notifications.detail-panel :notification="$selectedNotification" :action-link="$selectedActionLink" />
        </aside>
    </div>
</x-admin.layout>
