<?php

namespace App\Http\Middleware;

use App\Services\Api\ApiJsonResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiScope
{
    public function __construct(private readonly ApiJsonResponse $json) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $key = $request->attributes->get('api_key');

        if (! $key || ! in_array($scope, $key->scopes ?? [], true)) {
            return $this->json->error('missing_scope', 'This API key does not include the required scope.', 403, ['required_scope' => $scope]);
        }

        return $next($request);
    }
}
