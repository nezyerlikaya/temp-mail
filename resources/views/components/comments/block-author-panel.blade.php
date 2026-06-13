@props(['comment', 'canManageBlocklist', 'canViewPrivate'])

@if ($canManageBlocklist && $canViewPrivate)
    <div class="rounded-lg border border-red-200 bg-red-50 p-3">
        <p class="text-sm font-extrabold text-red-950">Block author readiness</p>
        <p class="mt-1 text-xs font-semibold text-red-900">Stores only protected hashes, never raw private metadata.</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['email' => 'Block email hash', 'ip' => 'Block IP hash'] as $type => $label)
                <form method="POST" action="{{ route('admin.comment-moderation.block', $comment) }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <button class="inline-flex min-h-9 items-center rounded-lg border border-red-300 bg-white px-3 text-xs font-extrabold text-red-900 focus:outline-none focus:ring-4 focus:ring-red-600/20">{{ $label }}</button>
                </form>
            @endforeach
        </div>
    </div>
@endif
