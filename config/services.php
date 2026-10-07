<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'curseforge' => [
        'key' => env('CURSEFORGE_API_KEY'),
    ],

    'github' => [
        'token' => env('GITHUB_TOKEN'),
    ],

    'dgen_cloud' => [
        'app_id' => env('DGEN_CLOUD_APP_ID'),
        'public_key' => env('DGEN_CLOUD_PUBLIC_KEY'),
    ],

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
    ],

    'redis_peers' => [
        'host_1' => env('REDIS_SENTINEL_1'),
        'host_2' => env('REDIS_SENTINEL_2'),
        'host_3' => env('REDIS_SENTINEL_3'),
        'port' => (int) env('REDIS_PEER_PORT', env('REDIS_PORT', env('REDIS_SENTINEL_PORT', 6379))),
        'password' => env('REDIS_PASSWORD'),
    ],
];
