<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->secure() || str_starts_with((string) config('app.url'), 'https://')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $script = "'self'";
        $style = "'self'";
        $connect = "'self'";

        // The Vite dev server injects modules from a second origin during local
        // development. Allow it only when the hot file exists so a production
        // build keeps the tighter same-origin policy.
        $hotFile = public_path('hot');
        if (app()->environment('local') && is_file($hotFile)) {
            $devOrigin = trim((string) @file_get_contents($hotFile));
            if ($devOrigin !== '') {
                $script .= ' '.$devOrigin;
                $style .= ' '.$devOrigin;
                $connect .= ' '.$devOrigin.' '.str_replace(['http://', 'https://'], ['ws://', 'wss://'], $devOrigin);
            }
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "img-src 'self' data:",
            "font-src 'self'",
            'style-src '.$style,
            'script-src '.$script,
            'connect-src '.$connect,
        ]);
    }
}
