<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Compress JSON API responses with Gzip to reduce payload size.
 * Typically reduces JSON response size by 60-80%.
 */
class CompressResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only compress if client accepts gzip and response is large enough
        if (
            !$request->header('Accept-Encoding') ||
            !str_contains($request->header('Accept-Encoding'), 'gzip')
        ) {
            return $response;
        }

        $content = $response->getContent();

        // Only compress responses larger than 1KB
        if (strlen($content) < 1024) {
            return $response;
        }

        $compressed = gzencode($content, 6);

        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', strlen($compressed));
        $response->headers->remove('Content-Length'); // Let the server recalculate

        return $response;
    }
}
