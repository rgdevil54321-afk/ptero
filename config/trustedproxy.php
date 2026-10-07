<?php

return [
    'proxies' => in_array(env('TRUSTED_PROXIES', []), ['*', '**']) ?
        env('TRUSTED_PROXIES') : explode(',', env('TRUSTED_PROXIES') ?? ''),
];
