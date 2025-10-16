<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Define os proxies confiáveis e os cabeçalhos usados para detectar o host.
    | Isso é essencial quando você usa Cloudflare, Nginx, Load Balancer ou App Platform.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '*'),

    'headers' => Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                 Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                 Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                 Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
];
