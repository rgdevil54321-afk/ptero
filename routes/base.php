<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController;
use Pterodactyl\Http\Controllers\Base;
use Pterodactyl\Http\Middleware\CaptureUserActivity;
use Pterodactyl\Http\Middleware\EncryptCookies;
use Pterodactyl\Http\Middleware\LanguageMiddleware;
use Pterodactyl\Http\Middleware\RequireTwoFactorAuthentication;
use Pterodactyl\Http\Middleware\VerifyCsrfToken;

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

Route::get('/api/public/eggs', [\Pterodactyl\Http\Controllers\Api\PublicEggController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->middleware('throttle:30,1')
    ->name('api.public.eggs');

Route::get('/theme/hyperv2', [Base\HyperV2ThemePublicController::class, 'show'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->middleware('throttle:60,1');

Route::get('/hyper-media/{scope}/{hash}/{file}', [Base\HyperV2MediaController::class, 'show'])
    ->where([
        'scope' => '[A-Za-z0-9_-]+',
        'hash' => '[A-Fa-f0-9]{40,64}',
        'file' => '[A-Za-z0-9_.-]+',
    ])
    ->withoutMiddleware([
        'auth',
        RequireTwoFactorAuthentication::class,
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        LanguageMiddleware::class,
        CaptureUserActivity::class,
    ])
    ->middleware('throttle:240,1');

Route::get('/language/available', [Base\LanguageController::class, 'available'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);
Route::patch('/language', [Base\LanguageController::class, 'set'])
    ->name('language.set');

Route::get('/referral/{code}', [Pterodactyl\Http\Controllers\Auth\ReferralController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);

Route::get('/status', [Base\PublicStatusPageController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->middleware('throttle:30,1')
    ->name('public.status');

Route::get('/public/stats', [Base\PublicStatsController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->middleware('throttle:30,1')
    ->name('public.stats');

Route::get('/health', [Base\HealthController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->middleware('throttle:10,1')
    ->name('health');

Route::get('/docs/{path?}', [Base\DocumentationController::class, 'show'])
    ->where('path', '.*')
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class]);

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|daemon|health)).+');

Route::post('/api/public/litepay/webhook', [BillingController::class, 'litepayWebhook'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->name('api.public.litepay.webhook');

Route::post('/api/public/smepay/webhook', [BillingController::class, 'smepayWebhook'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->name('api.public.smepay.webhook');

/*
|--------------------------------------------------------------------------
| Playit.gg node bridge + egg claim endpoints (Turtle addon)
|--------------------------------------------------------------------------
| Stateless, token-protected (X-Playit-Token). The egg install script calls
| /claim during installation; a node bridge calls /report to publish the
| address that playit assigned.
*/
Route::post('/api/public/playit/report', [\Turtle\Playit\Http\Controllers\Api\PlayitPublicController::class, 'report'])
    ->withoutMiddleware([
        'auth',
        RequireTwoFactorAuthentication::class,
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        LanguageMiddleware::class,
        CaptureUserActivity::class,
    ])
    ->middleware('throttle:120,1')
    ->name('api.public.playit.report');

Route::get('/api/public/playit/claim', [\Turtle\Playit\Http\Controllers\Api\PlayitPublicController::class, 'claim'])
    ->withoutMiddleware([
        'auth',
        RequireTwoFactorAuthentication::class,
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        LanguageMiddleware::class,
        CaptureUserActivity::class,
    ])
    ->middleware('throttle:60,1')
    ->name('api.public.playit.claim');

Route::post('/api/public/playit/sync', [\Turtle\Playit\Http\Controllers\Api\PlayitPublicController::class, 'sync'])
    ->withoutMiddleware([
        'auth',
        RequireTwoFactorAuthentication::class,
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        LanguageMiddleware::class,
        CaptureUserActivity::class,
    ])
    ->middleware('throttle:12,1')
    ->name('api.public.playit.sync');
