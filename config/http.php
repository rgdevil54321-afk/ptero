<?php

return [
    'rate_limit' => [
        'client_period' => 1,
        'client' => env('APP_API_CLIENT_RATELIMIT', 720),

        'application_period' => 1,
        'application' => env('APP_API_APPLICATION_RATELIMIT', 256),
    ],
];
