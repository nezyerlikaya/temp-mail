@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4" role="alert" aria-live="polite">
        <p class="text-sm font-extrabold text-red-800">Please review the highlighted fields.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-bold text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
