<?php

namespace App\Actions\PublicSite;

use App\Http\Requests\PublicSite\CreatePublicMailboxRequest;
use App\Models\Mailbox;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Mailboxes\MailboxAddressService;
use App\Services\Mailboxes\MailboxLifecycleService;
use App\Services\PublicSite\PublicMailboxAccessService;
use App\Services\PublicSite\PublicMailboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePublicMailboxAction
{
    public function __construct(
        private readonly PublicMailboxService $mailboxes,
        private readonly PublicMailboxAccessService $access,
        private readonly MailboxAddressService $addresses,
        private readonly MailboxLifecycleService $lifecycle,
        private readonly AnalyticsEventTracker $analytics,
    ) {}

    /** @return array{mailbox: Mailbox, token: string, url: string} */
    public function handle(CreatePublicMailboxRequest $request): array
    {
        $locale = $request->attributes->get('public_locale');
        $domain = $this->mailboxes->resolveDomain((int) $request->validated('domain_id'));
        $rules = $this->mailboxes->rules();
        $localPart = $this->mailboxes->localPart((string) $request->validated('alias', ''), $request->user());
        $address = $this->addresses->address($localPart, $domain);

        $this->mailboxes->ensureCreationAllowed($address, $domain, $request->ip());
        $this->mailboxes->ensureWithinActiveLimit($request->ip(), $rules);
        $this->addresses->ensureAvailable($address);

        $mailbox = DB::transaction(fn (): Mailbox => Mailbox::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $request->user()?->id,
            'locale_id' => $locale?->id,
            'address' => $address,
            'local_part' => $localPart,
            'mailbox_type' => $request->user() ? 'registered' : 'guest',
            'status' => 'active',
            'expires_at' => now()->addMinutes($this->mailboxes->lifetimeFor($request->user())),
            'last_activity_at' => now(),
            'message_count' => 0,
            'created_ip_hash' => $this->mailboxes->ipHash($request->ip()),
            'activity_timeline' => $this->lifecycle->initialTimeline($request->user() ? 'registered' : 'guest'),
        ])->refresh());

        $this->analytics->trackSafely('mailbox.created', [
            'user' => $mailbox->user_id,
            'locale_id' => $mailbox->locale_id,
            'domain' => $domain,
            'ip' => $request->ip(),
            'metadata' => ['source' => 'public', 'mailbox_type' => $mailbox->mailbox_type],
        ]);

        $token = $this->access->issue($request, $mailbox);

        if (! $mailbox->exists) {
            throw ValidationException::withMessages(['mailbox' => 'The mailbox could not be created. Try again.']);
        }

        return [
            'mailbox' => $mailbox,
            'token' => $token,
            'url' => $this->access->url($mailbox, $locale->locale, $token),
        ];
    }
}
