<?php

use App\Http\Middleware\ApplyActivePublicTheme;
use App\Http\Middleware\ApplyPublicLocaleDirection;
use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\EnforceApiUsageLimit;
use App\Http\Middleware\EnforceApplicationMaintenance;
use App\Http\Middleware\EnsureApiScope;
use App\Http\Middleware\EnsurePublicCommentsAvailable;
use App\Http\Middleware\EnsurePublishedPublicContent;
use App\Http\Middleware\RejectInactivePublicLocale;
use App\Http\Middleware\ResolvePublicLocale;
use App\Http\Middleware\UseFileStorageForInstaller;
use App\Http\Middleware\ValidatePublicMailboxAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('web', UseFileStorageForInstaller::class);
        $middleware->appendToGroup('web', EnforceApplicationMaintenance::class);
        $middleware->alias([
            'api.key' => AuthenticateApiKey::class,
            'api.scope' => EnsureApiScope::class,
            'api.usage' => EnforceApiUsageLimit::class,
            'public.locale' => ResolvePublicLocale::class,
            'public.locale.active' => RejectInactivePublicLocale::class,
            'public.mailbox.access' => ValidatePublicMailboxAccess::class,
            'public.theme' => ApplyActivePublicTheme::class,
            'public.direction' => ApplyPublicLocaleDirection::class,
            'public.content.published' => EnsurePublishedPublicContent::class,
            'public.comments.available' => EnsurePublicCommentsAvailable::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'data' => null,
                'meta' => ['errors' => $exception->errors()],
                'error' => ['code' => 'validation_failed', 'message' => 'The request payload could not be validated.'],
            ], 422);
        });
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'data' => null,
                'meta' => [],
                'error' => ['code' => 'not_found', 'message' => 'The requested API resource was not found.'],
            ], 404);
        });
    })->create();
