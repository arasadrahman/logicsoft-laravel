<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => $e->getMessage(),
                        "exception" => get_class($e),
                        "file" => $e->getFile(),
                        "line" => $e->getLine(),
                        "trace" => config("app.debug") ? $e->getTrace() : [],
                    ],
                    $request->get->status ?? 500,
                );
            }
        });
    })
    ->create();
