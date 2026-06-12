<?php

namespace App\Services\Security;

class BotProviderRegistry
{
    /** @return array<string, array<string, mixed>> */
    public function providers(): array
    {
        return [
            'none' => [
                'label' => 'None',
                'description' => 'Bot protection is disabled. Use only during setup or recovery.',
                'recommended' => false,
            ],
            'turnstile' => [
                'label' => 'Cloudflare Turnstile',
                'description' => 'Recommended privacy-friendly challenge provider for shared-hosting deployments.',
                'recommended' => true,
            ],
            'recaptcha' => [
                'label' => 'Google reCAPTCHA',
                'description' => 'Supports v2 checkbox readiness and v3 score readiness.',
                'recommended' => false,
            ],
        ];
    }

    /** @return array<string, string> */
    public function protectedForms(): array
    {
        return [
            'login' => 'Login',
            'register' => 'Register',
            'forgot_password' => 'Forgot password',
            'contact' => 'Contact',
            'comments' => 'Comments',
            'mailbox_creation' => 'Mailbox creation',
            'api_access' => 'API access readiness',
        ];
    }

    /** @return array<string, string> */
    public function failModes(): array
    {
        return [
            'block' => 'Block',
            'challenge' => 'Challenge',
            'log_only' => 'Log only',
        ];
    }
}
