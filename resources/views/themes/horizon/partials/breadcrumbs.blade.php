@if (! empty($breadcrumbs))
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2 text-sm font-semibold text-stone-500">
            @foreach ($breadcrumbs as $item)
                <li class="flex items-center gap-2">
                    @if (! $loop->first)<span aria-hidden="true">/</span>@endif
                    @if ($item['url'])
                        <a href="{{ $item['url'] }}" class="hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-600/25">{{ $item['label'] }}</a>
                    @else
                        <span aria-current="page">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
