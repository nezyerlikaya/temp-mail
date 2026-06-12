<?php

namespace App\Services\Mailboxes;

use App\Models\Domain;
use Illuminate\Validation\ValidationException;

class MailboxDomainResolver
{
    public function resolve(int $domainId): Domain
    {
        $domain = Domain::query()->find($domainId);

        if (! $domain) {
            throw ValidationException::withMessages(['domain_id' => 'Select an existing receiving domain.']);
        }

        if (! $domain->is_active || ! $domain->is_public || $domain->status !== 'ready') {
            throw ValidationException::withMessages([
                'domain_id' => 'Mailboxes can only be created on active, public, and usable domains.',
            ]);
        }

        return $domain;
    }
}
