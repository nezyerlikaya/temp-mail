<?php

namespace App\Console\Commands;

use App\Services\Billing\MembershipExpiryService;
use Illuminate\Console\Command;

class ProcessMembershipExpirations extends Command
{
    protected $signature = 'memberships:process-expirations';

    protected $description = 'Process expired and expiring membership readiness states.';

    public function handle(MembershipExpiryService $expiry): int
    {
        $result = $expiry->process();
        $this->info($result['expired'].' expired, '.$result['expiring'].' marked expiring.');

        return self::SUCCESS;
    }
}
