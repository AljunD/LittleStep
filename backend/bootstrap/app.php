<?php

use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Middleware\ApiAuthenticate;
use App\Http\Middleware\TeacherMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(Authenticate::class, ApiAuthenticate::class);

        $middleware->alias([
            'auth'    => ApiAuthenticate::class,
            'teacher' => TeacherMiddleware::class, // ✅ Teacher-only middleware
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
