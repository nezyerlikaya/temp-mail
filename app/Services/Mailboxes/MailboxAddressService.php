<?php

namespace App\Services\Mailboxes;

use App\Models\Domain;
use App\Models\Mailbox;
use Illuminate\Validation\ValidationException;

class MailboxAddressService
{
    public function normalizeLocalPart(string $localPart): string
    {
        $normalized = str($localPart)->lower()->trim()->replaceMatches('/[^a-z0-9._-]+/', '-')->trim('-._')->toString();

        if ($normalized === '' || strlen($normalized) > 64) {
            throw ValidationException::withMessages(['local_part' => 'Enter a usable local part up to 64 characters.']);
        }

        return $normalized;
    }

    public function address(string $localPart, Domain $domain): string
    {
        return $this->normalizeLocalPart($localPart).'@'.$domain->domain_name;
    }

    public function ensureAvailable(string $address): void
    {
        if (Mailbox::query()->where('address', $address)->exists()) {
            throw ValidationException::withMessages(['local_part' => 'That mailbox address is already in use.']);
        }
    }
}
