<?php

namespace App\Actions\BlockedLists;

use App\Models\User;
use App\Services\BlockedLists\BlockedListExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportBlockedEntriesAction
{
    public function __construct(private readonly BlockedListExportService $exports) {}

    /** @param array<string, mixed> $filters */
    public function handle(User $actor, array $filters, bool $includeSensitiveIp): StreamedResponse
    {
        return $this->exports->export($actor, $filters, $includeSensitiveIp);
    }
}
