<?php

namespace App\Services\BlockedLists;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlockedValueNormalizer
{
    /** @return array<string, string> */
    public function types(): array
    {
        return [
            'sender_email' => 'Sender email',
            'sender_domain' => 'Sender domain',
            'recipient_email_pattern' => 'Recipient email pattern',
            'recipient_domain' => 'Recipient domain',
            'ip_address' => 'IP address or protected range',
            'comment_email' => 'Comment email',
            'blocked_phrase' => 'Blocked word/phrase readiness',
        ];
    }

    public function normalize(string $type, string $value): string
    {
        $value = trim($value);

        return match ($type) {
            'sender_email', 'comment_email' => $this->email($value),
            'sender_domain', 'recipient_domain' => $this->domain($value),
            'recipient_email_pattern' => $this->emailPattern($value),
            'ip_address' => $this->ipRule($value),
            'blocked_phrase' => $this->phrase($value),
            default => $this->fail('entry_type', 'Select a supported blocked-list entry type.'),
        };
    }

    public function display(string $type, string $normalized): string
    {
        if ($type === 'ip_address') {
            return $this->maskIpRule($normalized);
        }

        if (in_array($type, ['sender_email', 'comment_email'], true)) {
            [$name, $domain] = array_pad(explode('@', $normalized, 2), 2, '');

            return substr($name, 0, 2).'***@'.$domain;
        }

        return $normalized;
    }

    public function hash(string $normalized): string
    {
        return hash('sha256', $normalized);
    }

    private function email(string $value): string
    {
        $email = str($value)->lower()->toString();

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->fail('value', 'Enter a valid email address.');
        }

        return $email;
    }

    private function domain(string $value): string
    {
        $domain = str($value)->lower()->replaceStart('http://', '')->replaceStart('https://', '')->before('/')->trim('.')->toString();

        if (! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i', $domain)) {
            $this->fail('value', 'Enter a valid domain name without a URL path.');
        }

        return $domain;
    }

    private function emailPattern(string $value): string
    {
        $pattern = str($value)->lower()->toString();

        if (! str_contains($pattern, '@') || substr_count($pattern, '*') > 2 || str_contains($pattern, '**')) {
            $this->fail('value', 'Use a safe recipient pattern such as *@example.com or billing*@example.com.');
        }

        [$local, $domain] = array_pad(explode('@', $pattern, 2), 2, '');

        if ($local === '' || $domain === '' || ! preg_match('/^[a-z0-9._%+\-*]+$/i', $local)) {
            $this->fail('value', 'Recipient patterns may only use email-safe characters and limited wildcards.');
        }

        $this->domain(str_replace('*', 'x', $domain));

        return $pattern;
    }

    private function ipRule(string $value): string
    {
        $value = trim($value);

        if (filter_var($value, FILTER_VALIDATE_IP) !== false) {
            return $value;
        }

        if (preg_match('/^([0-9a-f:.]+)\/([0-9]{1,3})$/i', $value, $matches) && filter_var($matches[1], FILTER_VALIDATE_IP) !== false) {
            $prefix = (int) $matches[2];
            $max = str_contains($matches[1], ':') ? 128 : 32;

            if ($prefix >= 0 && $prefix <= $max) {
                return $value;
            }
        }

        $this->fail('value', 'Enter an IP address or a protected CIDR range.');
    }

    private function phrase(string $value): string
    {
        $phrase = Str::squish(str(strip_tags($value))->lower()->toString());

        if (Str::length($phrase) < 3 || Str::length($phrase) > 120) {
            $this->fail('value', 'Blocked phrases must be between 3 and 120 characters.');
        }

        if (preg_match('/[<>{}\[\]\\\\]/', $phrase)) {
            $this->fail('value', 'Blocked phrases cannot include unsafe pattern characters.');
        }

        return $phrase;
    }

    private function maskIpRule(string $value): string
    {
        if (str_contains($value, '/')) {
            [$ip, $prefix] = explode('/', $value, 2);

            return $this->maskIpRule($ip).'/'.$prefix;
        }

        if (str_contains($value, ':')) {
            return substr($value, 0, 4).'…';
        }

        $parts = explode('.', $value);

        return count($parts) === 4 ? $parts[0].'.'.$parts[1].'.***.***' : 'Protected IP';
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
