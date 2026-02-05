<?php

namespace Modules\Sabores\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to add cache headers to Sabores API responses
 * Improves performance by allowing clients to cache images and data
 */
class ImageCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add cache headers to successful responses
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Cache duration in seconds (7 days = 604800 seconds)
        $cacheDuration = 604800;

        // Add cache control headers
        $response->headers->set('Cache-Control', 'public, max-age=' . $cacheDuration);

        // Add ETag for cache validation based on content
        $content = $response->getContent();
        if ($content) {
            $etag = md5($content);
            $response->headers->set('ETag', '"' . $etag . '"');

            // Check if client has a cached version
            $requestEtag = str_replace('"', '', $request->header('If-None-Match', ''));
            if ($requestEtag === $etag) {
                return response('', 304)
                    ->header('Cache-Control', 'public, max-age=' . $cacheDuration)
                    ->header('ETag', '"' . $etag . '"');
            }
        }

        // Add Last-Modified header
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');

        // Add Expires header
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $cacheDuration) . ' GMT');

        return $response;
    }
}
