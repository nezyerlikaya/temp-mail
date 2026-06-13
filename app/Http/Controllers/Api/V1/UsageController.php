<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiJsonResponse;
use App\Services\Api\ApiRateLimitResolver;
use App\Services\Api\ApiUsageTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    public function __construct(
        private readonly ApiUsageTracker $usage,
        private readonly ApiRateLimitResolver $limits,
        private readonly ApiJsonResponse $json,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $key = $request->attributes->get('api_key');
        $limit = $this->limits->monthlyLimit($key);

        return $this->json->success($this->usage->summary($key->user, $limit), [
            'environment' => $key->environment,
            'key_prefix' => $key->key_prefix,
        ]);
    }
}
