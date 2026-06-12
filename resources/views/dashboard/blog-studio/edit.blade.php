<x-admin.layout :title="$post->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Blog Studio"
        :title="$post->title"
        description="Refine content, media, taxonomy readiness, and publishing state for this language-specific post."
    >
        <x-slot:actions>
            <x-blog.status-badge :status="$post->status" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-blog.validation-summary />

    <x-blog.post-editor :post="$post" :editor="$editor" :action="route('admin.blog-studio.update', $post)" method="PUT" />

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-blog.public-url-panel :post="$post" :preview="$preview" />
            <x-blog.lifecycle-actions
                :post="$post"
                :can-publish="$editor['canPublish']"
                :can-hide="$editor['canHide']"
                :can-trash="$editor['canTrash']"
                :can-restore="$editor['canRestore']"
            />
        </div>

        <div class="min-w-0 space-y-6">
            <x-admin.card title="Ownership readiness" description="Prepared for future author deletion and suspension transfer flows.">
                <div class="space-y-3 text-sm">
                    <p class="font-bold text-stone-700">{{ $ownership['message'] }}</p>
                    <dl>
                        <dt class="text-xs font-bold uppercase text-stone-500">Current author</dt>
                        <dd class="mt-1 font-extrabold text-stone-950">{{ $post->author?->name ?? 'System' }}</dd>
                    </dl>
                </div>
            </x-admin.card>

            <x-blog.delete-warning :post="$post" :can-delete="$editor['canDelete']" />
        </div>
    </div>
</x-admin.layout>
