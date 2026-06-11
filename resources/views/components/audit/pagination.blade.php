@props(['paginator'])

@if ($paginator->hasPages())
    <nav {{ $attributes->merge(['class' => 'mt-5']) }} aria-label="Audit feed pagination">
        {{ $paginator->links() }}
    </nav>
@endif
