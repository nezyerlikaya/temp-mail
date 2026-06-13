@props(['comment', 'canApprove'])

@if ($canApprove && $comment->status === 'spam')
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
        <p class="text-sm font-extrabold text-amber-950">False-positive recovery</p>
        <p class="mt-1 text-xs font-semibold text-amber-900">Original decision is retained for audit readiness.</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['pending' => 'Restore to pending', 'approved' => 'Restore approved'] as $status => $label)
                <form method="POST" action="{{ route('admin.comment-moderation.false-positive', $comment) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button class="inline-flex min-h-9 items-center rounded-lg border border-amber-300 bg-white px-3 text-xs font-extrabold text-amber-900 focus:outline-none focus:ring-4 focus:ring-amber-500/20">{{ $label }}</button>
                </form>
            @endforeach
        </div>
    </div>
@endif
