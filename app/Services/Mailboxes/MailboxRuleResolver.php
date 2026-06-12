<?php

namespace App\Services\Mailboxes;

use App\Models\MailboxRule;

class MailboxRuleResolver
{
    public function __construct(private readonly MailboxRulesStore $store) {}

    public function rules(): MailboxRule
    {
        return $this->store->current();
    }

    public function lifetimeFor(string $mailboxType): int
    {
        $rules = $this->rules();

        return match ($mailboxType) {
            'registered' => $rules->registered_lifetime_minutes,
            'premium' => $rules->premium_lifetime_minutes,
            default => $rules->guest_lifetime_minutes,
        };
    }
}
