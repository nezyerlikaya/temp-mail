@if ($errors->any())
    <x-admin.alert variant="danger" title="Review SEO fields" class="mb-6">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif
