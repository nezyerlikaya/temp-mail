<?php

namespace App\Actions\Mailboxes;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Mailboxes\MailboxRulesStore;
use Illuminate\Support\Facades\DB;

class ExpiredMailboxCleanupAction
{
    public function __construct(private readonly MailboxRulesStore $rules, private readonly AuditLogger $audit) {}

    /** @return array{removed: int, cutoff: string} */
    public function handle(User $actor): array
    {
        $rules = $this->rules->current();
        $cutoff = now()->subHours($rules->expired_cleanup_delay_hours);
        $ids = Mailbox::query()->where('status', 'expired')->whereNotNull('expires_at')->where('expires_at', '<=', $cutoff)->pluck('id');

        $removed = DB::transaction(fn (): int => Mailbox::query()->whereIn('id', $ids)->where('status', 'expired')->delete());
        $this->audit->record('mailbox.expired_cleanup_run', $actor, null, [
            'removed_count' => $removed, 'cleanup_delay_hours' => $rules->expired_cleanup_delay_hours,
        ], ['module' => 'mailbox', 'action' => 'Expired mailbox cleanup run']);

        return ['removed' => $removed, 'cutoff' => $cutoff->toIso8601String()];
    }
}
