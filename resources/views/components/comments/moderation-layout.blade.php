@props(['user'])

<x-admin.layout title="Comment Moderation" :user="$user">
    {{ $slot }}
</x-admin.layout>
