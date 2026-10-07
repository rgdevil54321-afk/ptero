<?php

use Illuminate\Support\Str;


return [

    'driver' => env('SESSION_DRIVER', 'file'),
    


    'lifetime' => env('SESSION_LIFETIME', 720),

    'expire_on_close' => false,


    'encrypt' => true,


    'files' => storage_path('framework/sessions'),


    'connection' => env('SESSION_CONNECTION', 'default'),



    'table' => 'sessions',


    'store' => env('SESSION_STORE'),


    'lottery' => [2, 100],


    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'pterodactyl'), '_') . '_session'
    ),


    'path' => '/',


    'domain' => env('SESSION_DOMAIN'),


    'secure' => env('SESSION_SECURE_COOKIE'),


    'http_only' => true,


    'same_site' => env('SESSION_SAMESITE_COOKIE', 'lax'),
];
