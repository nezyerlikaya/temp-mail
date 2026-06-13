<?php

namespace App\Services\PublicSite;

use App\Models\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicMailboxAccessService
{
    public function issue(Request $request, Mailbox $mailbox): string
    {
        $token = Str::random(64);

        $request->session()->put($this->sessionKey($mailbox), Hash::make($token));

        return $token;
    }

    public function canAccess(Request $request, Mailbox $mailbox, string $token): bool
    {
        if (! preg_match('/^[A-Za-z0-9]{64}$/', $token)) {
            return false;
        }

        $hash = $request->session()->get($this->sessionKey($mailbox));

        return is_string($hash) && Hash::check($token, $hash);
    }

    public function url(Mailbox $mailbox, string $locale, string $token): string
    {
        return route('public.mailbox.show', [
            'locale' => $locale,
            'mailbox' => $mailbox,
            'access_token' => $token,
        ]);
    }

    public function messageUrl(Mailbox $mailbox, int $messageId, string $locale, string $token): string
    {
        return route('public.mailbox.messages.show', [
            'locale' => $locale,
            'mailbox' => $mailbox,
            'message' => $messageId,
            'access_token' => $token,
        ]);
    }

    private function sessionKey(Mailbox $mailbox): string
    {
        return 'public_mailboxes.'.$mailbox->id.'.access_hash';
    }
}
