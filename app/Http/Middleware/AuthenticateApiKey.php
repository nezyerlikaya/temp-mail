<?php

namespace App\Http\Middleware;

use App\Services\Api\ApiJsonResponse;
use App\Services\Api\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(private readonly ApiKeyService $keys, private readonly ApiJsonResponse $json) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) $request->bearerToken();

        if ($secret === '') {
            return $this->json->error('missing_api_key', 'Send an API key using the Authorization Bearer header.', 401);
        }

        $key = $this->keys->authenticate($secret, $request->ip());

        if (! $key) {
            return $this->json->error('invalid_api_key', 'The API key is invalid, expired, revoked, blocked by IP, or not permitted by plan.', 401);
        }

        $request->attributes->set('api_key', $key);
        $request->attributes->set('api_user', $key->user);

        return $next($request);
    }
}
