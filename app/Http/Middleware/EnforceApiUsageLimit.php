<?php

namespace App\Http\Middleware;

use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Api\ApiJsonResponse;
use App\Services\Api\ApiRateLimitResolver;
use App\Services\Api\ApiUsageTracker;
use App\Services\Security\AbuseSignalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApiUsageLimit
{
    public function __construct(
        private readonly ApiRateLimitResolver $limits,
        private readonly ApiUsageTracker $usage,
        private readonly ApiJsonResponse $json,
        private readonly AbuseSignalService $signals,
        private readonly AnalyticsEventTracker $analytics,
    ) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $key = $request->attributes->get('api_key');

        if ($key && $this->limits->isExceeded($key)) {
            $response = $this->json->error('rate_limit_exceeded', 'The monthly API request limit for this plan has been reached.', 429);
            $this->signals->record([
                'signal_type' => 'rate_limited_request',
                'severity' => 'medium',
                'source_module' => 'api',
                'target_reference' => $key->key_prefix,
                'actor_user_id' => $key->user_id,
                'ip' => $request->ip(),
                'metadata' => [
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'key_prefix' => $key->key_prefix,
                ],
            ]);
            $this->analytics->trackSafely('security.rate_limited', [
                'user' => $key->user,
                'ip' => $request->ip(),
                'metadata' => [
                    'source' => 'api',
                    'route' => $request->path(),
                    'method' => $request->method(),
                    'response_status' => 429,
                ],
            ]);
            $this->usage->recordRequest($key, $request->path(), $request->method(), 429, $this->duration($started));

            return $response;
        }

        $response = $next($request);

        if ($key) {
            $this->usage->recordUsage($key);
            $this->usage->recordRequest($key, $request->path(), $request->method(), $response->getStatusCode(), $this->duration($started));
        }

        return $response;
    }

    private function duration(float $started): int
    {
        return max(0, (int) round((microtime(true) - $started) * 1000));
    }
}
