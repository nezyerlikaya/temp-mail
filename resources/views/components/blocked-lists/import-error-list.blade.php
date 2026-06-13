@props(['errors' => []])
@if ($errors !== [])
    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-950" role="alert">
        <p class="font-extrabold">Import blocked</p>
        <ul class="mt-2 space-y-1">
            @foreach ($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
