<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

/**
 * Lista de proxies: config/trustedproxy.php (env TRUSTED_PROXIES).
 */
class TrustProxies extends Middleware
{
    /**
     * Cabeceras estándar de reverse proxy (Cloudflare + Nginx). Sin AWS ELB.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX;
}
