<?php

use Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\FiveMUtilsController;

use Pterodactyl\Enum\ResourceLimit;
use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Api\Client\DGEN\WingsAddonController;
use Pterodactyl\Http\Controllers\Api\Client;
use Pterodactyl\Http\Middleware\Activity\ServerSubject;
use Pterodactyl\Http\Middleware\Activity\AccountSubject;
use Pterodactyl\Http\Middleware\RequireTwoFactorAuthentication;
use Pterodactyl\Http\Middleware\Api\Client\Server\ResourceBelongsToServer;
use Pterodactyl\Http\Middleware\Api\Client\Server\AuthenticateServerAccess;
use Pterodactyl\Http\Middleware\Api\Client\Server\EnsureDiscordMembership;
use Pterodactyl\Http\Middleware\EnforceHyperV2PanelAccess;
use Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\NodeStatusController;
use Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\CustomMonitorController;
use Pterodactyl\Http\Controllers\Api\Client\DGEN\LoginActivityController;
use Pterodactyl\Http\Controllers\Api\Client\DGEN\DdosAlertController;

Route::get('/', [Client\ClientController::class, 'index'])->name('api:client.index');
Route::get('/permissions', [Client\ClientController::class, 'permissions']);

Route::get('/public/node-status', [Pterodactyl\Http\Controllers\Base\PublicNodeStatusController::class, 'index'])
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class, 'client-api']);

Route::prefix('/account')->middleware(AccountSubject::class)->group(function () {
    Route::prefix('/')->withoutMiddleware(RequireTwoFactorAuthentication::class)->group(function () {
        Route::get('/', [Client\AccountController::class, 'index'])->name('api:client.account');
        Route::get('/two-factor', [Client\TwoFactorController::class, 'index']);
        Route::post('/two-factor', [Client\TwoFactorController::class, 'store']);
        Route::post('/two-factor/disable', [Client\TwoFactorController::class, 'delete']);
    });

    Route::put('/info', [Client\AccountController::class, 'updateAccountInfo'])->name('api:client.account.update-info');
    Route::put('/email', [Client\AccountController::class, 'updateEmail'])
        ->middleware('throttle')
        ->name('api:client.account.update-email');
    Route::put('/password', [Client\AccountController::class, 'updatePassword'])->name('api:client.account.update-password');

    Route::get('/activity', Client\ActivityLogController::class)->name('api:client.account.activity');

    Route::get('/api-keys', [Client\ApiKeyController::class, 'index']);
    Route::post('/api-keys', [Client\ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{identifier}', [Client\ApiKeyController::class, 'delete']);

    Route::prefix('/ssh-keys')->group(function () {
        Route::get('/', [Client\SSHKeyController::class, 'index']);
        Route::post('/', [Client\SSHKeyController::class, 'store']);
        Route::post('/remove', [Client\SSHKeyController::class, 'delete']);
    });

    Route::group(['prefix' => '/login-activity'], function () {
        Route::get('/', [Client\DGEN\LoginActivityController::class, 'index']);
        Route::post('/revoke/{sessionId}', [Client\DGEN\LoginActivityController::class, 'revoke']);
    });
});

Route::group(['prefix' => '/theme'], function () {
    Route::get('/hyperv2', [Client\Theme\HyperV2ThemeController::class, 'show']);
    Route::put('/hyperv2', [Client\Theme\HyperV2ThemeController::class, 'update']);
    Route::get('/hyperv2/media', [Client\Theme\HyperV2MediaController::class, 'index']);
    Route::post('/hyperv2/media', [Client\Theme\HyperV2MediaController::class, 'store']);
    Route::patch('/hyperv2/media/{hash}', [Client\Theme\HyperV2MediaController::class, 'update'])
        ->where('hash', '[A-Fa-f0-9]{40,64}');
    Route::delete('/hyperv2/media/{hash}', [Client\Theme\HyperV2MediaController::class, 'destroy'])
        ->where('hash', '[A-Fa-f0-9]{40,64}');
    Route::get('/hyperv2/version', [Client\Theme\HyperV2ThemeController::class, 'checkVersion']);
    Route::post('/hyperv2/update', [Client\Theme\HyperV2ThemeController::class, 'startUpdate']);
    Route::get('/hyperv2/update/status', [Client\Theme\HyperV2ThemeController::class, 'getUpdateStatus']);
    Route::get('/hyperv2/sidebar', [Client\Theme\HyperV2ThemeController::class, 'getAvailableSidebarItems']);
    Route::post('/hyperv2/sso/exchange', [Client\Theme\HyperV2ThemeController::class, 'ssoExchange']);
    Route::get('/hyperv2/sso/info', [Client\Theme\HyperV2ThemeController::class, 'ssoInfo']);
    Route::post('/hyperv2/sso/disconnect', [Client\Theme\HyperV2ThemeController::class, 'ssoDisconnect']);
});

Route::group(['prefix' => '/discord-verification'], function () {
    Route::get('/', [Client\DiscordVerificationController::class, 'check']);
    Route::post('/refresh', [Client\DiscordVerificationController::class, 'refresh'])->middleware('throttle:5,1');
    Route::get('/account', [Client\DiscordVerificationController::class, 'accountCheck']);
    Route::post('/account/refresh', [Client\DiscordVerificationController::class, 'accountRefresh'])->middleware('throttle:5,1');
});

Route::group(['prefix' => '/addons'], function () {
    Route::get('/', [Client\Theme\HyperV2AddonController::class, 'show']);
    Route::get('/defaults', [Client\Theme\HyperV2AddonController::class, 'defaults']);
    Route::put('/', [Client\Theme\HyperV2AddonController::class, 'update']);
    Route::get('/export-raw', [Client\Theme\HyperV2AddonController::class, 'exportRaw']);
    Route::get('/check-server-availability', [Client\Theme\HyperV2AddonController::class, 'checkServerAvailability']);
    
    Route::post('/subdomain-manager/test-connection', [Client\Servers\DGEN\SubdomainManagerController::class, 'testConnection']);
    Route::post('/subdomain-manager/fetch-domains', [Client\Servers\DGEN\SubdomainManagerController::class, 'fetchDomains']);
    Route::get('/subdomain-manager/fetch-all-subdomains', [Client\Servers\DGEN\SubdomainManagerController::class, 'fetchAllSubdomains']);
    Route::delete('/subdomain-manager/subdomains/{id}', [Client\Servers\DGEN\SubdomainManagerController::class, 'deleteSubdomainAdmin']);

    Route::get('/custom-mod-manager/mods', [Client\Servers\DGEN\CustomModManagerController::class, 'index']);
    Route::post('/custom-mod-manager/mods', [Client\Servers\DGEN\CustomModManagerController::class, 'store']);
    Route::put('/custom-mod-manager/mods/{id}', [Client\Servers\DGEN\CustomModManagerController::class, 'update']);
    Route::delete('/custom-mod-manager/mods/{id}', [Client\Servers\DGEN\CustomModManagerController::class, 'destroy']);

    Route::group(['prefix' => '/discord-bot'], function () {
        Route::get('/stats', [Client\Admin\DGEN\DiscordBotController::class, 'stats']);
        Route::post('/sync', [Client\Admin\DGEN\DiscordBotController::class, 'triggerSync']);
        Route::get('/bot-status', [Client\Admin\DGEN\DiscordBotController::class, 'botStatus']);
        Route::post('/restart', [Client\Admin\DGEN\DiscordBotController::class, 'restartBot']);
    });

    Route::group(['prefix' => '/wings'], function () {
        Route::post('/check-status', [WingsAddonController::class, 'checkStatus'])->middleware('throttle:30,1');
    });

})->withoutMiddleware(['client-api'])->middleware(EnforceHyperV2PanelAccess::class);

Route::get('admin/addons/server-type-changer/all-nests-eggs', [Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\ServerTypeChangerController::class, 'getAllNestsAndEggs'])
    ->withoutMiddleware(['client-api'])
    ->middleware(EnforceHyperV2PanelAccess::class);

Route::group(['prefix' => '/addons/server-importer'], function () {
    Route::post('/test-connection', [Client\Servers\DGEN\ServerImporterController::class, 'testConnection']);
    Route::get('/imports', [Client\Servers\DGEN\ServerImporterController::class, 'userImports']);
});

Route::group(['prefix' => '/addons/upload-from-url'], function () {
    Route::post('/query', [Client\Servers\DGEN\UploadFromUrlController::class, 'query']);
});

Route::group(['prefix' => '/addons/github-source-control'], function () {
    Route::get('/account', [Client\Servers\DGEN\GithubSourceControlController::class, 'account']);
    Route::post('/account', [Client\Servers\DGEN\GithubSourceControlController::class, 'connect']);
    Route::delete('/account', [Client\Servers\DGEN\GithubSourceControlController::class, 'disconnect']);
    Route::get('/repositories', [Client\Servers\DGEN\GithubSourceControlController::class, 'repositories']);
});

Route::group(['prefix' => 'addons'], function () {
    Route::get('/node-status', [NodeStatusController::class, 'index']);
    Route::get('/node-status/monitors', [CustomMonitorController::class, 'index']);
    Route::post('/node-status/monitors', [CustomMonitorController::class, 'store']);
    Route::put('/node-status/monitors/{id}', [CustomMonitorController::class, 'update']);
    Route::delete('/node-status/monitors/{id}', [CustomMonitorController::class, 'destroy']);
    Route::get('/login-activity', [LoginActivityController::class, 'index']);
    Route::post('/login-activity/revoke', [LoginActivityController::class, 'revoke']);

    Route::post('/DGEN/server-stats', [Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\ServerStatsController::class, 'batch']);

    Route::group(['prefix' => '/billing'], function () {
        Route::get('/balance', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getBalance']);
        Route::post('/top-up', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'initiateTopUp']);
        Route::post('/verify', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'verifyTransaction']);
        Route::get('/transactions', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getTransactions']);
        
        Route::get('/admin/stats', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getAdminStats']);
        Route::get('/admin/transactions', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getAllTransactions']);
        Route::get('/admin/users-with-credits', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getUsersWithCredits']);
        Route::get('/admin/referrals', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getReferralOverview']);
        
        Route::get('/store/categories', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\StoreController::class, 'index']);
        Route::get('/store/nodes', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\StoreController::class, 'getNodes']);
        Route::get('/store/categories/{shortUrl}', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\StoreController::class, 'showCategory']);
        
        Route::post('/order/create', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\OrderController::class, 'createServer']);
        Route::post('/order/renew/{serverUuid}', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\OrderController::class, 'renewServer']);
        Route::post('/services/{serverUuid}/auto-renew', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\OrderController::class, 'updateAutoRenew']);
        Route::post('/services/{serverUuid}/cancel', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\OrderController::class, 'cancelServer']);
        Route::post('/services/{serverUuid}/reactivate', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\OrderController::class, 'reactivateServer']);
        Route::get('/services', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\OrderController::class, 'getServices']);
        Route::post('/promocodes/validate', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\PromoCodeController::class, 'validateCode']);
        Route::get('/discount', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getDiscount']);

        Route::get('/referral', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'getReferralInfo']);
        Route::post('/referral/code', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'updateReferralCode']);
        Route::post('/referral/withdraw', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\BillingController::class, 'withdrawReferralBalance']);
    });

    Route::group(['prefix' => '/ddos-alert'], function () {
        Route::get('/summary', [DdosAlertController::class, 'summary']);
        Route::get('/attacks', [DdosAlertController::class, 'attacks']);
        Route::get('/charts', [DdosAlertController::class, 'charts']);
        Route::post('/sync-now', [DdosAlertController::class, 'syncNow']);
    });
});



