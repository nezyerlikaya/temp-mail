<?php

namespace App\Services\PublicSite;

use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\MailboxRule;
use App\Models\User;
use App\Services\BlockedLists\BlockedListEnforcementService;
use App\Services\Mailboxes\MailboxAddressService;
use App\Services\Mailboxes\MailboxRuleResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicMailboxService
{
    public function __construct(
        private readonly MailboxRuleResolver $rules,
        private readonly MailboxAddressService $addresses,
        private readonly BlockedListEnforcementService $enforcement,
    ) {}

    /** @return Collection<int, Domain> */
    public function availableDomains(): Collection
    {
        return Domain::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->where('catch_all_ready', true)
            ->where('status', 'ready')
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get();
    }

    public function resolveDomain(int $domainId): Domain
    {
        $domain = $this->availableDomains()->firstWhere('id', $domainId);

        if (! $domain) {
            throw ValidationException::withMessages([
                'domain_id' => 'Select an active public domain that is ready to receive mail.',
            ]);
        }

        return $domain;
    }

    public function rules(): MailboxRule
    {
        return $this->rules->rules();
    }

    public function lifetimeFor(?User $user): int
    {
        return $this->rules->lifetimeFor($user ? 'registered' : 'guest');
    }

    public function allowsCustomAlias(?User $user): bool
    {
        $membership = $user?->memberships()
            ->with('plan.limits')
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latest('starts_at')
            ->first();

        return (bool) $membership?->plan?->limits?->custom_alias_allowed;
    }

    public function localPart(string $requestedAlias, ?User $user): string
    {
        if (filled($requestedAlias)) {
            if (! $this->allowsCustomAlias($user)) {
                throw ValidationException::withMessages([
                    'alias' => 'Custom aliases are available only when the current plan permits them.',
                ]);
            }

            return $this->addresses->normalizeLocalPart($requestedAlias);
        }

        return $this->randomAlias();
    }

    public function ensureCreationAllowed(string $address, Domain $domain, ?string $ip): void
    {
        $this->enforcement->ensureMailboxCreationAllowed($address, $domain->domain_name, $ip);
    }

    public function ensureWithinActiveLimit(?string $ip, MailboxRule $rules): void
    {
        $hash = $this->ipHash($ip);

        if (! $hash) {
            return;
        }

        $count = Mailbox::query()
            ->where('mailbox_type', 'guest')
            ->where('status', 'active')
            ->where('created_ip_hash', $hash)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();

        if ($count >= $rules->maximum_active_mailboxes) {
            throw ValidationException::withMessages([
                'domain_id' => 'Active mailbox limit reached for this session. Let an inbox expire before creating another.',
            ]);
        }
    }

    public function ipHash(?string $ip): ?string
    {
        return filled($ip) ? hash_hmac('sha256', (string) $ip, (string) config('app.key')) : null;
    }

    private function randomAlias(): string
    {
        $rules = $this->rules();
        $length = max(8, min(32, (int) $rules->random_alias_length));

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $alias = match ($rules->random_alias_format) {
                'numeric' => collect(range(1, $length))->map(fn (): string => (string) random_int(0, 9))->implode(''),
                'words' => Str::lower(Str::random(6).'-'.Str::random(max(4, $length - 7))),
                default => Str::lower(Str::random($length)),
            };

            if (! Mailbox::query()->where('local_part', $alias)->exists()) {
                return $alias;
            }
        }

        return Str::lower(Str::random($length + 8));
    }
}
