<?php

namespace App\Services\Translations;

class TranslationSourceRegistry
{
    /** @return array<int, array<string, mixed>> */
    public function sources(): array
    {
        return [
            $this->source('common', 'common.brand.name', 'Temp Mail Cloud', 'Product name used in public interface.', 'short_text', true, 10),
            $this->source('navigation', 'nav.home', 'Home', 'Primary public navigation label.', 'short_text', true, 10),
            $this->source('navigation', 'nav.mailbox', 'Mailbox', 'Temporary mailbox navigation label.', 'short_text', true, 20),
            $this->source('homepage', 'home.header.logo', 'Temp Mail Cloud', 'Logo text in the homepage header.', 'short_text', true, 10),
            $this->source('homepage', 'home.hero.title', 'Private temporary email in seconds', 'Homepage hero headline.', 'short_text', true, 20),
            $this->source('homepage', 'home.hero.description', 'Create a clean temporary inbox for signups, downloads, and privacy-first workflows.', 'Homepage hero supporting copy.', 'long_text', true, 30),
            $this->source('homepage', 'home.cta.title', 'Start with a fresh inbox', 'Homepage CTA title.', 'short_text', true, 40),
            $this->source('homepage', 'home.cta.button', 'Create mailbox', 'Homepage primary CTA label.', 'short_text', true, 50),
            $this->source('homepage', 'home.badge.no_permanent_inbox', 'No permanent inbox', 'Homepage trust badge for temporary inbox behavior.', 'short_text', true, 60),
            $this->source('homepage', 'home.badge.privacy_first', 'Privacy first', 'Homepage trust badge for privacy positioning.', 'short_text', true, 70),
            $this->source('homepage', 'home.visual.mailbox_stream', 'Mailbox stream', 'Decorative public homepage mailbox panel label.', 'short_text', true, 80),
            $this->source('homepage', 'home.visual.ready', 'Ready', 'Decorative public homepage readiness label.', 'short_text', true, 90),
            $this->source('homepage', 'home.feature.simple.title', 'Simple', 'Homepage simple feature title.', 'short_text', true, 100),
            $this->source('homepage', 'home.feature.simple.body', 'Focused temporary inbox access.', 'Homepage simple feature copy.', 'short_text', true, 110),
            $this->source('homepage', 'home.feature.private.title', 'Private', 'Homepage private feature title.', 'short_text', true, 120),
            $this->source('homepage', 'home.feature.private.body', 'Built for short-lived identities.', 'Homepage private feature copy.', 'short_text', true, 130),
            $this->source('homepage', 'home.feature.locale.body', 'Interface direction ready.', 'Homepage locale feature copy.', 'short_text', true, 140),
            $this->source('mailbox_experience', 'mailbox.create.button', 'Create mailbox', 'Button used to create a new temporary mailbox.', 'short_text', true, 10),
            $this->source('mailbox_experience', 'mailbox.create.title', 'Create a private temporary inbox', 'Mailbox creator title.', 'short_text', true, 11),
            $this->source('mailbox_experience', 'mailbox.create.description', 'Pick a receiving domain and start with a random alias. Custom aliases unlock with eligible plans.', 'Mailbox creator description.', 'long_text', true, 12),
            $this->source('mailbox_experience', 'mailbox.domain.empty', 'Receiving domains are not ready yet.', 'No public receiving domains empty state.', 'short_text', true, 13),
            $this->source('mailbox_experience', 'mailbox.domain.label', 'Receiving domain', 'Mailbox domain select label.', 'short_text', true, 13),
            $this->source('mailbox_experience', 'mailbox.alias.label', 'Custom alias', 'Mailbox custom alias label.', 'short_text', true, 14),
            $this->source('mailbox_experience', 'mailbox.alias.placeholder', 'Leave blank for a random alias', 'Mailbox alias placeholder.', 'short_text', true, 15),
            $this->source('mailbox_experience', 'mailbox.empty.title', 'Your inbox is empty', 'Empty inbox headline.', 'short_text', true, 20),
            $this->source('mailbox_experience', 'mailbox.empty.body', 'Messages sent to this address will appear here after refresh.', 'Empty inbox explanatory text.', 'short_text', true, 21),
            $this->source('mailbox_experience', 'mailbox.refresh.label', 'Refresh inbox', 'Inbox refresh button label.', 'short_text', true, 30),
            $this->source('mailbox_experience', 'mailbox.status.active', 'Active inbox', 'Active mailbox status label.', 'short_text', true, 40),
            $this->source('mailbox_experience', 'mailbox.status.expired', 'Expired inbox', 'Expired mailbox status label.', 'short_text', true, 41),
            $this->source('mailbox_experience', 'mailbox.expires.label', 'Expires in', 'Mailbox countdown label.', 'short_text', true, 42),
            $this->source('mailbox_experience', 'mailbox.messages.title', 'Messages', 'Mailbox message list title.', 'short_text', true, 43),
            $this->source('mailbox_experience', 'mailbox.preview.title', 'Message preview', 'Mailbox message preview title.', 'short_text', true, 44),
            $this->source('mailbox_experience', 'mailbox.preview.empty', 'Select a message to preview safe content.', 'Mailbox no selected message hint.', 'short_text', true, 45),
            $this->source('authentication', 'auth.login.title', 'Sign in to your account', 'Login screen title.', 'short_text', true, 10),
            $this->source('authentication', 'auth.login.button', 'Sign in', 'Login submit button label.', 'short_text', true, 20),
            $this->source('errors_validation', 'validation.required', 'This field is required.', 'Required field validation message.', 'short_text', true, 10),
            $this->source('errors_validation', 'errors.not_found', 'The requested page could not be found.', 'Public 404 message.', 'short_text', true, 20),
            $this->source('footer', 'footer.copyright', 'All rights reserved.', 'Footer copyright sentence.', 'short_text', true, 10),
            $this->source('footer', 'footer.links.privacy', 'Privacy Policy', 'Footer privacy link label.', 'short_text', true, 20),
            $this->source('cookie_consent', 'cookie.banner.title', 'Privacy preferences', 'Cookie banner title.', 'short_text', false, 10),
            $this->source('cookie_consent', 'cookie.banner.accept', 'Accept all', 'Cookie consent accept button.', 'short_text', false, 20),
            $this->source('cookie_consent', 'cookie.banner.body', 'We use privacy-friendly cookies to keep your mailbox experience reliable.', 'Cookie banner explanatory body copy with rich text readiness.', 'rich_text', false, 30),
            $this->source('system_messages', 'system.maintenance.title', 'Maintenance in progress', 'Public maintenance title.', 'short_text', true, 10),
            $this->source('system_messages', 'system.saved', 'Changes saved.', 'Generic successful save message.', 'short_text', true, 20),
            $this->source('system_messages', 'system.maintenance.enabled', 'false', 'Boolean readiness flag for maintenance messaging.', 'boolean', false, 30),
        ];
    }

    /** @return array<string, mixed> */
    private function source(string $group, string $key, string $value, string $description, string $type, bool $required, int $sortOrder): array
    {
        return [
            'group_key' => $group,
            'translation_key' => $key,
            'source_value' => $value,
            'description' => $description,
            'value_type' => $type,
            'is_required' => $required,
            'is_active' => true,
            'sort_order' => $sortOrder,
        ];
    }
}
