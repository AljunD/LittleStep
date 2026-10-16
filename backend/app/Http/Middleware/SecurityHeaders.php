<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->isSecure()) {
            $httpsBaseUrl = preg_replace('/^http:/i', 'https:', rtrim(config('app.url'), '/'));

            return redirect()->away($httpsBaseUrl.$request->getRequestUri(), 308);
        }

        $nonce = base64_encode(random_bytes(18));
        $request->attributes->set('cspNonce', $nonce);

        $scriptSources = "'self' 'nonce-{$nonce}'";
        $styleSources = "'self' 'nonce-{$nonce}' https://cdnjs.cloudflare.com https://fonts.googleapis.com";
        $fontSources = "'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com";
        $connectSources = "'self'";
        if (app()->environment(['local', 'testing'])) {
            $scriptSources .= ' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173';
            $styleSources .= ' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173';
            $fontSources .= ' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173';
            $connectSources .= ' ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173';
        }

        $response = $next($request);
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src {$scriptSources}; style-src {$styleSources}; style-src-attr 'unsafe-inline'; img-src 'self' data: blob:; font-src {$fontSources}; connect-src {$connectSources}; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
        );
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
