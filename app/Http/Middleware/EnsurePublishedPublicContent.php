<?php

namespace App\Http\Middleware;

use App\Models\BlogPost;
use App\Models\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublishedPublicContent
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->attributes->get('public_locale');
        $post = $request->route('post');

        if ($post instanceof BlogPost) {
            abort_unless(
                $locale instanceof Locale
                && (int) $post->locale_id === (int) $locale->id
                && $post->status === 'published'
                && $post->trashed_at === null
                && $post->published_at !== null
                && $post->published_at->lte(now()),
                404
            );
        }

        return $next($request);
    }
}
