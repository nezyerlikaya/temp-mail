<?php

namespace App\Http\Middleware;

use App\Models\BlogPost;
use App\Services\Comments\CommentSettingsStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicCommentsAvailable
{
    public function __construct(private readonly CommentSettingsStore $settings) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $post = $request->route('post');

        abort_unless($post instanceof BlogPost && $this->settings->acceptsComments($post), 404);

        return $next($request);
    }
}
