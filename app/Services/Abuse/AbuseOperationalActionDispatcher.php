<?php

namespace App\Services\Abuse;

use App\Actions\Comments\TrashCommentAction;
use App\Actions\Mailboxes\ExpireMailboxAction;
use App\Actions\Mailboxes\LockMailboxAction;
use App\Actions\Users\SuspendUserAction;
use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Blocklists\BlocklistService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AbuseOperationalActionDispatcher
{
    public function __construct(
        private readonly LockMailboxAction $lockMailbox,
        private readonly ExpireMailboxAction $expireMailbox,
        private readonly SuspendUserAction $suspendUser,
        private readonly TrashCommentAction $trashComment,
        private readonly BlocklistService $blocklist,
        private readonly AbuseCaseTimelineService $timeline,
        private readonly AbuseNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function dispatch(User $actor, AbuseReport $report, array $payload): string
    {
        $correlationId = (string) Str::uuid();
        $previous = request()->headers->get('X-Request-Id');
        request()->headers->set('X-Request-Id', $correlationId);

        try {
            $summary = match ($payload['operational_action']) {
                'lock_mailbox' => $this->lock($actor, $report),
                'expire_mailbox' => $this->expire($actor, $report),
                'suspend_user' => $this->suspend($actor, $report),
                'trash_comment' => $this->trash($actor, $report),
                'block_sender_email', 'block_sender_domain', 'block_recipient_email', 'block_recipient_domain', 'block_ip_hash' => $this->block($actor, $report, $payload),
                default => throw ValidationException::withMessages(['operational_action' => 'Select a supported operational action.']),
            };

            $this->timeline->record($report, $actor, 'operational_action', $summary, ['action' => $payload['operational_action']], $correlationId);
            $this->audit->record('abuse.operational_action_executed', $actor, null, [
                'case_reference' => $report->case_reference,
                'operational_action' => $payload['operational_action'],
            ], ['module' => 'trust', 'target' => $report, 'correlation_id' => $correlationId, 'severity' => 'critical']);
            $this->notifications->dispatchCriticalResolution($report, $payload['operational_action']);

            return $summary;
        } finally {
            if ($previous === null) {
                request()->headers->remove('X-Request-Id');
            } else {
                request()->headers->set('X-Request-Id', $previous);
            }
        }
    }

    private function lock(User $actor, AbuseReport $report): string
    {
        $mailbox = $report->mailbox;
        throw_unless($mailbox, ValidationException::withMessages(['operational_action' => 'This case has no linked mailbox.']));
        $this->lockMailbox->handle($actor, $mailbox);

        return 'Linked mailbox locked after confirmed review.';
    }

    private function expire(User $actor, AbuseReport $report): string
    {
        $mailbox = $report->mailbox;
        throw_unless($mailbox, ValidationException::withMessages(['operational_action' => 'This case has no linked mailbox.']));
        $this->expireMailbox->handle($actor, $mailbox);

        return 'Linked mailbox expired after confirmed review.';
    }

    private function suspend(User $actor, AbuseReport $report): string
    {
        $user = $report->reportedUser;
        throw_unless($user, ValidationException::withMessages(['operational_action' => 'This case has no linked user.']));
        $this->suspendUser->handle($actor, $user);

        return 'Linked user suspended after confirmed review.';
    }

    private function trash(User $actor, AbuseReport $report): string
    {
        $comment = $report->relatedComment;
        throw_unless($comment, ValidationException::withMessages(['operational_action' => 'This case has no linked comment.']));
        $this->trashComment->handle($actor, $comment);

        return 'Linked comment moved to trash after confirmed review.';
    }

    /** @param array<string, mixed> $payload */
    private function block(User $actor, AbuseReport $report, array $payload): string
    {
        $type = match ($payload['operational_action']) {
            'block_sender_email' => 'sender_email',
            'block_sender_domain' => 'sender_domain',
            'block_recipient_email' => 'recipient_email',
            'block_recipient_domain' => 'recipient_domain',
            default => 'blocked_ip_hash',
        };
        $entry = $this->blocklist->add($actor, $report, $type, $payload['block_value']);
        $this->audit->record('abuse.blocklist_entry_created', $actor, null, [
            'case_reference' => $report->case_reference,
            'block_type' => $type,
            'value_preview' => $entry->value_preview,
        ], ['module' => 'mail-infrastructure', 'target' => $report]);

        return 'Blocklist entry created: '.$entry->value_preview;
    }
}
