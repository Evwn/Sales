<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\BadRequestException;
use Illuminate\Http\Exceptions\UnauthorizedException;
use Illuminate\Http\Exceptions\ForbiddenException;
use Illuminate\Http\Exceptions\NotFoundHttpException;
use Illuminate\Http\Exceptions\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ExpiredException;
use Illuminate\Http\Exceptions\TooManyRequestsException;
use Illuminate\Http\Exceptions\ServerErrorException;
use Illuminate\Http\Exceptions\HttpException;
use Illuminate\Http\Exceptions\HttpExceptionInterface;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        $status = $this->isHttpException($exception)
            ? $exception->getStatusCode()
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
                'message' => $exception->getMessage(),
            ], $status);
        }

        if (isset($errorPages[$status])) {
            return Inertia::render($errorPages[$status], [
                'status' => $status,
                'message' => $exception->getMessage(),
            ])->toResponse($request)->setStatusCode($status);
        }

        return parent::render($request, $exception);
    }
} 