<?php

use Pterodactyl\Models\Backup;

return [
    'default' => env('APP_BACKUP_DRIVER', Backup::ADAPTER_WINGS),

    'presigned_url_lifespan' => (int) env('BACKUP_PRESIGNED_URL_LIFESPAN', 60),

    'max_part_size' => env('BACKUP_MAX_PART_SIZE', 5 * 1024 * 1024 * 1024),

    'prune_age' => env('BACKUP_PRUNE_AGE', 360),

    'throttles' => [
        'limit' => env('BACKUP_THROTTLE_LIMIT', 2),
        'period' => env('BACKUP_THROTTLE_PERIOD', 600),
    ],

    'disks' => [
        'wings' => [
            'adapter' => Backup::ADAPTER_WINGS,
        ],

        's3' => [
            'adapter' => Backup::ADAPTER_AWS_S3,

            'region' => env('AWS_DEFAULT_REGION'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),

            'bucket' => env('AWS_BACKUPS_BUCKET'),

            'prefix' => env('AWS_BACKUPS_BUCKET') ?? '',

            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'use_accelerate_endpoint' => env('AWS_BACKUPS_USE_ACCELERATE', false),

            'storage_class' => env('AWS_BACKUPS_STORAGE_CLASS'),
        ],
    ],
];
