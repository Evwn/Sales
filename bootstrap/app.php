<?php

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\UpdateOnlineStatus;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
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


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
            'auth' => Authenticate::class,
            'auth.basic' =>AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'business.access' => \App\Http\Middleware\BusinessAccess::class,
            'branch.access' => \App\Http\Middleware\CheckBranchAccess::class,
            'role.access' => \App\Http\Middleware\RoleRouteAccess::class,
            'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
            'update.online.status' => \App\Http\Middleware\UpdateOnlineStatus::class,
                // add other aliases here if needed
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
