<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();

        // Resident-submitted values stay in Inertia's in-memory form state and are
        // never copied into the server-side validation flash bag.
        $exceptions->dontFlash([
            'resident_name',
            'contact_email',
            'contact_phone',
            'general_location',
            'request_details',
            'response_details',
            'appointment_note',
            'attachments',
            'tracking_pin',
            'pin',
        ]);

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            $status = $response->getStatusCode();
            $renderableStatuses = [403, 404, 419, 429, 500, 503];

            if ($request->expectsJson()
                || ! in_array($status, $renderableStatuses, true)
                || ($status === 500 && config('app.debug'))) {
                return $response;
            }

            Inertia::flushShared();

            $errorResponse = Inertia::render('errors/show', [
                'appName' => config('app.name'),
                'status' => $status,
                'copy' => Lang::get('phase6.errors'),
            ])->toResponse($request);

            $errorResponse->setStatusCode($status);

            return SecurityHeaders::apply($request, $errorResponse);
        });
    })->create();
