@props(['section', 'items', 'quality', 'canReorder' => false])

@php
    $itemData = $items->map(fn ($item): array => [
        'id' => $item->id,
        'title' => $item->title,
        'content' => $item->content,
        'status' => $item->status,
        'updateUrl' => route('admin.sections-studio.items.update', [$section, $item]),
        'deleteUrl' => route('admin.sections-studio.items.destroy', [$section, $item]),
    ])->values();
@endphp

<div class="space-y-4" x-data="{
    items: {{ Illuminate\Support\Js::from($itemData) }},
    draggedId: null,
    openId: null,
    moveBefore(targetId) {
        if (!this.draggedId || this.draggedId === targetId) return;
        const from = this.items.findIndex(item => item.id === this.draggedId);
        const to = this.items.findIndex(item => item.id === targetId);
        const [moved] = this.items.splice(from, 1);
        this.items.splice(to, 0, moved);
    }
}">
    <x-sections.faq-quality-indicator :quality="$quality" />

    @if ($items->isEmpty())
        <div class="rounded-lg border border-dashed border-stone-300 bg-stone-50 p-6 text-center text-sm font-bold text-stone-600">
            Add the first FAQ question below.
        </div>
    @else
        <form method="POST" action="{{ route('admin.sections-studio.items.reorder', $section) }}" class="space-y-3">
            @csrf
            <template x-for="item in items" :key="item.id">
                <div
                    draggable="{{ $canReorder ? 'true' : 'false' }}"
                    x-on:dragstart="draggedId = item.id"
                    x-on:dragover.prevent
                    x-on:drop.prevent="moveBefore(item.id)"
                    class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm"
                >
                    <input type="hidden" name="order[]" x-bind:value="item.id">
                    <div class="flex items-start gap-3">
                        @if ($canReorder)
                            <x-sections.drag-handle />
                        @endif
                        <button type="button" class="min-w-0 flex-1 text-left focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="openId = openId === item.id ? null : item.id" x-bind:aria-expanded="(openId === item.id).toString()">
                            <span class="block font-extrabold text-stone-950" x-text="item.title"></span>
                            <span class="mt-1 block text-xs font-bold text-stone-500" x-text="item.status === 'active' ? 'Active item' : 'Inactive item'"></span>
                        </button>
                    </div>

                    <div x-cloak x-show="openId === item.id" class="mt-4 border-t border-stone-200 pt-4">
                        <p class="whitespace-pre-line text-sm leading-6 text-stone-600" x-text="item.content"></p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <a x-bind:href="'#faq-item-' + item.id" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Edit below</a>
                        </div>
                    </div>
                </div>
            </template>

            @if ($canReorder)
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Save item order</button>
            @endif
        </form>

        <div class="space-y-4">
            @foreach ($items as $item)
                <div id="faq-item-{{ $item->id }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                    <form method="POST" action="{{ route('admin.sections-studio.items.update', [$section, $item]) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="faq-question-{{ $item->id }}" class="text-sm font-extrabold text-stone-800">Question</label>
                            <input id="faq-question-{{ $item->id }}" name="title" value="{{ $item->title }}" type="text" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                        </div>
                        <div>
                            <label for="faq-answer-{{ $item->id }}" class="text-sm font-extrabold text-stone-800">Answer</label>
                            <textarea id="faq-answer-{{ $item->id }}" name="content" rows="4" class="mt-2 block w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ $item->content }}</textarea>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <x-sections.item-status-toggle :id="'faq-status-'.$item->id" :status="$item->status" />
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Update item</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.sections-studio.items.destroy', [$section, $item]) }}" class="mt-3" x-data="{ confirmed: false }" x-on:submit="if (! confirmed) $event.preventDefault()">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="confirm_remove" x-bind:value="confirmed ? '1' : ''">
                        <label class="flex items-center gap-2 text-xs font-bold text-red-800">
                            <input type="checkbox" x-model="confirmed" class="rounded border-red-300 text-red-700 focus:ring-red-600/20">
                            Confirm soft remove
                        </label>
                        <button type="submit" x-bind:disabled="! confirmed" class="mt-2 inline-flex min-h-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-xs font-extrabold text-red-800 disabled:opacity-50">Remove item</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
