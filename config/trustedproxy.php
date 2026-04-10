<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxies de confianza
    |--------------------------------------------------------------------------
    |
    | Tráfico típico: Cliente → Cloudflare (proxy naranja) → Nginx → PHP-FPM.
    | El peer directo de PHP es Nginx; con "*" Laravel confía solo en REMOTE_ADDR
    | (ese salto) y aplica X-Forwarded-For / X-Forwarded-Proto / Host correctamente.
    |
    | Valores: "*" (recomendado detrás de un solo reverse proxy), lista separada por
    | comas de IPs, o CIDRs. Ver también deploy/nginx-tootli-cloudflare.conf.example.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '*'),

];
