<?php

namespace App\Actions\PublicSite;

use App\Models\Mailbox;

class RefreshPublicInboxAction
{
    public function handle(Mailbox $mailbox): Mailbox
    {
        if ($mailbox->status === 'active' && $mailbox->expires_at && $mailbox->expires_at->lte(now())) {
            $mailbox->forceFill(['status' => 'expired', 'last_activity_at' => now()])->save();
        }

        return $mailbox->refresh();
    }
}
