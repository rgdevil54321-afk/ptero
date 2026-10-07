<?php

use Illuminate\Support\Facades\Facade;

return [

    'version' => '1.15.1',


    'name' => env('APP_NAME', 'Hyper Game Panel'),


    'env' => env('APP_ENV', 'production'),


    'debug' => env('APP_DEBUG', false),


    'url' => env('APP_URL', 'http://localhost'),


    'timezone' => env('APP_TIMEZONE', 'UTC'),


    'locale' => env('APP_LOCALE', 'en'),


    'fallback_locale' => 'en',


    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',


    'exceptions' => [
        'report_all' => env('APP_REPORT_ALL_EXCEPTIONS', false),
    ],


    'maintenance' => [
        'driver' => 'file',
    ],


    'providers' => [
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        Pterodactyl\Providers\ActivityLogServiceProvider::class,
        Pterodactyl\Providers\AppServiceProvider::class,
        Pterodactyl\Providers\AuthServiceProvider::class,
        Pterodactyl\Providers\BackupsServiceProvider::class,
        Pterodactyl\Providers\BladeServiceProvider::class,
        Pterodactyl\Providers\EventServiceProvider::class,
        Pterodactyl\Providers\HashidsServiceProvider::class,
        Pterodactyl\Providers\RouteServiceProvider::class,
        Pterodactyl\Providers\RepositoryServiceProvider::class,
        Pterodactyl\Providers\ViewComposerServiceProvider::class,

        Prologue\Alerts\AlertsServiceProvider::class,
    ],



    'aliases' => Facade::defaultAliases()->merge([
        'Alert' => Prologue\Alerts\Facades\Alert::class,
        'Carbon' => Carbon\Carbon::class,
        'JavaScript' => Laracasts\Utilities\JavaScript\JavaScriptFacade::class,
        'Theme' => Pterodactyl\Extensions\Facades\Theme::class,

        'Activity' => Pterodactyl\Facades\Activity::class,
        'LogBatch' => Pterodactyl\Facades\LogBatch::class,
        'LogTarget' => Pterodactyl\Facades\LogTarget::class,
    ])->toArray(),
];
