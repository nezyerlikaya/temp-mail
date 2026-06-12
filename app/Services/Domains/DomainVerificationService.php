<?php

namespace App\Services\Domains;

use App\Models\Domain;

class DomainVerificationService
{
    public function expectedOwnershipRecord(Domain|string $domain): array
    {
        $name = $domain instanceof Domain ? $domain->domain_name : $domain;

        return [
            'type' => 'TXT',
            'host' => '_tempmail-verification.'.$name,
            'value' => 'tempmail-site-verification='.substr(hash_hmac('sha256', $name, (string) config('app.key')), 0, 32),
        ];
    }
}
