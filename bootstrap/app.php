<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PDOException $exception, Request $request): ?Response {
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);
            $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());

            if (! in_array($driverCode, [2002, 2003, 2006, 2013], true) && ! str_starts_with($sqlState, '08')) {
                return null;
            }

            $headers = ['Retry-After' => '30', 'Cache-Control' => 'no-store'];

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'The workspace is temporarily unavailable. Please try again shortly.',
                ], 503, $headers);
            }

            return response()->view('errors.database-unavailable', [], 503, $headers);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