Route::group(['prefix' => '/addons/staff-request'], function () {
    Route::get('/requests', [Client\Servers\DGEN\StaffRequestController::class, 'index']);
    Route::get('/requests/count', [Client\Servers\DGEN\StaffRequestController::class, 'count']);
    Route::get('/owner-requests', [Client\Servers\DGEN\StaffRequestController::class, 'ownerRequests']);
    Route::post('/requests', [Client\Servers\DGEN\StaffRequestController::class, 'store']);
    Route::post('/requests/{staffRequest}/accept', [Client\Servers\DGEN\StaffRequestController::class, 'accept']);
    Route::post('/requests/{staffRequest}/reject', [Client\Servers\DGEN\StaffRequestController::class, 'reject']);
    Route::delete('/requests/{staffRequest}', [Client\Servers\DGEN\StaffRequestController::class, 'destroy']);
    Route::post('/auto-reject', [Client\Servers\DGEN\StaffRequestController::class, 'autoReject']);
    Route::get('/servers', [Client\Servers\DGEN\StaffRequestController::class, 'searchServers']);
    Route::get('/my-servers', [Client\Servers\DGEN\StaffRequestController::class, 'myServers']);
});

Route::get('/admin/users/search', [Client\AdminUserSearchController::class, 'search'])
    ->withoutMiddleware(['client-api'])
    ->middleware(EnforceHyperV2PanelAccess::class);

Route::group(['prefix' => '/pwa'], function () {
    Route::get('/manifest.json', [Client\Theme\PwaController::class, 'manifest']);
    Route::get('/sw-config.js', [Client\Theme\PwaController::class, 'serviceWorkerConfig']);
})->withoutMiddleware(['client-api', RequireTwoFactorAuthentication::class]);

