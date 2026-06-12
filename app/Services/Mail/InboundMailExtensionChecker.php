<?php

namespace App\Services\Mail;

class InboundMailExtensionChecker
{
    /** @return array{ready: bool, extension: string, message: string} */
    public function check(): array
    {
        $ready = extension_loaded('imap')
            && function_exists('imap_open')
            && function_exists('imap_close');

        return [
            'ready' => $ready,
            'extension' => 'imap',
            'message' => $ready
                ? 'PHP IMAP is available for safe connection tests.'
                : 'PHP IMAP is missing. Enable the imap extension in your hosting control panel before testing inbound mail.',
        ];
    }
}
