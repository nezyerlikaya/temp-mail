<?php

namespace App\Services\Api;

use App\Models\ApiKey;
use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Billing\PlanLimitResolver;
use App\Services\BlockedLists\BlockedListEnforcementService;
use App\Services\Mailboxes\MailboxAddressService;
use App\Services\Mailboxes\MailboxLifecycleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MailboxApiService
{
    public function __construct(
        private readonly MailboxAddressService $addresses,
        private readonly MailboxLifecycleService $lifecycle,
        private readonly PlanLimitResolver $limits,
        private readonly AnalyticsEventTracker $analytics,
        private readonly BlockedListEnforcementService $enforcement,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(ApiKey $key, array $data): Mailbox
    {
        $limit = $this->limits->forUser($key->user)->maximum_active_inboxes;
        $active = Mailbox::query()
            ->where('user_id', $key->user_id)
            ->where('status', 'active')
            ->count();

        if ($active >= $limit) {
            throw ValidationException::withMessages(['limit' => 'Mailbox plan limit has been reached.']);
        }

        $domain = $this->domainFor($data['domain'] ?? null);
        $localPart = filled($data['local_part'] ?? null)
            ? $this->addresses->normalizeLocalPart((string) $data['local_part'])
            : Str::lower(Str::random(12));
        $address = $this->addresses->address($localPart, $domain);
        $this->enforcement->ensureMailboxCreationAllowed($address, $domain->domain_name, request()->ip());
        $this->addresses->ensureAvailable($address);

        $mailbox = Mailbox::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $key->user_id,
            'address' => $address,
            'local_part' => $localPart,
            'mailbox_type' => $key->environment === 'test' ? 'api_test' : 'api_live',
            'status' => 'active',
            'expires_at' => now()->addMinutes($this->limits->forUser($key->user)->inbox_lifetime_minutes),
            'last_activity_at' => now(),
            'message_count' => 0,
            'activity_timeline' => $this->lifecycle->initialTimeline('api'),
            'created_by' => $key->user_id,
            'api_key_id' => $key->id,
            'api_environment' => $key->environment,
        ])->load('domain');

        $this->analytics->trackSafely('mailbox.created', [
            'user' => $key->user,
            'domain' => $domain,
            'metadata' => [
                'source' => 'api',
                'environment' => $key->environment,
                'mailbox_type' => $mailbox->mailbox_type,
                'owner_assigned' => true,
            ],
        ]);

        return $mailbox;
    }

    public function list(ApiKey $key, int $perPage = 15): LengthAwarePaginator
    {
        return Mailbox::query()
            ->with('domain')
            ->where('user_id', $key->user_id)
            ->where('api_environment', $key->environment)
            ->latest()
            ->paginate(min(max($perPage, 1), 50));
    }

    public function owned(ApiKey $key, Mailbox $mailbox): ?Mailbox
    {
        if ((int) $mailbox->user_id !== (int) $key->user_id || $mailbox->api_environment !== $key->environment) {
            return null;
        }

        return $mailbox->load('domain');
    }

    public function expire(ApiKey $key, Mailbox $mailbox): ?Mailbox
    {
        $owned = $this->owned($key, $mailbox);

        if (! $owned) {
            return null;
        }

        $owned->forceFill([
            'status' => 'expired',
            'expires_at' => now(),
        ])->save();

        $expired = $this->lifecycle->record($owned, 'api_mailbox_expired', 'Mailbox expired', 'Mailbox was expired by API request.')->load('domain');
        $this->analytics->trackSafely('mailbox.expired', [
            'user' => $key->user,
            'domain_id' => $expired->domain_id,
            'metadata' => [
                'source' => 'api',
                'environment' => $key->environment,
                'mailbox_type' => $expired->mailbox_type,
            ],
        ]);

        return $expired;
    }

    public function messages(ApiKey $key, Mailbox $mailbox, int $perPage = 15): ?LengthAwarePaginator
    {
        $owned = $this->owned($key, $mailbox);

        if (! $owned) {
            return null;
        }

        return $owned->messages()
            ->whereNull('deleted_at')
            ->latest('received_at')
            ->paginate(min(max($perPage, 1), 50));
    }

    public function message(ApiKey $key, Mailbox $mailbox, MailboxMessage $message): ?MailboxMessage
    {
        $owned = $this->owned($key, $mailbox);

        if (! $owned || (int) $message->mailbox_id !== (int) $owned->id || $message->deleted_at !== null) {
            return null;
        }

        return $message;
    }

    /** @return Collection<int, Domain> */
    public function domains()
    {
        return Domain::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->where('catch_all_ready', true)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->get();
    }

    private function domainFor(mixed $domain): Domain
    {
        $query = Domain::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->where('catch_all_ready', true)
            ->where('status', 'active');

        if (filled($domain)) {
            $query->where('domain_name', (string) $domain);
        } else {
            $query->orderByDesc('is_default')->orderBy('sort_order');
        }

        $domainModel = $query->first();

        if (! $domainModel) {
            throw ValidationException::withMessages(['domain' => 'No active public receiving domain is available.']);
        }

        return $domainModel;
    }
}