Route::group([
    'prefix' => '/servers/{server}',
    'middleware' => [
        ServerSubject::class,
        AuthenticateServerAccess::class,
        ResourceBelongsToServer::class,
        EnsureDiscordMembership::class,
    ],
], function () {
    Route::get('/', [Client\Servers\ServerController::class, 'index'])->name('api:client:server.view');
    Route::middleware([ResourceLimit::Websocket->middleware()])
        ->get('/websocket', Client\Servers\WebsocketController::class)
        ->name('api:client:server.ws');
    Route::get('/resources', Client\Servers\ResourceUtilizationController::class)->name('api:client:server.resources');
    Route::get('/activity', Client\Servers\ActivityLogController::class)->name('api:client:server.activity');

    Route::get('/addons/custom-mod-manager/mods', [Client\Servers\DGEN\CustomModManagerController::class, 'listForServer']);
    Route::post('/addons/custom-mod-manager/install', [Client\Servers\DGEN\CustomModManagerController::class, 'install']);
    Route::get('/addons/custom-mod-manager/progress', [Client\Servers\DGEN\CustomModManagerController::class, 'getProgress']);

    Route::group(['prefix' => '/addons/github-source-control'], function () {
        Route::get('/status', [Client\Servers\DGEN\GithubSourceControlController::class, 'status']);
        Route::get('/branches', [Client\Servers\DGEN\GithubSourceControlController::class, 'branches']);
        Route::get('/commits', [Client\Servers\DGEN\GithubSourceControlController::class, 'commits']);
        Route::post('/diff', [Client\Servers\DGEN\GithubSourceControlController::class, 'diff']);
        Route::post('/clone', [Client\Servers\DGEN\GithubSourceControlController::class, 'clone']);
        Route::post('/fetch', [Client\Servers\DGEN\GithubSourceControlController::class, 'fetch']);
        Route::post('/pull', [Client\Servers\DGEN\GithubSourceControlController::class, 'pull']);
        Route::post('/push', [Client\Servers\DGEN\GithubSourceControlController::class, 'push']);
        Route::post('/stage', [Client\Servers\DGEN\GithubSourceControlController::class, 'stage']);
        Route::post('/unstage', [Client\Servers\DGEN\GithubSourceControlController::class, 'unstage']);
        Route::post('/discard', [Client\Servers\DGEN\GithubSourceControlController::class, 'discard']);
        Route::post('/commit', [Client\Servers\DGEN\GithubSourceControlController::class, 'commit']);
    });

    Route::group(['prefix' => '/addons/server-time-changer'], function () {
        Route::get('/status', [\Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\ServerTimeChangerController::class, 'status'])
            ->name('api:client:server.addons.server-time-changer.status');
        Route::post('/set', [\Pterodactyl\Http\Controllers\Api\Client\Servers\DGEN\ServerTimeChangerController::class, 'setTimezone'])
            ->name('api:client:server.addons.server-time-changer.set');
    });

    Route::get('/minecraft/player-count', [Client\Servers\DGEN\MinecraftController::class, 'getPlayerCount'])->name('api:client:server.minecraft.player-count');
    Route::group(['prefix' => '/minecraft'], function () {
        Route::get('/configuration', [Client\Servers\DGEN\MinecraftController::class, 'getConfiguration'])->name('api:client:server.minecraft.configuration');
        Route::get('/icon', [Client\Servers\DGEN\MinecraftController::class, 'getIcon'])->name('api:client:server.minecraft.icon.get');
        Route::post('/icon', [Client\Servers\DGEN\MinecraftController::class, 'uploadIcon'])->name('api:client:server.minecraft.icon.upload');
        Route::delete('/icon', [Client\Servers\DGEN\MinecraftController::class, 'deleteIcon'])->name('api:client:server.minecraft.icon.delete');
        Route::get('/motd', [Client\Servers\DGEN\MinecraftController::class, 'getMotd'])->name('api:client:server.minecraft.motd.get');
        Route::put('/motd', [Client\Servers\DGEN\MinecraftController::class, 'updateMotd'])->name('api:client:server.minecraft.motd.update');
        Route::get('/properties', [Client\Servers\DGEN\MinecraftController::class, 'getProperties'])->name('api:client:server.minecraft.properties.get');
        Route::put('/properties', [Client\Servers\DGEN\MinecraftController::class, 'updateProperties'])->name('api:client:server.minecraft.properties.update');
        Route::get('/config', [Client\Servers\DGEN\MinecraftController::class, 'getConfig'])->name('api:client:server.minecraft.config.get');
        Route::put('/config', [Client\Servers\DGEN\MinecraftController::class, 'updateConfig'])->name('api:client:server.minecraft.config.update');
        Route::get('/files', [Client\Servers\DGEN\MinecraftController::class, 'listYamlFiles'])->name('api:client:server.minecraft.files.get');
        Route::get('/yaml', [Client\Servers\DGEN\MinecraftController::class, 'getYamlFile'])->name('api:client:server.minecraft.yaml.get');
        Route::put('/yaml', [Client\Servers\DGEN\MinecraftController::class, 'updateYamlFile'])->name('api:client:server.minecraft.yaml.update');
        Route::get('/debug-scan', [Client\Servers\DGEN\MinecraftController::class, 'debugDirectoryScan'])->name('api:client:server.minecraft.debug-scan.get');
        
        Route::get('/version-changer/types', [Client\Servers\DGEN\MinecraftVersionController::class, 'getServerTypes'])->name('api:client:server.minecraft.version-changer.types');
        Route::get('/version-changer/versions/{type}', [Client\Servers\DGEN\MinecraftVersionController::class, 'getVersions'])->name('api:client:server.minecraft.version-changer.versions');
        Route::get('/version-changer/builds/{type}/{version}', [Client\Servers\DGEN\MinecraftVersionController::class, 'getBuilds'])->name('api:client:server.minecraft.version-changer.builds');
        Route::post('/version-changer/change', [Client\Servers\DGEN\MinecraftVersionController::class, 'changeVersion'])->name('api:client:server.minecraft.version-changer.change');
        Route::get('/version-changer/progress', [Client\Servers\DGEN\MinecraftVersionController::class, 'getProgress'])->name('api:client:server.minecraft.version-changer.progress');
        
        Route::get('/plugin-installer/installed', [Client\Servers\DGEN\MinecraftPluginController::class, 'getInstalledPlugins'])->name('api:client:server.minecraft.plugin-installer.installed');
        Route::post('/plugin-installer/install', [Client\Servers\DGEN\MinecraftPluginController::class, 'installPlugin'])->name('api:client:server.minecraft.plugin-installer.install');
        Route::delete('/plugin-installer/uninstall', [Client\Servers\DGEN\MinecraftPluginController::class, 'uninstallPlugin'])->name('api:client:server.minecraft.plugin-installer.uninstall');
        Route::get('/plugin-installer/progress', [Client\Servers\DGEN\MinecraftPluginController::class, 'getProgress'])->name('api:client:server.minecraft.plugin-installer.progress');
        Route::get('/plugin-installer/versions/{provider}/{pluginId}', [Client\Servers\DGEN\MinecraftPluginController::class, 'getPluginVersions'])->name('api:client:server.minecraft.plugin-installer.versions');
        Route::get('/plugin-installer/details/{provider}/{pluginId}', [Client\Servers\DGEN\MinecraftPluginController::class, 'getPluginDetails'])->name('api:client:server.minecraft.plugin-installer.details');
        Route::get('/plugin-installer/icon/{provider}/{iconPath}', [Client\Servers\DGEN\MinecraftPluginController::class, 'getPluginIcon'])
            ->where('iconPath', '.*')
            ->name('api:client:server.minecraft.plugin-installer.icon');


        Route::get('/mod-installer/check-availability', [Client\Servers\DGEN\MinecraftModController::class, 'checkAddonAvailability'])->name('api:client:server.minecraft.mod-installer.check-availability');

        Route::get('/plugin-installer/cache', [Client\Servers\DGEN\MinecraftPluginCacheController::class, 'getCachedPlugins'])->name('api:client:server.minecraft.plugin-installer.cache.get');
        Route::post('/plugin-installer/cache', [Client\Servers\DGEN\MinecraftPluginCacheController::class, 'cachePluginData'])->name('api:client:server.minecraft.plugin-installer.cache.post');
        Route::delete('/plugin-installer/cache', [Client\Servers\DGEN\MinecraftPluginCacheController::class, 'clearCache'])->name('api:client:server.minecraft.plugin-installer.cache.clear');
        Route::get('/plugin-installer/cache/status', [Client\Servers\DGEN\MinecraftPluginCacheController::class, 'getCacheStatus'])->name('api:client:server.minecraft.plugin-installer.cache.status');
        Route::get('/plugin-installer/game-versions', [Client\Servers\DGEN\MinecraftPluginCacheController::class, 'getGameVersions'])->name('api:client:server.minecraft.plugin-installer.game-versions');

        Route::get('/mod-installer/installed', [Client\Servers\DGEN\MinecraftModController::class, 'getInstalledMods'])->name('api:client:server.minecraft.mod-installer.installed');
        Route::post('/mod-installer/install', [Client\Servers\DGEN\MinecraftModController::class, 'installMod'])->name('api:client:server.minecraft.mod-installer.install');
        Route::delete('/mod-installer/uninstall', [Client\Servers\DGEN\MinecraftModController::class, 'uninstallMod'])->name('api:client:server.minecraft.mod-installer.uninstall');
        Route::get('/mod-installer/progress', [Client\Servers\DGEN\MinecraftModController::class, 'getProgress'])->name('api:client:server.minecraft.mod-installer.progress');
        Route::get('/mod-installer/versions/{provider}/{modId}', [Client\Servers\DGEN\MinecraftModController::class, 'getModVersions'])->name('api:client:server.minecraft.mod-installer.versions');
        Route::get('/mod-installer/cache', [Client\Servers\DGEN\MinecraftModCacheController::class, 'getCachedMods'])->name('api:client:server.minecraft.mod-installer.cache.get');
        Route::post('/mod-installer/cache', [Client\Servers\DGEN\MinecraftModCacheController::class, 'cacheModData'])->name('api:client:server.minecraft.mod-installer.cache.post');
        Route::delete('/mod-installer/cache', [Client\Servers\DGEN\MinecraftModCacheController::class, 'clearCache'])->name('api:client:server.minecraft.mod-installer.cache.clear');
        Route::get('/mod-installer/cache/status', [Client\Servers\DGEN\MinecraftModCacheController::class, 'getCacheStatus'])->name('api:client:server.minecraft.mod-installer.cache.status');
        Route::get('/mod-installer/game-versions', [Client\Servers\DGEN\MinecraftModCacheController::class, 'getGameVersions'])->name('api:client:server.minecraft.mod-installer.game-versions');

        Route::get('/mod-installer/icon/{provider}/{iconPath}', [Client\Servers\DGEN\MinecraftModController::class, 'getModIcon'])
            ->where('iconPath', '.*')
            ->name('api:client:server.minecraft.mod-installer.icon');

        Route::get('/modpack-installer/installed', [Client\Servers\DGEN\MinecraftModpackController::class, 'getInstalledModpacks'])->name('api:client:server.minecraft.modpack-installer.installed');
        Route::post('/modpack-installer/install', [Client\Servers\DGEN\MinecraftModpackController::class, 'installModpack'])->name('api:client:server.minecraft.modpack-installer.install');
        Route::delete('/modpack-installer/uninstall', [Client\Servers\DGEN\MinecraftModpackController::class, 'uninstallModpack'])->name('api:client:server.minecraft.modpack-installer.uninstall');
        Route::get('/modpack-installer/progress', [Client\Servers\DGEN\MinecraftModpackController::class, 'getProgress'])->name('api:client:server.minecraft.modpack-installer.progress');
        Route::get('/modpack-installer/versions/{provider}/{modpackId}', [Client\Servers\DGEN\MinecraftModpackController::class, 'getModpackVersions'])->name('api:client:server.minecraft.modpack-installer.versions');
        Route::post('/modpack-installer/restore', [Client\Servers\DGEN\MinecraftModpackController::class, 'restoreModpackServer'])->name('api:client:server.minecraft.modpack-installer.restore');
        Route::get('/modpack-installer/status', [Client\Servers\DGEN\MinecraftModpackController::class, 'getModpackInstallStatus'])->name('api:client:server.minecraft.modpack-installer.status');
        Route::get('/modpack-installer/check-addon-availability', [Client\Servers\DGEN\MinecraftModpackController::class, 'checkAddonAvailability'])->name('api:client:server.minecraft.modpack-installer.check-addon-availability');
        Route::get('/modpack-installer/cache', [Client\Servers\DGEN\MinecraftModpackCacheController::class, 'getCachedModpacks'])->name('api:client:server.minecraft.modpack-installer.cache.get');
        Route::get('/modpack-installer/game-versions', [Client\Servers\DGEN\MinecraftModpackCacheController::class, 'getGameVersions'])->name('api:client:server.minecraft.modpack-installer.game-versions');
        Route::post('/modpack-installer/cache', [Client\Servers\DGEN\MinecraftModpackCacheController::class, 'cacheModpackData'])->name('api:client:server.minecraft.modpack-installer.cache.post');
        Route::get('/modpack-installer/modpack/{modpack}/versions', [Client\Servers\DGEN\MinecraftModpackCacheController::class, 'getModpackVersions'])->name('api:client:server.minecraft.modpack-installer.modpack.versions');
        Route::delete('/modpack-installer/cache', [Client\Servers\DGEN\MinecraftModpackCacheController::class, 'clearCache'])->name('api:client:server.minecraft.modpack-installer.cache.clear');
        Route::get('/modpack-installer/cache/status', [Client\Servers\DGEN\MinecraftModpackCacheController::class, 'getCacheStatus'])->name('api:client:server.minecraft.modpack-installer.cache.status');

        Route::get('/modpack-installer/icon/{provider}/{iconPath}', [Client\Servers\DGEN\MinecraftModpackController::class, 'getModpackIcon'])
            ->where('iconPath', '.*')
            ->name('api:client:server.minecraft.modpack-installer.icon');

        Route::get('/world-manager/installed', [Client\Servers\DGEN\MinecraftWorldController::class, 'getInstalledWorlds'])->name('api:client:server.minecraft.world-manager.installed');
        Route::post('/world-manager/install', [Client\Servers\DGEN\MinecraftWorldController::class, 'installWorld'])->name('api:client:server.minecraft.world-manager.install');
        Route::delete('/world-manager/uninstall', [Client\Servers\DGEN\MinecraftWorldController::class, 'uninstallWorld'])->name('api:client:server.minecraft.world-manager.uninstall');
        Route::get('/world-manager/progress', [Client\Servers\DGEN\MinecraftWorldController::class, 'getProgress'])->name('api:client:server.minecraft.world-manager.progress');
        Route::get('/world-manager/inspect', [Client\Servers\DGEN\MinecraftWorldController::class, 'inspectServer'])->name('api:client:server.minecraft.world-manager.inspect');

        Route::get('/world-manager/level-name', [Client\Servers\DGEN\MinecraftWorldController::class, 'getLevelName'])->name('api:client:server.minecraft.world-manager.level-name.get');
        Route::post('/world-manager/level-name', [Client\Servers\DGEN\MinecraftWorldController::class, 'updateLevelName'])->name('api:client:server.minecraft.world-manager.level-name.update');

        Route::get('/world-manager/cache', [Client\Servers\DGEN\MinecraftWorldCacheController::class, 'getCachedWorlds'])->name('api:client:server.minecraft.world-manager.cache.get');
        Route::post('/world-manager/cache', [Client\Servers\DGEN\MinecraftWorldCacheController::class, 'cacheWorldData'])->name('api:client:server.minecraft.world-manager.cache.post');
        Route::delete('/world-manager/cache', [Client\Servers\DGEN\MinecraftWorldCacheController::class, 'clearCache'])->name('api:client:server.minecraft.world-manager.cache.clear');
        Route::get('/world-manager/cache/status', [Client\Servers\DGEN\MinecraftWorldCacheController::class, 'getCacheStatus'])->name('api:client:server.minecraft.world-manager.cache.status');
        Route::get('/world-manager/game-versions', [Client\Servers\DGEN\MinecraftWorldCacheController::class, 'getGameVersions'])->name('api:client:server.minecraft.world-manager.game-versions');
        
        Route::get('/world-manager/versions/{worldId}', [Client\Servers\DGEN\MinecraftWorldController::class, 'getWorldVersions'])->name('api:client:server.minecraft.world-manager.versions');
        
        Route::get('/world-manager/icon/{avatarPath}', [Client\Servers\DGEN\MinecraftWorldController::class, 'getWorldIcon'])
            ->where('avatarPath', '.*')
            ->name('api:client:server.minecraft.world-manager.icon');

        Route::get('/world-manager/check-addon-availability', [Client\Servers\DGEN\MinecraftWorldController::class, 'checkAddonAvailability'])->name('api:client:server.minecraft.world-manager.check-addon-availability');

        Route::get('/bedrock-addon-installer/installed', [Client\Servers\DGEN\MinecraftBedrockAddonController::class, 'getInstalledAddons'])->name('api:client:server.minecraft.bedrock-addon-installer.installed');
        Route::post('/bedrock-addon-installer/install', [Client\Servers\DGEN\MinecraftBedrockAddonController::class, 'installAddon'])->name('api:client:server.minecraft.bedrock-addon-installer.install');
        Route::delete('/bedrock-addon-installer/uninstall', [Client\Servers\DGEN\MinecraftBedrockAddonController::class, 'uninstallAddon'])->name('api:client:server.minecraft.bedrock-addon-installer.uninstall');
        Route::get('/bedrock-addon-installer/progress', [Client\Servers\DGEN\MinecraftBedrockAddonController::class, 'getProgress'])->name('api:client:server.minecraft.bedrock-addon-installer.progress');
        Route::get('/bedrock-addon-installer/versions/{addonId}', [Client\Servers\DGEN\MinecraftBedrockAddonController::class, 'getAddonVersions'])->name('api:client:server.minecraft.bedrock-addon-installer.versions');
        Route::get('/bedrock-addon-installer/icon/{iconPath}', [Client\Servers\DGEN\MinecraftBedrockAddonController::class, 'getAddonIcon'])
            ->where('iconPath', '.*')
            ->name('api:client:server.minecraft.bedrock-addon-installer.icon');

        Route::get('/bedrock-addon-installer/cache', [Client\Servers\DGEN\MinecraftBedrockAddonCacheController::class, 'getCachedAddons'])->name('api:client:server.minecraft.bedrock-addon-installer.cache.get');
        Route::post('/bedrock-addon-installer/cache', [Client\Servers\DGEN\MinecraftBedrockAddonCacheController::class, 'cacheAddonData'])->name('api:client:server.minecraft.bedrock-addon-installer.cache.post');
        Route::delete('/bedrock-addon-installer/cache', [Client\Servers\DGEN\MinecraftBedrockAddonCacheController::class, 'clearCache'])->name('api:client:server.minecraft.bedrock-addon-installer.cache.clear');
        Route::get('/bedrock-addon-installer/cache/status', [Client\Servers\DGEN\MinecraftBedrockAddonCacheController::class, 'getCacheStatus'])->name('api:client:server.minecraft.bedrock-addon-installer.cache.status');

        Route::get('/bedrock-version-changer/versions', [Client\Servers\DGEN\MinecraftBedrockVersionController::class, 'getVersions'])->name('api:client:server.minecraft.bedrock-version-changer.versions');
        Route::get('/bedrock-version-changer/specific/{type}/{version}', [Client\Servers\DGEN\MinecraftBedrockVersionController::class, 'getSpecificVersions'])->name('api:client:server.minecraft.bedrock-version-changer.specific');
        Route::post('/bedrock-version-changer/change', [Client\Servers\DGEN\MinecraftBedrockVersionController::class, 'changeVersion'])->name('api:client:server.minecraft.bedrock-version-changer.change');
        Route::get('/bedrock-version-changer/progress', [Client\Servers\DGEN\MinecraftBedrockVersionController::class, 'getProgress'])->name('api:client:server.minecraft.bedrock-version-changer.progress');
        Route::group(['prefix' => '/player-manager'], function () {
            Route::get('/', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'index'])->name('api:client:server.minecraft.player-manager.index');
            Route::post('/fix-rcon', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'fixRcon'])->name('api:client:server.minecraft.player-manager.fix-rcon');
            Route::get('/details/{playerUuid}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'details'])->name('api:client:server.minecraft.player-manager.details');
            Route::post('/details/{playerUuid}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'saveDetails'])->name('api:client:server.minecraft.player-manager.save-details');
            Route::post('/icons', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'batchIcons'])->name('api.client.servers.DGEN.minecraft.player-manager.icons-batch');
            Route::get('/icon/{item}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'icon'])->name('api.client.servers.DGEN.minecraft.player-manager.icon');
            Route::get('/worlds', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'worlds'])->name('api:client:server.minecraft.player-manager.worlds');
            Route::post('/action', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'action'])->name('api:client:server.minecraft.player-manager.action');
            Route::post('/health/{player}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setHealth'])->name('api:client:server.minecraft.player-manager.health');
            Route::post('/food/{player}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setFood'])->name('api:client:server.minecraft.player-manager.food');
            Route::post('/experience/{player}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setExperience'])->name('api:client:server.minecraft.player-manager.experience');
        });

        Route::group(['prefix' => '/players'], function () {
            Route::get('/fast-query', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'fastQuery'])->name('api:client:server.players.fast-query');
            Route::post('/reload', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'reload'])->name('api:client:server.players.reload');
            Route::get('/query-status', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'getQueryStatus'])->name('api:client:server.players.query-status');
            Route::post('/enable-query', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'enableQuery'])->name('api:client:server.players.enable-query');
            Route::get('/worlds', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'getWorlds'])->name('api:client:server.players.worlds');
            Route::post('/icons', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'batchIcons'])->name('api:client:server.players.icons');
            Route::get('/icon/{item}', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'itemIcon'])->name('api:client:server.players.icon');

            Route::get('/{uuid}/items', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'getPlayerItems'])->name('api:client:server.players.items');
            Route::post('/{uuid}/whitelist', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'whitelistPlayer'])->name('api:client:server.players.whitelist.add');
            Route::delete('/{uuid}/whitelist', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'unwhitelistPlayer'])->name('api:client:server.players.whitelist.remove');
            Route::post('/{uuid}/ban', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'banPlayer'])->name('api:client:server.players.ban.add');
            Route::delete('/{uuid}/ban', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'unbanPlayer'])->name('api:client:server.players.ban.remove');
            Route::post('/{uuid}/op', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'opPlayer'])->name('api:client:server.players.op.add');
            Route::delete('/{uuid}/op', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'deopPlayer'])->name('api:client:server.players.op.remove');
            Route::post('/{uuid}/clear-inventory', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'clearPlayerInventory'])->name('api:client:server.players.clear-inventory');
            Route::delete('/{uuid}/wipe-data', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'wipePlayerData'])->name('api:client:server.players.wipe-data');
            Route::post('/{uuid}/gamemode', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'changeGamemode'])->name('api:client:server.players.gamemode');

            Route::group(['prefix' => '/java'], function () {
                Route::post('/kick', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'kickPlayer'])->name('api:client:server.players.java.kick');
                Route::post('/ban-with-reason', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'banWithReason'])->name('api:client:server.players.java.ban-with-reason');
                Route::post('/give-item', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'giveItem'])->name('api:client:server.players.java.give-item');
                Route::post('/set-health', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setHealth'])->name('api:client:server.players.java.set-health');
                Route::post('/set-food', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setFood'])->name('api:client:server.players.java.set-food');
                Route::post('/set-saturation', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setSaturation'])->name('api:client:server.players.java.set-saturation');
                Route::post('/set-experience', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setExperience'])->name('api:client:server.players.java.set-experience');
                Route::post('/apply-effect', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'applyEffect'])->name('api:client:server.players.java.apply-effect');
                Route::post('/action', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'genericAction'])->name('api:client:server.players.java.action');
                Route::post('/set-inventory-slot', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setInventorySlot'])->name('api:client:server.players.java.set-inventory-slot');

                Route::group(['prefix' => '/server'], function () {
                    Route::get('/info', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'getServerInfo'])->name('api:client:server.players.java.server.info');
                    Route::post('/time', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setServerTime'])->name('api:client:server.players.java.server.time');
                    Route::post('/weather', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setServerWeather'])->name('api:client:server.players.java.server.weather');
                    Route::post('/difficulty', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'setServerDifficulty'])->name('api:client:server.players.java.server.difficulty');
                    Route::post('/gamerule', [Client\Servers\DGEN\MinecraftPlayerManagerController::class, 'toggleGameRule'])->name('api:client:server.players.java.server.gamerule');
                });
            });
        });
    });

    Route::group(['prefix' => '/ark'], function () {
        Route::get('/mod-installer/server-info', [Client\Servers\DGEN\ArkModController::class, 'getServerInfo'])->name('api:client:server.ark.mod-installer.server-info');
        Route::get('/mod-installer/mod-ids', [Client\Servers\DGEN\ArkModController::class, 'getModIds'])->name('api:client:server.ark.mod-installer.mod-ids');
        Route::get('/mod-installer/installed', [Client\Servers\DGEN\ArkModController::class, 'getInstalledMods'])->name('api:client:server.ark.mod-installer.installed');
        Route::post('/mod-installer/install', [Client\Servers\DGEN\ArkModController::class, 'installMod'])->name('api:client:server.ark.mod-installer.install');
        Route::delete('/mod-installer/uninstall', [Client\Servers\DGEN\ArkModController::class, 'uninstallMod'])->name('api:client:server.ark.mod-installer.uninstall');
        Route::get('/mod-installer/search', [Client\Servers\DGEN\ArkModController::class, 'searchMods'])->name('api:client:server.ark.mod-installer.search');
        Route::get('/mod-installer/versions/{modId}', [Client\Servers\DGEN\ArkModController::class, 'getModVersions'])->name('api:client:server.ark.mod-installer.versions');
        Route::get('/mod-installer/progress', [Client\Servers\DGEN\ArkModController::class, 'getProgress'])->name('api:client:server.ark.mod-installer.progress');
        Route::get('/mod-installer/check-availability', [Client\Servers\DGEN\ArkModController::class, 'checkAddonAvailability'])->name('api:client:server.ark.mod-installer.check-availability');

        Route::get('/mod-installer/cache', [Client\Servers\DGEN\ArkModCacheController::class, 'getCachedMods'])->name('api:client:server.ark.mod-installer.cache.get');
        Route::post('/mod-installer/cache', [Client\Servers\DGEN\ArkModCacheController::class, 'cacheModData'])->name('api:client:server.ark.mod-installer.cache.post');
        Route::delete('/mod-installer/cache', [Client\Servers\DGEN\ArkModCacheController::class, 'clearCache'])->name('api:client:server.ark.mod-installer.cache.clear');
        Route::get('/mod-installer/cache/status', [Client\Servers\DGEN\ArkModCacheController::class, 'getCacheStatus'])->name('api:client:server.ark.mod-installer.cache.status');

        Route::get('/mod-installer/icon/{provider}/{iconPath}', [Client\Servers\DGEN\ArkModController::class, 'getModIcon'])
            ->where('iconPath', '.*')
            ->name('api:client:server.ark.mod-installer.icon');
    });

    Route::group(['prefix' => '/hytale'], function () {
        Route::get('/mod-installer/check-availability', [Client\Servers\DGEN\HytaleModController::class, 'checkAddonAvailability'])->name('api:client:server.hytale.mod-installer.check-availability');
        Route::get('/mod-installer/cache', [Client\Servers\DGEN\HytaleModController::class, 'getCachedMods'])->name('api:client:server.hytale.mod-installer.cache');
        Route::get('/mod-installer/installed', [Client\Servers\DGEN\HytaleModController::class, 'getInstalledMods'])->name('api:client:server.hytale.mod-installer.installed');
        Route::post('/mod-installer/install', [Client\Servers\DGEN\HytaleModController::class, 'installMod'])->name('api:client:server.hytale.mod-installer.install');
        Route::delete('/mod-installer/uninstall', [Client\Servers\DGEN\HytaleModController::class, 'uninstallMod'])->name('api:client:server.hytale.mod-installer.uninstall');
        Route::get('/mod-installer/progress', [Client\Servers\DGEN\HytaleModController::class, 'getProgress'])->name('api:client:server.hytale.mod-installer.progress');
        Route::get('/mod-installer/versions/{provider}/{modId}', [Client\Servers\DGEN\HytaleModController::class, 'getModVersions'])->name('api:client:server.hytale.mod-installer.versions');
        Route::get('/mod-installer/icon/{provider}/{iconPath}', [Client\Servers\DGEN\HytaleModController::class, 'getModIcon'])
            ->where('iconPath', '.*')
            ->name('api:client:server.hytale.mod-installer.icon');

        Route::get('/world-manager/installed', [Client\Servers\DGEN\HytaleWorldController::class, 'getInstalledWorlds'])->name('api:client:server.hytale.world-manager.installed');
        Route::post('/world-manager/install', [Client\Servers\DGEN\HytaleWorldController::class, 'installWorld'])->name('api:client:server.hytale.world-manager.install');
        Route::get('/world-manager/progress', [Client\Servers\DGEN\HytaleWorldController::class, 'getProgress'])->name('api:client:server.hytale.world-manager.progress');
        Route::get('/world-manager/versions/{worldId}', [Client\Servers\DGEN\HytaleWorldController::class, 'getWorldVersions'])->name('api:client:server.hytale.world-manager.versions');
        
        Route::get('/world-manager/icon/{avatarPath}', [Client\Servers\DGEN\HytaleWorldController::class, 'getWorldIcon'])
            ->where('avatarPath', '.*')
            ->name('api:client:server.hytale.world-manager.icon');
            
        Route::get('/world-manager/check-addon-availability', [Client\Servers\DGEN\HytaleWorldController::class, 'checkAddonAvailability'])->name('api:client:server.hytale.world-manager.check-availability');
        
        Route::get('/world-manager/inspect', [Client\Servers\DGEN\HytaleWorldController::class, 'inspectServer'])->name('api:client:server.hytale.world-manager.inspect');
        Route::delete('/world-manager/uninstall', [Client\Servers\DGEN\HytaleWorldController::class, 'uninstallWorld'])->name('api:client:server.hytale.world-manager.uninstall');

        Route::get('/world-manager/level-name', [Client\Servers\DGEN\HytaleWorldController::class, 'getLevelName'])->name('api:client:server.hytale.world-manager.level-name.get');
        Route::post('/world-manager/level-name', [Client\Servers\DGEN\HytaleWorldController::class, 'updateLevelName'])->name('api:client:server.hytale.world-manager.level-name.update');

        Route::get('/world-manager/cache', [Client\Servers\DGEN\HytaleWorldCacheController::class, 'getCachedWorlds'])->name('api:client:server.hytale.world-manager.cache.get');
        Route::post('/world-manager/cache', [Client\Servers\DGEN\HytaleWorldCacheController::class, 'cacheWorldData'])->name('api:client:server.hytale.world-manager.cache.post');
        Route::delete('/world-manager/cache', [Client\Servers\DGEN\HytaleWorldCacheController::class, 'clearCache'])->name('api:client:server.hytale.world-manager.cache.clear');
        Route::get('/world-manager/cache/status', [Client\Servers\DGEN\HytaleWorldCacheController::class, 'getCacheStatus'])->name('api:client:server.hytale.world-manager.cache.status');
    });

    Route::group(['prefix' => '/fivem-utils'], function () {
        Route::get('/components', [Client\Servers\DGEN\FiveMUtilsController::class, 'getComponents']);
        Route::post('cache', [Client\Servers\DGEN\FiveMUtilsController::class, 'clearCache']);
        Route::post('build', [Client\Servers\DGEN\FiveMUtilsController::class, 'setGameBuild']);
        Route::post('txadmin', [Client\Servers\DGEN\FiveMUtilsController::class, 'toggleTxAdmin']);
        Route::post('txadmin-port', [Client\Servers\DGEN\FiveMUtilsController::class, 'setTxAdminPort']);
        Route::post('database', [Client\Servers\DGEN\FiveMUtilsController::class, 'configureDatabase']);
        Route::post('artifact', [Client\Servers\DGEN\FiveMUtilsController::class, 'changeArtifact']);
    });

    Route::group(['prefix' => '/arma-reforger'], function () {
        Route::get('/mod-manager/test', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'test'])->name('api:client:server.arma-reforger.mod-manager.test');
        Route::get('/mod-manager/config', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getSelectedConfig'])->name('api:client:server.arma-reforger.mod-manager.config');
        Route::post('/mod-manager/dependencies', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'fetchDependencies'])->name('api:client:server.arma-reforger.mod-manager.dependencies');

        Route::get('/mod-manager/mods', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'fetchMods'])->name('api:client:server.arma-reforger.mod-manager.mods');
        Route::get('/mod-manager/mods/{modId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'fetchModById'])->name('api:client:server.arma-reforger.mod-manager.mod-details');
        Route::get('/mod-manager/mod/{modId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'fetchModById'])->name('api:client:server.arma-reforger.mod-manager.mod-details-alias');
        Route::get('/mod-manager/search', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'fetchMods'])->name('api:client:server.arma-reforger.mod-manager.search');

        Route::get('/mod-manager/installed', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getInstalledMods'])->name('api:client:server.arma-reforger.mod-manager.installed');
        Route::post('/mod-manager/install', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'updateMod'])->name('api:client:server.arma-reforger.mod-manager.install');
        Route::post('/mod-manager/uninstall', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'updateMod'])->name('api:client:server.arma-reforger.mod-manager.uninstall');

        Route::post('/mod-manager/batch-details', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'batchFetchModDetails'])->name('api:client:server.arma-reforger.mod-manager.batch-details');
        Route::post('/mod-manager/refresh-cache', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'refreshModCache'])->name('api:client:server.arma-reforger.mod-manager.refresh-cache');
        Route::post('/mod-manager/clear-dependency-cache', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'clearDependencyCache'])->name('api:client:server.arma-reforger.mod-manager.clear-dependency-cache');
        
        Route::post('/mod-manager/diagnose', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'diagnose'])->name('api:client:server.arma-reforger.mod-manager.diagnose');
        Route::get('/mod-manager/diagnose', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'diagnoseStatus'])->name('api:client:server.arma-reforger.mod-manager.diagnose.status');
        Route::delete('/mod-manager/diagnose', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'diagnoseAbort'])->name('api:client:server.arma-reforger.mod-manager.diagnose.abort');

        Route::get('/mod-manager/webhook-settings', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getWebhookSettings'])->name('api:client:server.arma-reforger.mod-manager.webhook-settings.get');
        Route::post('/mod-manager/webhook-settings', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'saveWebhookSettings'])->name('api:client:server.arma-reforger.mod-manager.webhook-settings.save');

        Route::get('/mod-manager/collections', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getCollections'])->name('api:client:server.arma-reforger.mod-manager.collections');
        Route::post('/mod-manager/collections', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'createCollection'])->name('api:client:server.arma-reforger.mod-manager.collections.create');
        Route::put('/mod-manager/collections/{collectionId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'updateCollection'])->name('api:client:server.arma-reforger.mod-manager.collections.update');
        Route::delete('/mod-manager/collections/{collectionId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'deleteCollection'])->name('api:client:server.arma-reforger.mod-manager.collections.delete');
        Route::post('/mod-manager/collections/{collectionId}/apply', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'applyCollection'])->name('api:client:server.arma-reforger.mod-manager.collections.apply');
        Route::post('/mod-manager/collections/{collectionId}/toggle-visibility', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'toggleCollectionVisibility'])->name('api:client:server.arma-reforger.mod-manager.collections.toggle-visibility');

        Route::get('/mod-manager/collections/{collectionId}/members', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getCollectionMembers'])->name('api:client:server.arma-reforger.mod-manager.collections.members.index');
        Route::post('/mod-manager/collections/{collectionId}/members', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'addCollectionMember'])->name('api:client:server.arma-reforger.mod-manager.collections.members.store');
        Route::delete('/mod-manager/collections/{collectionId}/members/{userId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'removeCollectionMember'])->name('api:client:server.arma-reforger.mod-manager.collections.members.destroy');
        Route::put('/mod-manager/collections/{collectionId}/members/{userId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'updateCollectionMember'])->name('api:client:server.arma-reforger.mod-manager.collections.members.update');

        Route::post('/mod-manager/update', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'updateMod'])->name('api:client:server.arma-reforger.mod-manager.update');
        Route::post('/mod-manager/bulk-update', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'bulkUpdateMods'])->name('api:client:server.arma-reforger.mod-manager.bulk-update');
        Route::post('/mod-manager/reorder', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'reorderMods'])->name('api:client:server.arma-reforger.mod-manager.reorder');
        Route::post('/mod-manager/send-to-discord', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'sendToDiscord'])->name('api:client:server.arma-reforger.mod-manager.send-to-discord');
        Route::get('/mod-manager/addons-size', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getAddonsSize'])->name('api:client:server.arma-reforger.mod-manager.addons-size');
        Route::get('/mod-manager/versions/{modId}', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'getModVersions'])->name('api:client:server.arma-reforger.mod-manager.versions');
        Route::post('/mod-manager/batch-version-update', [Client\Servers\DGEN\ArmaReforgerModManagerController::class, 'batchVersionUpdate'])->name('api:client:server.arma-reforger.mod-manager.batch-version-update');

        Route::get('/config-editor', [Client\Servers\DGEN\ArmaReforgerConfigController::class, 'index'])->name('api:client:server.arma-reforger.config-editor.index');
        Route::post('/config-editor', [Client\Servers\DGEN\ArmaReforgerConfigController::class, 'save'])->name('api:client:server.arma-reforger.config-editor.save');
        Route::get('/config-editor/mods-scenarios', [Client\Servers\DGEN\ArmaReforgerConfigController::class, 'getModsWithScenarios'])->name('api:client:server.arma-reforger.config-editor.mods-scenarios');

        Route::group(['prefix' => '/admin-tools'], function () {
            Route::get('/test',            [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'test'])->name('api:client:server.arma-reforger.admin-tools.test');
            Route::get('/check-installed', [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'checkInstalled'])->name('api:client:server.arma-reforger.admin-tools.check-installed');
            Route::get('/config',          [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'getToolsConfig'])->name('api:client:server.arma-reforger.admin-tools.config');
            Route::post('/config',         [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'updateConfig'])->name('api:client:server.arma-reforger.admin-tools.config.update');
            Route::post('/reset',          [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'resetConfig'])->name('api:client:server.arma-reforger.admin-tools.reset');
            Route::get('/priority-queue',  [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'getPriorityQueue'])->name('api:client:server.arma-reforger.admin-tools.priority-queue');
            Route::post('/priority-queue', [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'updatePriorityQueue'])->name('api:client:server.arma-reforger.admin-tools.priority-queue.update');
            Route::get('/stats',           [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'getStats'])->name('api:client:server.arma-reforger.admin-tools.stats');
            Route::get('/player-count',    [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'getPlayerCount'])->name('api:client:server.arma-reforger.admin-tools.player-count');
            Route::get('/scoreboard',      [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'getScoreboard'])->name('api:client:server.arma-reforger.admin-tools.scoreboard');
            Route::get('/webhook-config',  [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'getWebhookConfig'])->name('api:client:server.arma-reforger.admin-tools.webhook-config');
            Route::post('/webhook-config', [Client\Servers\DGEN\ArmaReforgerAdminToolsController::class, 'updateWebhookConfig'])->name('api:client:server.arma-reforger.admin-tools.webhook-config.update');
        });
    });

    Route::get('/hyperv2-addon/check-server-availability', [Client\Servers\DGEN\MinecraftPluginController::class, 'checkAddonAvailability'])->name('api:client:server.hyperv2-addon.check-availability');

    Route::post('/command', [Client\Servers\CommandController::class, 'index']);
    Route::post('/power', [Client\Servers\PowerController::class, 'index']);

    Route::group(['prefix' => '/databases'], function () {
        Route::get('/', [Client\Servers\DatabaseController::class, 'index']);
        Route::middleware([ResourceLimit::Database->middleware()])
            ->post('/', [Client\Servers\DatabaseController::class, 'store']);
        Route::post('/{database}/rotate-password', [Client\Servers\DatabaseController::class, 'rotatePassword']);
        Route::delete('/{database}', [Client\Servers\DatabaseController::class, 'delete']);
        Route::delete('/{database}/clear', [Client\Servers\DatabaseController::class, 'clear']);
        Route::post('/{database}/import', [Client\Servers\DatabaseController::class, 'import']);
        Route::get('/{database}/export', [Client\Servers\DatabaseController::class, 'export']);
        Route::get('/{database}/download/{filename}', [Client\Servers\DatabaseController::class, 'download'])->name('api.client.servers.database.download');
        Route::post('/{database}/import-remote', [Client\Servers\DatabaseController::class, 'importFromRemote']);
    });

    Route::group(['prefix' => '/files'], function () {
        Route::get('/list', [Client\Servers\FileController::class, 'directory']);
        Route::get('/contents', [Client\Servers\FileController::class, 'contents']);
        Route::get('/recent-edits', [Client\Servers\FileController::class, 'recentEdits']);
        Route::get('/recent-edits/{edit}', [Client\Servers\FileController::class, 'recentEdit']);
        Route::get('/download', [Client\Servers\FileController::class, 'download']);
        Route::put('/rename', [Client\Servers\FileController::class, 'rename']);
        Route::post('/copy', [Client\Servers\FileController::class, 'copy']);
        Route::post('/write', [Client\Servers\FileController::class, 'write']);
        Route::post('/compress', [Client\Servers\FileController::class, 'compress']);
        Route::post('/decompress', [Client\Servers\FileController::class, 'decompress']);
        Route::post('/delete', [Client\Servers\FileController::class, 'delete']);
        Route::post('/create-folder', [Client\Servers\FileController::class, 'create']);
        Route::post('/chmod', [Client\Servers\FileController::class, 'chmod']);
        Route::middleware([ResourceLimit::FilePull->middleware()])
            ->post('/pull', [Client\Servers\FileController::class, 'pull']);
        Route::get('/upload', Client\Servers\FileUploadController::class);
        Route::post('/search', [WingsAddonController::class, 'searchFiles']);
        Route::post('/replace', [WingsAddonController::class, 'replaceFiles']);
        Route::post('/folder-size', [WingsAddonController::class, 'folderSize']);
        Route::post('/folder-size-batch', [WingsAddonController::class, 'folderSizeBatch']);



        Route::group(['prefix' => '/recycle'], function () {
            Route::get('/', [Client\Servers\DGEN\RecycleBinController::class, 'index']);
            Route::get('/stats', [Client\Servers\DGEN\RecycleBinController::class, 'stats']);
            Route::post('/', [Client\Servers\DGEN\RecycleBinController::class, 'store']);
            Route::post('/restore', [Client\Servers\DGEN\RecycleBinController::class, 'restore']);
            Route::post('/restore/multiple', [Client\Servers\DGEN\RecycleBinController::class, 'restoreMultiple']);
            Route::delete('/permanent', [Client\Servers\DGEN\RecycleBinController::class, 'permanentDelete']);
            Route::delete('/empty', [Client\Servers\DGEN\RecycleBinController::class, 'empty']);
            Route::get('/{fileId}', [Client\Servers\DGEN\RecycleBinController::class, 'show']);
            Route::get('/{fileId}/preview', [Client\Servers\DGEN\RecycleBinController::class, 'preview']);
            Route::get('/{fileId}/download', [Client\Servers\DGEN\RecycleBinController::class, 'download']);
        });

        Route::group(['prefix' => '/subdomain-manager'], function () {
            Route::get('/', [Client\Servers\DGEN\SubdomainManagerController::class, 'index']);
            Route::post('/', [Client\Servers\DGEN\SubdomainManagerController::class, 'store'])->middleware('throttle:10,1');
            Route::post('/check', [Client\Servers\DGEN\SubdomainManagerController::class, 'checkAvailability'])->middleware('throttle:30,1');
            Route::delete('/{id}', [Client\Servers\DGEN\SubdomainManagerController::class, 'destroy']);
        });

        Route::group(['prefix' => '/addons/auto-suspend'], function () {
            Route::get('/expiry', [Client\Servers\DGEN\AutoSuspendController::class, 'getExpiry']);
            Route::post('/expiry', [Client\Servers\DGEN\AutoSuspendController::class, 'setExpiry']);
            Route::delete('/expiry', [Client\Servers\DGEN\AutoSuspendController::class, 'removeExpiry']);
        });

        Route::group(['prefix' => '/quick-access'], function () {
            Route::get('/', [Client\Servers\DGEN\QuickFileAccessController::class, 'index']);
            Route::post('/', [Client\Servers\DGEN\QuickFileAccessController::class, 'store']);
            Route::post('/toggle', [Client\Servers\DGEN\QuickFileAccessController::class, 'toggle']);
            Route::post('/check', [Client\Servers\DGEN\QuickFileAccessController::class, 'check']);
            Route::post('/validate', [Client\Servers\DGEN\QuickFileAccessController::class, 'validateItems']);
            Route::delete('/{id}', [Client\Servers\DGEN\QuickFileAccessController::class, 'destroy']);
            Route::delete('/', [Client\Servers\DGEN\QuickFileAccessController::class, 'destroyByPath']);
        });
    });

    Route::group(['prefix' => '/addons/staff-request'], function () {
        Route::get('/requests', [Client\Servers\DGEN\StaffRequestController::class, 'serverRequests']);
        Route::get('/requests/count', [Client\Servers\DGEN\StaffRequestController::class, 'serverPendingCount']);
        Route::post('/requests', [Client\Servers\DGEN\StaffRequestController::class, 'store']);
        Route::post('/requests/{staffRequest}/accept', [Client\Servers\DGEN\StaffRequestController::class, 'accept']);
        Route::post('/requests/{staffRequest}/reject', [Client\Servers\DGEN\StaffRequestController::class, 'reject']);
        Route::get('/search-servers', [Client\Servers\DGEN\StaffRequestController::class, 'searchServers']);
    });

    Route::group(['prefix' => '/addons/server-importer'], function () {
        Route::get('/imports', [Client\Servers\DGEN\ServerImporterController::class, 'index']);
        Route::post('/imports', [Client\Servers\DGEN\ServerImporterController::class, 'store']);
        Route::get('/imports/{import}', [Client\Servers\DGEN\ServerImporterController::class, 'show']);
        Route::patch('/imports/{import}', [Client\Servers\DGEN\ServerImporterController::class, 'update']);
        Route::delete('/imports/{import}', [Client\Servers\DGEN\ServerImporterController::class, 'destroy']);
        Route::post('/imports/{import}/browse', [Client\Servers\DGEN\ServerImporterController::class, 'browse']);
        Route::post('/imports/{import}/import', [Client\Servers\DGEN\ServerImporterController::class, 'import']);
        Route::get('/imports/{import}/progress', [Client\Servers\DGEN\ServerImporterController::class, 'importProgress']);
        Route::post('/imports/{import}/cancel', [Client\Servers\DGEN\ServerImporterController::class, 'cancelImport']);
        Route::post('/restore', [Client\Servers\DGEN\ServerImporterController::class, 'restore']);
        Route::get('/status', [Client\Servers\DGEN\ServerImporterController::class, 'status']);
    });

    Route::group(['prefix' => '/addons/server-type-changer'], function () {
        Route::get('/nests', [Client\Servers\DGEN\ServerTypeChangerController::class, 'getNests']);
        Route::get('/current', [Client\Servers\DGEN\ServerTypeChangerController::class, 'getCurrentServerType']);
        Route::post('/change', [Client\Servers\DGEN\ServerTypeChangerController::class, 'changeServerType']);
        Route::get('/progress', [Client\Servers\DGEN\ServerTypeChangerController::class, 'getProgress']);
    });

    Route::group(['prefix' => '/addons/upload-from-url'], function () {
        Route::post('/upload', [Client\Servers\DGEN\UploadFromUrlController::class, 'upload']);
    });

    Route::group(['prefix' => '/addons/server-splitter'], function () {
        Route::get('/available-resources', [Client\Servers\DGEN\ServerSplitterController::class, 'availableResources']);
        Route::get('/splits', [Client\Servers\DGEN\ServerSplitterController::class, 'index']);
        Route::post('/splits', [Client\Servers\DGEN\ServerSplitterController::class, 'store']);
        Route::get('/splits/{split}', [Client\Servers\DGEN\ServerSplitterController::class, 'show']);
        Route::put('/splits/{split}', [Client\Servers\DGEN\ServerSplitterController::class, 'update']);
        Route::delete('/splits/{split}', [Client\Servers\DGEN\ServerSplitterController::class, 'destroy']);


    });

    Route::group(['prefix' => '/config-editor'], function () {
        Route::get('/files', [Client\Servers\DGEN\ConfigEditorController::class, 'getAvailableFiles'])->name('api:client:server.config-editor.files');
        Route::get('/content', [Client\Servers\DGEN\ConfigEditorController::class, 'getFileContent'])->name('api:client:server.config-editor.content.get');
        Route::put('/content', [Client\Servers\DGEN\ConfigEditorController::class, 'updateFileContent'])->name('api:client:server.config-editor.content.update');
    });


    Route::group(['prefix' => '/addons/startup-presets'], function () {
        Route::get('/presets', [Client\Servers\DGEN\StartupPresetsController::class, 'getPresets']);
        Route::post('/apply', [Client\Servers\DGEN\StartupPresetsController::class, 'applyPreset']);
        Route::put('/startup', [Client\Servers\DGEN\StartupPresetsController::class, 'updateStartup']);
    });

    Route::group(['prefix' => '/addons/schedule-presets'], function () {
        Route::post('/apply', [Client\Servers\DGEN\SchedulePresetsController::class, 'applyPreset']);
        Route::post('/import', [Client\Servers\DGEN\SchedulePresetsController::class, 'importSchedule']);
    });

    Route::group(['prefix' => '/addons/server-wiper'], function () {
        Route::get('/schedules', [Client\Servers\DGEN\ServerWiperController::class, 'getSchedules']);
        Route::post('/schedules', [Client\Servers\DGEN\ServerWiperController::class, 'createSchedule']);
        Route::put('/schedules/{scheduleId}', [Client\Servers\DGEN\ServerWiperController::class, 'updateSchedule']);
        Route::patch('/schedules/{scheduleId}/toggle', [Client\Servers\DGEN\ServerWiperController::class, 'toggleSchedule']);
        Route::delete('/schedules/{scheduleId}', [Client\Servers\DGEN\ServerWiperController::class, 'deleteSchedule']);
        Route::post('/schedules/{scheduleId}/execute', [Client\Servers\DGEN\ServerWiperController::class, 'executeNow']);
        Route::get('/history', [Client\Servers\DGEN\ServerWiperController::class, 'getHistory']);
        Route::get('/rust-maps', [Client\Servers\DGEN\ServerWiperController::class, 'getRustMaps']);
        Route::post('/rust-maps', [Client\Servers\DGEN\ServerWiperController::class, 'createRustMap']);
        Route::delete('/rust-maps/{mapId}', [Client\Servers\DGEN\ServerWiperController::class, 'deleteRustMap']);
    });

    Route::group(['prefix' => '/minecraft/votifier-tester'], function () {
        Route::post('/test', [Client\Servers\DGEN\MinecraftVotifierTesterController::class, 'test']);
    });

    Route::group(['prefix' => '/addons/reverse-proxy'], function () {
        Route::get('/', [Client\ReverseProxyController::class, 'index']);
        Route::post('/', [Client\ReverseProxyController::class, 'store']);
        Route::put('/{proxy}', [Client\ReverseProxyController::class, 'update']);
        Route::delete('/{proxy}', [Client\ReverseProxyController::class, 'delete']);

        Route::get('/whitelist', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'index']);
        Route::post('/whitelist', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'store']);
        Route::put('/whitelist/{id}', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'update']);
        Route::delete('/whitelist/{id}', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'destroy']);
        Route::get('/search', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'searchServers']);
    });


    Route::group(['prefix' => '/addons/wings-agent'], function () {
        Route::get('/ticket', [Client\Servers\DGEN\ServerAgentTicketController::class, 'ticket'])->name('api:client:server.DGEN.wings-agent.ticket');
    });

    Route::group(['prefix' => '/addons/network-statistics'], function () {
        Route::get('/allocations', [Client\Servers\DGEN\NetworkStatisticsController::class, 'allocations'])->name('api:client:server.DGEN.network-statistics.allocations');
        Route::get('/port-detail', [Client\Servers\DGEN\NetworkStatisticsController::class, 'portDetail'])->name('api:client:server.DGEN.network-statistics.port-detail');
        Route::get('/port-history', [Client\Servers\DGEN\NetworkStatisticsController::class, 'portHistory'])->name('api:client:server.DGEN.network-statistics.port-history');
    });

    Route::group(['prefix' => '/addons/firewall-manager'], function () {
        Route::get('/allocations', [Client\Servers\DGEN\FirewallManagerController::class, 'allocations'])->name('api:client:server.DGEN.firewall-manager.allocations');
        Route::get('/rules', [Client\Servers\DGEN\FirewallManagerController::class, 'rules'])->name('api:client:server.DGEN.firewall-manager.rules');
        Route::post('/rule/add', [Client\Servers\DGEN\FirewallManagerController::class, 'addRule'])->name('api:client:server.DGEN.firewall-manager.rule-add');
        Route::post('/rule/delete', [Client\Servers\DGEN\FirewallManagerController::class, 'deleteRule'])->name('api:client:server.DGEN.firewall-manager.rule-delete');
        Route::post('/port/reset', [Client\Servers\DGEN\FirewallManagerController::class, 'resetPort'])->name('api:client:server.DGEN.firewall-manager.port-reset');
    });

    Route::group(['prefix' => '/DGEN/command-history'], function () {
        Route::get('/', [Client\Servers\DGEN\CommandHistoryController::class, 'index'])->name('api:client:server.DGEN.command-history.index');
        Route::post('/', [Client\Servers\DGEN\CommandHistoryController::class, 'store'])->name('api:client:server.DGEN.command-history.store');
    });

    Route::group(['prefix' => '/addons/DGEN/fastdl'], function () {
        Route::get('/', [Client\Servers\DGEN\FastDLController::class, 'index']);
        Route::post('/sync', [Client\Servers\DGEN\FastDLController::class, 'sync']);
    });

    Route::group(['prefix' => '/schedules'], function () {
        Route::get('/', [Client\Servers\ScheduleController::class, 'index']);
        Route::middleware([ResourceLimit::Schedule->middleware()])
            ->post('/', [Client\Servers\ScheduleController::class, 'store']);
        Route::get('/{schedule}', [Client\Servers\ScheduleController::class, 'view']);
        Route::post('/{schedule}', [Client\Servers\ScheduleController::class, 'update']);
        Route::post('/{schedule}/execute', [Client\Servers\ScheduleController::class, 'execute']);
        Route::get('/{schedule}/export', [Client\Servers\ScheduleController::class, 'export']);
        Route::delete('/{schedule}', [Client\Servers\ScheduleController::class, 'delete']);

        Route::post('/{schedule}/tasks', [Client\Servers\ScheduleTaskController::class, 'store']);
        Route::post('/{schedule}/tasks/{task}', [Client\Servers\ScheduleTaskController::class, 'update']);
        Route::delete('/{schedule}/tasks/{task}', [Client\Servers\ScheduleTaskController::class, 'delete']);
    });

    Route::group(['prefix' => '/network'], function () {
        Route::get('/allocations', [Client\Servers\NetworkAllocationController::class, 'index']);
        Route::middleware([ResourceLimit::Allocation->middleware()])
            ->post('/allocations', [Client\Servers\NetworkAllocationController::class, 'store']);
        Route::post('/allocations/{allocation}', [Client\Servers\NetworkAllocationController::class, 'update']);
        Route::post('/allocations/{allocation}/primary', [Client\Servers\NetworkAllocationController::class, 'setPrimary']);
        Route::delete('/allocations/{allocation}', [Client\Servers\NetworkAllocationController::class, 'delete']);
    });

    Route::group(['prefix' => '/users'], function () {
        Route::get('/', [Client\Servers\SubuserController::class, 'index']);
        Route::middleware([ResourceLimit::Subuser->middleware()])
            ->post('/', [Client\Servers\SubuserController::class, 'store']);
        Route::get('/{user}', [Client\Servers\SubuserController::class, 'view']);
        Route::post('/{user}', [Client\Servers\SubuserController::class, 'update']);
        Route::delete('/{user}', [Client\Servers\SubuserController::class, 'delete']);
    });

    Route::get('/admin/users/search', [Client\AdminUserSearchController::class, 'search']);

    Route::group(['prefix' => '/backups'], function () {
        Route::get('/', [Client\Servers\BackupController::class, 'index']);
        Route::post('/', [Client\Servers\BackupController::class, 'store']);
        Route::get('/auto', [Client\Servers\BackupController::class, 'autoBackups']);
        Route::get('/auto/{backup}/download', [Client\Servers\BackupController::class, 'downloadAutomatedBackup'])
            ->whereNumber('backup');
        Route::get('/auto/{backup}/download-file', [Client\Servers\BackupController::class, 'streamAutomatedBackupDownload'])
            ->whereNumber('backup');
        Route::middleware([ResourceLimit::Backup->middleware()])
            ->post('/auto/{backup}/restore', [Client\Servers\BackupController::class, 'restoreAutomatedBackup'])
            ->whereNumber('backup');
        Route::get('/{backup}', [Client\Servers\BackupController::class, 'view']);
        Route::get('/{backup}/download', [Client\Servers\BackupController::class, 'download']);
        Route::post('/{backup}/lock', [Client\Servers\BackupController::class, 'toggleLock']);
        Route::middleware([ResourceLimit::Backup->middleware()])
            ->post('/{backup}/restore', [Client\Servers\BackupController::class, 'restore']);
        Route::delete('/{backup}', [Client\Servers\BackupController::class, 'delete']);
        Route::post('/{backup}/force-fail', [Client\Servers\BackupController::class, 'forceFail']);
    });

    Route::group(['prefix' => '/startup'], function () {
        Route::get('/', [Client\Servers\StartupController::class, 'index']);
        Route::put('/variable', [Client\Servers\StartupController::class, 'update']);
    });

    Route::group(['prefix' => '/settings'], function () {
        Route::post('/rename', [Client\Servers\SettingsController::class, 'rename']);
        Route::post('/reinstall', [Client\Servers\SettingsController::class, 'reinstall']);
        Route::put('/docker-image', [Client\Servers\SettingsController::class, 'dockerImage']);
    });



});

