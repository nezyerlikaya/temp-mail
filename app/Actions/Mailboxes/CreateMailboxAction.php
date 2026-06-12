<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxDomainResolver;
use App\Services\Mailboxes\MailboxStore;

class CreateMailboxAction
{
    public function __construct(
        private readonly MailboxDomainResolver $domains,
        private readonly MailboxStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data, ?string $ip): Mailbox
    {
        $domain = $this->domains->resolve((int) $data['domain_id']);
        $mailbox = $this->store->create($data, $domain, $actor, $ip);

        $this->audit->record('mailbox.created', $actor, null, [
            'mailbox_id' => $mailbox->id,
            'address_domain' => $domain->domain_name,
            'mailbox_type' => $mailbox->mailbox_type,
            'owner_assigned' => $mailbox->user_id !== null,
            'locale_assigned' => $mailbox->locale_id !== null,
        ], ['module' => 'mailbox', 'action' => 'Mailbox created', 'target' => $mailbox]);

        return $mailbox;
    }
}
