<?php

return [

    'load_environment_only' => (bool) env('APP_ENVIRONMENT_ONLY', false),


    'service' => [
        'author' => env('APP_SERVICE_AUTHOR', 'unknown@unknown.com'),
    ],


    'auth' => [
        '2fa_required' => env('APP_2FA_REQUIRED', 0),
        '2fa' => [
            'bytes' => 32,
            'window' => env('APP_2FA_WINDOW', 4),
            'verify_newer' => true,
        ],
    ],


    'paginate' => [
        'frontend' => [
            'servers' => env('APP_PAGINATE_FRONT_SERVERS', 15),
        ],
        'admin' => [
            'servers' => env('APP_PAGINATE_ADMIN_SERVERS', 25),
            'users' => env('APP_PAGINATE_ADMIN_USERS', 25),
        ],
        'api' => [
            'nodes' => env('APP_PAGINATE_API_NODES', 25),
            'servers' => env('APP_PAGINATE_API_SERVERS', 25),
            'users' => env('APP_PAGINATE_API_USERS', 25),
        ],
    ],


    'guzzle' => [
        'timeout' => env('GUZZLE_TIMEOUT', 15),
        'connect_timeout' => env('GUZZLE_CONNECT_TIMEOUT', 5),
    ],


    'cdn' => [
        'cache_time' => 60,
        'url' => 'https://cdn.pterodactyl.io/releases/latest.json',
    ],


    'client_features' => [
        'databases' => [
            'enabled' => env('PTERODACTYL_CLIENT_DATABASES_ENABLED', true),
            'allow_random' => env('PTERODACTYL_CLIENT_DATABASES_ALLOW_RANDOM', true),
        ],

        'schedules' => [
            'per_schedule_task_limit' => env('PTERODACTYL_PER_SCHEDULE_TASK_LIMIT', 10),
        ],

        'allocations' => [
            'enabled' => env('PTERODACTYL_CLIENT_ALLOCATIONS_ENABLED', false),
            'range_start' => env('PTERODACTYL_CLIENT_ALLOCATIONS_RANGE_START'),
            'range_end' => env('PTERODACTYL_CLIENT_ALLOCATIONS_RANGE_END'),
        ],
    ],


    'files' => [
        'max_edit_size' => env('PTERODACTYL_FILES_MAX_EDIT_SIZE', 1024 * 1024 * 50),
    ],


    'environment_variables' => [
        'P_SERVER_ALLOCATION_LIMIT' => 'allocation_limit',
    ],


    'assets' => [
        'use_hash' => env('PTERODACTYL_USE_ASSET_HASH', false),
    ],


    'email' => [
        'send_install_notification' => env('PTERODACTYL_SEND_INSTALL_NOTIFICATION', true),
        'send_reinstall_notification' => env('PTERODACTYL_SEND_REINSTALL_NOTIFICATION', true),
    ],


    'telemetry' => [
        'enabled' => env('PTERODACTYL_TELEMETRY_ENABLED', true),
    ],


    'default_language' => env('PTERODACTYL_DEFAULT_LANGUAGE', 'en'),

    'reverse_proxy' => [
        'ip' => env('REVERSE_PROXY_IP'),
    ],

    'features' => [
        'new_server_identifiers' => (bool) env('PTERODACTYL_USE_SERVER_IDENTIFIERS', false),
    ],

    'wings_agent' => [
        'verify_tls' => (bool) env('WINGS_AGENT_VERIFY_TLS', false),
        'port' => env('WINGS_AGENT_PORT') === null ? null : (int) env('WINGS_AGENT_PORT'),
    ],


    'hyper_update' => [
        'panel_path' => env('HYPER_UPDATE_PANEL_PATH'),
        'use_local' => (bool) env('HYPER_UPDATE_USE_LOCAL', false),
        'local_zip' => env('HYPER_UPDATE_LOCAL_ZIP'),
        'skip_fetch' => (bool) env('HYPER_UPDATE_SKIP_FETCH', false),
        'skip_system' => (bool) env('HYPER_UPDATE_SKIP_SYSTEM', false),
        'test_mode' => (bool) env('HYPER_UPDATE_TEST_MODE', false),
        'test_remote_version' => env('HYPER_UPDATE_TEST_REMOTE_VERSION', '99.0.0'),
    ],

];
