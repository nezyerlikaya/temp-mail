<x-admin.layout title="Appearance Preview" :user="request()->user()">
    <x-admin.page-header
        eyebrow="Brand"
        title="Signed Appearance Preview"
        description="Admin-only draft preview. Public visitors continue receiving published appearance tokens."
    />

    <x-appearance.preview-frame
        :preview="$preview"
        :radius-options="$radiusOptions"
        :shadow-options="$shadowOptions"
        :motion-options="$motionOptions"
        signed-url="#"
        :can-preview="false"
    />
</x-admin.layout>
