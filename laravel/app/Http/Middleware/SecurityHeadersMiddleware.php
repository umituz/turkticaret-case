<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and add security headers for XSS protection.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy - Strong XSS protection
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "img-src 'self' data: blob: https:; " .
            "font-src 'self' data:; " .
            "connect-src 'self'; " .
            "media-src 'self'; " .
            "object-src 'none'; " .
            "child-src 'self'; " .
            "frame-ancestors 'none'; " .
            "form-action 'self'; " .
            "upgrade-insecure-requests; " .
            "block-all-mixed-content"
        );

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // XSS Protection (legacy but still useful)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (Feature Policy replacement)
        $response->headers->set('Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        return $response;
    }
}
