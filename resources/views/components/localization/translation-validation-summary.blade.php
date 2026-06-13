@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-red-950']) }} role="alert" tabindex="-1">
        <p class="font-extrabold">Please fix the highlighted fields.</p>
        <p class="mt-1 text-sm font-semibold">Translation changes need attention.</p>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
