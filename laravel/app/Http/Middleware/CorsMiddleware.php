<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for handling Cross-Origin Resource Sharing (CORS) headers.
 * 
 * This middleware handles CORS preflight requests and adds appropriate
 * CORS headers to responses to enable cross-origin API access from
 * frontend applications. Supports credentials and common HTTP methods.
 *
 * @package App\Http\Middleware
 */
class CorsMiddleware
{
    /**
     * Handle an incoming request and add CORS headers.
     * 
     * Handles OPTIONS preflight requests and adds CORS headers to all responses
     * to allow cross-origin requests from the configured frontend URL.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware closure
     * @return Response The HTTP response with CORS headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = response(null, 200);
            $response->headers->set('Access-Control-Allow-Origin', env('FRONTEND_URL', '*'));
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-localization, Accept, X-XSRF-TOKEN');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '86400');
            return $response;
        }

        $response = $next($request);

        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        $response->headers->set('Access-Control-Allow-Origin', env('FRONTEND_URL', '*'));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-localization, Accept, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
