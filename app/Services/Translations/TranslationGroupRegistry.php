<?php

namespace App\Services\Translations;

class TranslationGroupRegistry
{
    /** @return array<string, string> */
    public function groups(): array
    {
        return [
            'common' => 'Common',
            'navigation' => 'Navigation',
            'homepage' => 'Homepage',
            'mailbox_experience' => 'Mailbox Experience',
            'authentication' => 'Authentication',
            'errors_validation' => 'Errors and Validation',
            'footer' => 'Footer',
            'cookie_consent' => 'Cookie and Consent',
            'system_messages' => 'System Messages',
        ];
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->groups());
    }
}