Route::group(['prefix' => '/addons/server-splitter'], function () {
    Route::get('/whitelist', [Client\Servers\DGEN\ServerSplitterWhitelistController::class, 'index']);
    Route::post('/whitelist', [Client\Servers\DGEN\ServerSplitterWhitelistController::class, 'store']);
    Route::put('/whitelist/{id}', [Client\Servers\DGEN\ServerSplitterWhitelistController::class, 'update']);
    Route::delete('/whitelist/{id}', [Client\Servers\DGEN\ServerSplitterWhitelistController::class, 'destroy']);
    Route::get('/search', [Client\Servers\DGEN\ServerSplitterWhitelistController::class, 'searchServers']);

    Route::get('/legacy-splits', [Client\Servers\DGEN\ServerSplitterMigrationController::class, 'getLegacySplits']);
    Route::post('/legacy-splits/migrate', [Client\Servers\DGEN\ServerSplitterMigrationController::class, 'migrateLegacySplit']);
    Route::get('/users', [Client\Servers\DGEN\ServerSplitterMigrationController::class, 'searchUsers']);
    Route::get('/users/{id}/servers', [Client\Servers\DGEN\ServerSplitterMigrationController::class, 'getUserServers']);
    Route::post('/hook', [Client\Servers\DGEN\ServerSplitterMigrationController::class, 'hookServer']);
});

