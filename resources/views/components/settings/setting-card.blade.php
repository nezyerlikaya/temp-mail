@props(['title', 'description' => null])

<x-admin.card :title="$title" :description="$description">{{ $slot }}</x-admin.card>
