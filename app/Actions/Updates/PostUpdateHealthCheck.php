<?php

namespace App\Actions\Updates;

use App\Services\Health\SystemHealthChecker;
use Throwable;

class PostUpdateHealthCheck
{
    public function __construct(private readonly SystemHealthChecker $health) {}

    /** @return array{status: string, message: string, summary: array<string, mixed>|null, checked_at: string|null} */
    public function run(): array
    {
        try {
            $result = $this->health->run();

            return [
                'status' => $result['overall_status'],
                'message' => 'Post-update health checks completed.',
                'summary' => $result['summary'],
                'checked_at' => $result['checked_at'],
            ];
        } catch (Throwable) {
            return [
                'status' => 'warning',
                'message' => 'Post-update health checks are ready, but could not run in the current environment.',
                'summary' => null,
                'checked_at' => null,
            ];
        }
    }
}