Route::group(['prefix' => '/addons/server-type-changer'], function () {
    Route::get('/whitelist', [Client\Servers\DGEN\ServerTypeChangerWhitelistController::class, 'index']);
    Route::post('/whitelist', [Client\Servers\DGEN\ServerTypeChangerWhitelistController::class, 'store']);
    Route::get('/whitelist/search', [Client\Servers\DGEN\ServerTypeChangerWhitelistController::class, 'searchServers']);
    Route::delete('/whitelist/{serverIdentifier}', [Client\Servers\DGEN\ServerTypeChangerWhitelistController::class, 'destroy']);
});

Route::group(['prefix' => '/addons/reverse-proxy'], function () {
    Route::get('/whitelist', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'index']);
    Route::post('/whitelist', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'store']);
    Route::put('/whitelist/{id}', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'update']);
    Route::delete('/whitelist/{id}', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'destroy']);
    Route::get('/search', [Client\Servers\DGEN\ReverseProxyWhitelistController::class, 'searchServers']);
});

Route::group(['prefix' => '/addons/fastdl-nginx'], function () {
    Route::post('/setup', [Client\DGEN\FastDLNginxController::class, 'setup']);
    Route::post('/remove', [Client\DGEN\FastDLNginxController::class, 'remove']);
    Route::post('/status', [Client\DGEN\FastDLNginxController::class, 'status']);
});
