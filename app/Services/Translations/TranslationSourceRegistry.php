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
            $this->source('mailbox_experience', 'mailbox.create.button', 'Create mailbox', 'Button used to create a new temporary mailbox.', 'short_text', true, 10),
            $this->source('mailbox_experience', 'mailbox.empty.title', 'Your inbox is empty', 'Empty inbox headline.', 'short_text', true, 20),
            $this->source('mailbox_experience', 'mailbox.refresh.label', 'Refresh inbox', 'Inbox refresh button label.', 'short_text', true, 30),
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
