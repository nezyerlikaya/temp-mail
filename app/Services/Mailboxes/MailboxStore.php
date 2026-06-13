<?php

namespace App\Services\Mailboxes;

use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\BlockedLists\BlockedListEnforcementService;
use Illuminate\Support\Carbon;

class MailboxStore
{
    public function __construct(
        private readonly MailboxAddressService $addresses,
        private readonly MailboxLifecycleService $lifecycle,
        private readonly BlockedListEnforcementService $enforcement,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, Domain $domain, User $actor, ?string $ip): Mailbox
    {
        $localPart = $this->addresses->normalizeLocalPart((string) $data['local_part']);
        $address = $this->addresses->address($localPart, $domain);
        $this->enforcement->ensureMailboxCreationAllowed($address, $domain->domain_name, $ip);
        $this->addresses->ensureAvailable($address);

        return Mailbox::query()->create([
            'domain_id' => $domain->id,
            'user_id' => filled($data['user_id'] ?? null) ? (int) $data['user_id'] : null,
            'locale_id' => filled($data['locale_id'] ?? null) ? (int) $data['locale_id'] : null,
            'address' => $address,
            'local_part' => $localPart,
            'mailbox_type' => (string) $data['mailbox_type'],
            'status' => 'active',
            'expires_at' => filled($data['expires_at'] ?? null) ? Carbon::parse($data['expires_at']) : null,
            'last_activity_at' => now(),
            'message_count' => 0,
            'created_ip_hash' => filled($ip) ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'activity_timeline' => $this->lifecycle->initialTimeline((string) $data['mailbox_type']),
            'created_by' => $actor->id,
        ])->refresh();
    }

    /** @return array<string, int|string> */
    public function metrics(): array
    {
        return [
            'active' => Mailbox::query()->where('status', 'active')->count(),
            'created_today' => Mailbox::query()->whereDate('created_at', today())->count(),
            'expired' => Mailbox::query()->where('status', 'expired')->count(),
            'locked' => Mailbox::query()->where('status', 'locked')->count(),
            'emails_today' => 'Ready',
            'delivery_health' => 'Ready',
        ];
    }
}
