<?php

namespace App\Services\EmailTemplates;

use App\Services\Mail\SmtpSettingsStore;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EmailTemplateDeliverabilityService
{
    public function __construct(private readonly SmtpSettingsStore $smtp) {}

    /** @return array{ready: bool, message: string} */
    public function readiness(): array
    {
        try {
            if (Schema::hasTable('smtp_connections')) {
                $defaultSmtp = $this->smtp->defaultConnection();

                if ($defaultSmtp) {
                    return [
                        'ready' => $defaultSmtp->status === 'connected',
                        'message' => $defaultSmtp->status === 'connected'
                            ? 'Email templates will use the default SMTP connection: '.$defaultSmtp->name.'.'
                            : 'Default SMTP connection exists but has not passed readiness testing.',
                    ];
                }
            }
        } catch (Throwable) {
            //
        }

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
