<?php

use App\Http\Middleware\ApiAuthenticate;
use App\Http\Middleware\GuardianMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TeacherMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(Authenticate::class, ApiAuthenticate::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->appendToGroup('api', SecurityHeaders::class);

        $middleware->alias([
            'auth' => ApiAuthenticate::class,
            'guardian' => GuardianMiddleware::class,
            'teacher' => TeacherMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Too many teacher sign-in attempts: show the warning on the login page instead of a bare 429 page.
        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            if (! $request->routeIs('auth.login.submit') || $request->expectsJson()) {
                return null;
            }

            $seconds = max(1, (int) ($exception->getHeaders()['Retry-After'] ?? 60));

            return redirect()->route('login')
                ->withErrors(['email' => "Too many sign-in attempts. Please try again in {$seconds} seconds."])
                ->with('login_alert', 'throttled')
                ->withInput($request->only('email'));
        });
    })->create();
