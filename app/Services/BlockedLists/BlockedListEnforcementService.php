<?php

namespace App\Services\BlockedLists;

use Illuminate\Validation\ValidationException;

class BlockedListEnforcementService
{
    public function __construct(private readonly BlockedListMatcher $matcher) {}

    public function mailSender(?string $senderEmail): array
    {
        $domain = $this->domainFromEmail($senderEmail);

        return $this->matcher->matchAny([
            'sender_email' => $senderEmail,
            'sender_domain' => $domain,
        ]);
    }

    public function mailRecipient(?string $recipientEmail): array
    {
        $domain = $this->domainFromEmail($recipientEmail);

        return $this->matcher->matchAny([
            'recipient_email_pattern' => $recipientEmail,
            'recipient_domain' => $domain,
        ]);
    }

    public function mailboxCreation(string $address, string $domain, ?string $ip = null): array
    {
        return $this->matcher->matchAny([
            'recipient_email_pattern' => $address,
            'recipient_domain' => $domain,
            'ip_address' => $ip,
        ]);
    }

    public function ensureMailboxCreationAllowed(string $address, string $domain, ?string $ip = null): void
    {
        $result = $this->mailboxCreation($address, $domain, $ip);

        if ($result['decision'] === 'blocked') {
            throw ValidationException::withMessages(['local_part' => 'This mailbox address or request source is blocked by a reviewed rule.']);
        }
    }

    public function comment(?string $email, ?string $ip, ?string $content): array
    {
        return $this->matcher->matchAny([
            'comment_email' => $email,
            'ip_address' => $ip,
            'blocked_phrase' => $content,
        ]);
    }

    public function securityIp(?string $ip): array
    {
        return filled($ip)
            ? $this->matcher->match('ip_address', (string) $ip)
            : ['decision' => 'allowed', 'message' => 'No IP was supplied for enforcement readiness.', 'matched' => false, 'entry' => null, 'checked_type' => 'ip_address'];
    }

    public function apiIpDecision(?string $ip): array
    {
        return $this->securityIp($ip);
    }

    private function domainFromEmail(?string $email): ?string
    {
        if (! filled($email) || ! str_contains((string) $email, '@')) {
            return null;
        }

        return str((string) $email)->after('@')->lower()->toString();
    }
}
