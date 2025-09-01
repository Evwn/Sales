<?php

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\UpdateOnlineStatus;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\BusinessAccess;
use App\Http\Middleware\CheckBranchAccess;
use App\Http\Middleware\RoleRouteAccess;
use App\Http\Middleware\ValidateSignature;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Inertia\Middleware::class,
            \App\Http\Middleware\HandleAppearance::class,
            \App\Http\Middleware\UpdateOnlineStatus::class,
        ]);
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'auth' => Authenticate::class,
            'auth.basic' =>AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'business.access' => \App\Http\Middleware\BusinessAccess::class,
            'branch.access' => \App\Http\Middleware\CheckBranchAccess::class,
           // 'role.access' => \App\Http\Middleware\RoleRouteAccess::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
            'update.online.status' => \App\Http\Middleware\UpdateOnlineStatus::class,
            'pos.only' => \App\Http\Middleware\PosOnly::class,
            'backoffice.only' => \App\Http\Middleware\BackofficeOnly::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
->withExceptions(function (Exceptions $exceptions) {

    // Handle AuthenticationException
    $exceptions->render(function (AuthenticationException $e, $request) {
        \Log::info('Caught AuthenticationException (custom handler)', [
            'url' => $request->url(),
            'expectsJson' => $request->expectsJson(),
            'inertia' => $request->inertia(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($request->inertia()) {
            return Inertia::render('Error/Unauthorized', [
                'status' => 401,
                'message' => 'You are not authenticated. Please login first.',
            ])->toResponse($request)->setStatusCode(401);
        }

        // Fallback for normal requests
        return redirect()->route('home');
    });

    // Generic handler
    $exceptions->render(function (Throwable $e, $request) {
        \Log::info('Handling exception: ' . get_class($e));
        if ($e instanceof AuthenticationException) {
        return null; // skip, handled above
        }
        // Handle throttle explicitly
        if ($e instanceof ThrottleRequestsException) {
            $status = 429;
            \Log::info($status);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => $status,
                    'message' => 'Too many requests. Please slow down.',
                ], $status);
            }

            return Inertia::render('Error/TooManyRequests', [
                'status' => $status,
                'message' => 'Too many requests. Please slow down.',
            ])->toResponse($request)->setStatusCode($status);
        }

        $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
            ? $e->getStatusCode()
            : 500;

        $errorPages = [
            400 => 'Error/BadRequest',
            401 => 'Error/Unauthorized',
            403 => 'Error/Forbidden',
            404 => 'Error/NotFound',
            405 => 'Error/BadRequest',
            419 => 'Error/Expired',
            429 => 'Error/TooManyRequests',
            500 => 'Error/ServerError',
            502 => 'Error/ServerError',
            503 => 'Error/ServerError',
            504 => 'Error/ServerError',
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $status,
                'message' => $e->getMessage(),
            ], $status);
        }

        if (isset($errorPages[$status])) {
            \Log::info($status);
            return Inertia::render($errorPages[$status], [
                'status' => $status,
                'message' => $e->getMessage(),
            ])->toResponse($request)->setStatusCode($status);
        }

        // Fallback for any unhandled cases
        return response('Unexpected error', $status);
    });
})->create();

