<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuardianMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->role === 'guardian' && $user->guardian && $user->hasVerifiedEmail(),
            403,
            'Guardian email verification is required before accessing mobile features.'
        );

        return $next($request);
    }
}
