<?php

namespace App\Http\Middleware;

use App\Models\Mailbox;
use App\Services\PublicSite\PublicMailboxAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePublicMailboxAccess
{
    public function __construct(private readonly PublicMailboxAccessService $access) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mailbox = $request->route('mailbox');
        abort_unless($mailbox instanceof Mailbox, 404);

        $token = (string) ($request->query('access_token') ?: $request->input('access_token'));
        abort_unless($this->access->canAccess($request, $mailbox, $token), 404);

        $request->attributes->set('public_access_token', $token);

        return $next($request);
    }
}
