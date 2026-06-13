@props(['preview', 'radiusOptions', 'shadowOptions', 'motionOptions', 'signedUrl', 'canPreview' => false])

@php
    $varMap = [
        'brand_color' => '--tm-brand-color',
        'accent_color' => '--tm-accent-color',
        'background_color' => '--tm-background-color',
        'surface_color' => '--tm-surface-color',
        'text_color' => '--tm-text-color',
        'muted_text_color' => '--tm-muted-text-color',
        'border_color' => '--tm-border-color',
        'button_radius' => '--tm-button-radius',
        'card_radius' => '--tm-card-radius',
        'shadow_level' => '--tm-shadow',
        'motion_level' => '--tm-motion-duration',
    ];
@endphp

<section
    class="rounded-lg border border-stone-200 bg-white shadow-sm"
    x-data="{
        mode: 'homepage',
        device: 'desktop',
        direction: 'ltr',
        varMap: @js($varMap),
        radius: @js($radiusOptions),
        shadow: @js($shadowOptions),
        motion: @js($motionOptions),
        applyToken(name, value) {
            if (! this.varMap[name] || ! this.$refs.previewSurface) return;
            let next = value;
            if (name === 'button_radius' || name === 'card_radius') next = this.radius[value] || value;
            if (name === 'shadow_level') next = this.shadow[value] || value;
            if (name === 'motion_level') next = this.motion[value] || value;
            this.$refs.previewSurface.style.setProperty(this.varMap[name], next);
        }
    }"
    x-on:appearance-token-changed.window="applyToken($event.detail.name, $event.detail.value)"
>
    <div class="space-y-4 border-b border-stone-200 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-stone-950">Draft Preview</h2>
                <p class="mt-1 text-sm leading-6 text-stone-600">Admin-only preview uses draft tokens and never mutates published public values.</p>
            </div>
            @if ($canPreview)
                <a href="{{ $signedUrl }}" target="_blank" rel="noreferrer" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-700 hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Signed preview</a>
            @endif
        </div>
        <x-appearance.preview-tabs :modes="$preview['modes']" />
        <x-appearance.device-preview-tabs :devices="$preview['devices']" :directions="$preview['directions']" />
    </div>

    <div class="bg-stone-100 p-5">
        <div class="mx-auto transition-all" x-bind:class="device === 'mobile' ? 'max-w-sm' : 'max-w-4xl'">
            <div x-ref="previewSurface" x-bind:dir="direction" class="overflow-hidden border border-stone-300 bg-[var(--tm-background-color)] text-[var(--tm-text-color)] shadow-sm" style="{{ $preview['style'] }}; border-radius: var(--tm-card-radius);">
                <div class="border-b px-5 py-4" style="border-color: var(--tm-border-color); background: var(--tm-surface-color);">
                    <p class="text-sm font-extrabold" style="color: var(--tm-brand-color);">Temp Mail SaaS</p>
                </div>
                <div class="space-y-5 p-5">
                    <template x-if="mode === 'homepage'">
                        <div>
                            <p class="text-sm font-bold" style="color: var(--tm-muted-text-color);">Homepage</p>
                            <h3 class="mt-2 text-2xl font-extrabold">Private inboxes for fast verification.</h3>
                            <p class="mt-2 text-sm leading-6" style="color: var(--tm-muted-text-color);">Create a disposable inbox, receive messages, and keep your real address private.</p>
                        </div>
                    </template>
                    <template x-if="mode === 'mailbox'">
                        <div class="space-y-3">
                            <h3 class="text-xl font-extrabold">Mailbox</h3>
                            <div class="rounded-md border p-3" style="border-color: var(--tm-border-color); background: var(--tm-surface-color); box-shadow: var(--tm-shadow);">
                                <p class="text-sm font-bold">security-code@example.com</p>
                                <p class="mt-1 text-xs" style="color: var(--tm-muted-text-color);">Your verification code arrived 1 minute ago.</p>
                            </div>
                        </div>
                    </template>
                    <template x-if="mode === 'blog_card'">
                        <div class="rounded-md border p-4" style="border-color: var(--tm-border-color); background: var(--tm-surface-color); box-shadow: var(--tm-shadow); border-radius: var(--tm-card-radius);">
                            <p class="text-xs font-extrabold" style="color: var(--tm-accent-color);">Privacy Guide</p>
                            <h3 class="mt-2 text-xl font-extrabold">How temporary email protects signups</h3>
                            <p class="mt-2 text-sm" style="color: var(--tm-muted-text-color);">A practical guide for safer account verification.</p>
                        </div>
                    </template>
                    <template x-if="mode === 'cta'">
                        <div class="rounded-md p-5" style="background: var(--tm-surface-color); border-radius: var(--tm-card-radius);">
                            <h3 class="text-xl font-extrabold">Start with a clean inbox</h3>
                            <button type="button" class="mt-4 min-h-11 px-4 text-sm font-extrabold text-white" style="background: var(--tm-brand-color); border-radius: var(--tm-button-radius); transition-duration: var(--tm-motion-duration);">Create inbox</button>
                        </div>
                    </template>
                    <template x-if="mode === 'faq'">
                        <div class="space-y-3">
                            <h3 class="text-xl font-extrabold">FAQ</h3>
                            <details class="rounded-md border p-3" style="border-color: var(--tm-border-color); background: var(--tm-surface-color); border-radius: var(--tm-card-radius);">
                                <summary class="font-extrabold">How long do inboxes last?</summary>
                                <p class="mt-2 text-sm" style="color: var(--tm-muted-text-color);">Retention depends on public mailbox rules configured by the admin.</p>
                            </details>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>
