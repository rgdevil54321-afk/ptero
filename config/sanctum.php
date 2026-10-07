<?php

return [

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Laravel\Sanctum\Sanctum::currentApplicationUrlWithPort()
    ))),


    'guard' => ['web'],


    'expiration' => null,


    'middleware' => [
        'verify_csrf_token' => Pterodactyl\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => Pterodactyl\Http\Middleware\EncryptCookies::class,
    ],
];
