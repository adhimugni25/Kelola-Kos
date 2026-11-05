<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Aset hasil build Vite dilayani dari origin yang sama sehingga tidak perlu
     * 'unsafe-inline' pada script-src. Hanya style-src yang dilonggarkan karena
     * sebagian pustaka menulis atribut style secara dinamis.
     */
    private function contentSecurityPolicy(): string
    {
        $vite = $this->viteDevServer();

        $scriptSrc = $vite === '' ? "'self'" : "'self' {$vite}";
        $connectSrc = $vite === '' ? "'self'" : "'self' {$vite} ws://localhost:5173 ws://127.0.0.1:5173";

        return implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "connect-src {$connectSrc}",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);
    }

    private function viteDevServer(): string
    {
        if (! app()->environment('local') || ! file_exists(public_path('hot'))) {
            return '';
        }

        return 'http://localhost:5173 http://127.0.0.1:5173';
    }
}
