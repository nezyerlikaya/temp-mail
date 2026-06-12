<?php

namespace App\Services\EmailTemplates;

class EmailTemplateDeliverabilityService
{
    /** @return array{ready: bool, message: string} */
    public function readiness(): array
    {
        $default = (string) config('mail.default', '');
        $from = (string) config('mail.from.address', '');

        if (blank($default) || blank($from)) {
            return [
                'ready' => false,
                'message' => 'Mail delivery is not configured. Add a default mailer and from address before sending tests.',
            ];
        }

        $mailer = config('mail.mailers.'.$default);
        if (! is_array($mailer)) {
            return [
                'ready' => false,
                'message' => 'The selected mailer is not configured.',
            ];
        }

        return [
            'ready' => true,
            'message' => 'Mail delivery will use the configured '.$default.' mailer.',
        ];
    }
}
