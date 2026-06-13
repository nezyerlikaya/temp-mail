<?php

namespace App\Services\BlockedLists;

class BlockedListService
{
    /** @return array<string, string> */
    public function sources(): array
    {
        return [
            'manual' => 'Manual',
            'abuse_report' => 'Abuse report',
            'security_review' => 'Security review',
            'comment_moderation' => 'Comment moderation',
        ];
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return ['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired'];
    }

    /** @return array<string, string> */
    public function groups(): array
    {
        return [
            'senders' => 'Senders',
            'domains' => 'Domains',
            'recipients' => 'Recipients',
            'ip-rules' => 'IP Rules',
            'comment-rules' => 'Comment Rules',
        ];
    }

    /** @return array<string, string> */
    public function notificationReadiness(): array
    {
        return [
            'label' => 'Notification readiness',
            'message' => 'Critical manual block actions are audit-linked and ready for notification rules.',
        ];
    }
}
