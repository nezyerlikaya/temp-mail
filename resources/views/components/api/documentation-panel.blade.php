@props(['documentation'])

<x-admin.card title="API documentation" description="Focused Temp Mail API reference for the current MVP. Examples use placeholders only.">
    <div class="space-y-6">
        <section>
            <h3 class="text-sm font-extrabold text-stone-950">Authentication</h3>
            <p class="mt-1 text-sm leading-6 text-stone-600">{{ $documentation['authentication'] }}</p>
        </section>

        <section>
            <h3 class="text-sm font-extrabold text-stone-950">Rate limits</h3>
            <p class="mt-1 text-sm leading-6 text-stone-600">{{ $documentation['rate_limits'] }}</p>
        </section>

        <section class="grid gap-3 md:grid-cols-2">
            @foreach($documentation['environments'] as $environment => $description)
                <div class="rounded-lg border border-stone-200 p-3">
                    <x-api.environment-badge :environment="$environment" />
                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ $description }}</p>
                </div>
            @endforeach
        </section>

        <section>
            <h3 class="text-sm font-extrabold text-stone-950">Endpoints</h3>
            <div class="mt-3 overflow-hidden rounded-lg border border-stone-200">
                @foreach($documentation['endpoints'] as $endpoint)
                    <div class="grid gap-2 border-b border-stone-200 p-3 text-sm last:border-b-0 md:grid-cols-[80px_minmax(0,1fr)_160px]">
                        <span class="font-extrabold text-stone-950">{{ $endpoint['method'] }}</span>
                        <span class="break-all font-bold text-stone-700">{{ $endpoint['path'] }}</span>
                        <span class="font-bold text-teal-800">{{ $endpoint['scope'] }}</span>
                        <p class="text-stone-600 md:col-span-3">{{ $endpoint['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="grid gap-3 md:grid-cols-2">
            <div>
                <h3 class="text-sm font-extrabold text-stone-950">Success format</h3>
                <pre class="mt-2 overflow-x-auto rounded-lg border border-stone-200 bg-stone-50 p-3 text-xs font-bold leading-6 text-stone-800"><code>{{ json_encode($documentation['response_format'], JSON_PRETTY_PRINT) }}</code></pre>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-stone-950">Error format</h3>
                <pre class="mt-2 overflow-x-auto rounded-lg border border-stone-200 bg-stone-50 p-3 text-xs font-bold leading-6 text-stone-800"><code>{{ json_encode($documentation['error_format'], JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </section>

        <section class="grid gap-3 md:grid-cols-2">
            @foreach($documentation['examples'] as $label => $example)
                <div>
                    <h3 class="text-sm font-extrabold text-stone-950">{{ str($label)->replace('_', ' ')->headline() }}</h3>
                    <pre class="mt-2 overflow-x-auto rounded-lg border border-stone-200 bg-stone-950 p-3 text-xs font-bold leading-6 text-white"><code>{{ $example }}</code></pre>
                </div>
            @endforeach
        </section>
    </div>
</x-admin.card>
