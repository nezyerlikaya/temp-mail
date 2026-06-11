@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900']) }} role="alert" aria-labelledby="page-errors-title">
        <p id="page-errors-title" class="font-extrabold">Please fix the highlighted fields.</p>
        <ul class="mt-3 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
