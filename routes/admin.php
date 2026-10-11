<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Admin;
use Pterodactyl\Http\Middleware\Admin\Servers\ServerInstalled;

Route::get('/', [Admin\BaseController::class, 'index'])->name('admin.index');

Route::group(['prefix' => 'audit-log'], function () {
    Route::get('/', [Admin\AuditLogController::class, 'index'])->name('admin.audit-log');
    Route::delete('/clear', [Admin\AuditLogController::class, 'clear'])->name('admin.audit-log.clear');
});


    Route::group(['prefix' => 'panel-logs'], function () {
        Route::get('/', [Admin\PanelLogsController::class, 'index'])->name('admin.panel-logs');
        Route::get('/list', [Admin\PanelLogsController::class, 'list'])->name('admin.panel-logs.list');
        Route::get('/history', [Admin\PanelLogsController::class, 'history'])->name('admin.panel-logs.history');
        Route::get('/stream', [Admin\PanelLogsController::class, 'stream'])->name('admin.panel-logs.stream');
    });


Route::get('/statistics', [Admin\AdminStatisticsController::class, 'index'])->name('admin.statistics');
Route::get('/statistics/live-stats', [Admin\AdminStatisticsController::class, 'liveStats'])->name('admin.statistics.live');
Route::get('/statistics/agent-endpoints', [Admin\AdminStatisticsController::class, 'agentEndpoints'])->name('admin.statistics.agent-endpoints');


Route::group(['prefix' => 'api'], function () {
    Route::get('/', [Admin\ApiController::class, 'index'])->name('admin.api.index');
    Route::get('/new', [Admin\ApiController::class, 'create'])->name('admin.api.new');

    Route::post('/new', [Admin\ApiController::class, 'store'])->name('admin.api.store');

    Route::delete('/revoke/{identifier}', [Admin\ApiController::class, 'delete'])->name('admin.api.delete');
});

Route::group(['prefix' => 'locations'], function () {
    Route::get('/', [Admin\LocationController::class, 'index'])->name('admin.locations');
    Route::get('/view/{location:id}', [Admin\LocationController::class, 'view'])->name('admin.locations.view');

    Route::post('/', [Admin\LocationController::class, 'create'])->name('admin.locations.store');
    Route::patch('/view/{location:id}', [Admin\LocationController::class, 'update'])->name('admin.locations.update');
});

Route::group(['prefix' => 'databases'], function () {
    Route::get('/', [Admin\DatabaseController::class, 'index'])->name('admin.databases');
    Route::get('/view/{host:id}', [Admin\DatabaseController::class, 'view'])->name('admin.databases.view');

    Route::post('/', [Admin\DatabaseController::class, 'create'])->name('admin.databases.store');
    Route::patch('/view/{host:id}', [Admin\DatabaseController::class, 'update'])->name('admin.databases.update');
    Route::delete('/view/{host:id}', [Admin\DatabaseController::class, 'delete'])->name('admin.databases.delete');
});

Route::group(['prefix' => 'settings'], function () {
    Route::get('/', [Admin\Settings\IndexController::class, 'index'])->name('admin.settings');
    Route::get('/mail', [Admin\Settings\MailController::class, 'index'])->name('admin.settings.mail');
    Route::get('/advanced', [Admin\Settings\AdvancedController::class, 'index'])->name('admin.settings.advanced');

    Route::post('/mail/test', [Admin\Settings\MailController::class, 'test'])->name('admin.settings.mail.test');

    Route::patch('/', [Admin\Settings\IndexController::class, 'update'])->name('admin.settings.update');
    Route::patch('/mail', [Admin\Settings\MailController::class, 'update'])->name('admin.settings.mail.update');
    Route::patch('/advanced', [Admin\Settings\AdvancedController::class, 'update'])->name('admin.settings.advanced.update');
});

Route::group(['prefix' => 'users'], function () {
    Route::get('/', [Admin\UserController::class, 'index'])->name('admin.users');
    Route::get('/accounts.json', [Admin\UserController::class, 'json'])->name('admin.users.json');
    Route::get('/new', [Admin\UserController::class, 'create'])->name('admin.users.new');
    Route::get('/view/{user:id}', [Admin\UserController::class, 'view'])->name('admin.users.view');

    Route::post('/new', [Admin\UserController::class, 'store'])->name('admin.users.store');

    Route::patch('/view/{user:id}', [Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/view/{user:id}', [Admin\UserController::class, 'delete'])->name('admin.users.delete');
    Route::delete('/view/{user:id}/session/{session}', [Admin\UserController::class, 'revokeSession'])->name('admin.users.session.revoke');
    Route::delete('/view/{user:id}/sessions', [Admin\UserController::class, 'revokeAllSessions'])->name('admin.users.session.revoke_all');
    Route::post('/view/{user:id}/impersonate', [Admin\UserController::class, 'impersonate'])->name('admin.users.impersonate');
});

Route::group(['prefix' => 'servers'], function () {
    Route::get('/', [Admin\Servers\ServerController::class, 'index'])->name('admin.servers');
    Route::post('/mass/action', [Admin\Servers\MassServerActionController::class, 'massAction'])->name('admin.servers.mass.action');
    Route::post('/mass/transfer', [Admin\Servers\MassServerActionController::class, 'massTransfer'])->name('admin.servers.mass.transfer');
    Route::get('/new', [Admin\Servers\CreateServerController::class, 'index'])->name('admin.servers.new');
    Route::get('/view/{server:id}', [Admin\Servers\ServerViewController::class, 'index'])->name('admin.servers.view');

    Route::group(['middleware' => [ServerInstalled::class]], function () {
        Route::get('/view/{server:id}/details', [Admin\Servers\ServerViewController::class, 'details'])->name('admin.servers.view.details');
        Route::get('/view/{server:id}/build', [Admin\Servers\ServerViewController::class, 'build'])->name('admin.servers.view.build');
        Route::get('/view/{server:id}/startup', [Admin\Servers\ServerViewController::class, 'startup'])->name('admin.servers.view.startup');
        Route::get('/view/{server:id}/database', [Admin\Servers\ServerViewController::class, 'database'])->name('admin.servers.view.database');
        Route::get('/view/{server:id}/mounts', [Admin\Servers\ServerViewController::class, 'mounts'])->name('admin.servers.view.mounts');
    });

    Route::get('/view/{server:id}/manage', [Admin\Servers\ServerViewController::class, 'manage'])->name('admin.servers.view.manage');
    Route::get('/view/{server:id}/delete', [Admin\Servers\ServerViewController::class, 'delete'])->name('admin.servers.view.delete');

    Route::post('/new', [Admin\Servers\CreateServerController::class, 'store'])->name('admin.servers.store');
    Route::post('/view/{server:id}/build', [Admin\ServersController::class, 'updateBuild'])->name('admin.servers.view.build.update');
    Route::post('/view/{server:id}/startup', [Admin\ServersController::class, 'saveStartup'])->name('admin.servers.view.startup.update');
    Route::post('/view/{server:id}/database', [Admin\ServersController::class, 'newDatabase'])->name('admin.servers.view.database.store');
    Route::post('/view/{server:id}/mounts', [Admin\ServersController::class, 'addMount'])->name('admin.servers.view.mounts.store');
    Route::post('/view/{server:id}/manage/toggle', [Admin\ServersController::class, 'toggleInstall'])->name('admin.servers.view.manage.toggle');
    Route::post('/view/{server:id}/manage/suspension', [Admin\ServersController::class, 'manageSuspension'])->name('admin.servers.view.manage.suspension');
    Route::post('/view/{server:id}/manage/reinstall', [Admin\ServersController::class, 'reinstallServer'])->name('admin.servers.view.manage.reinstall');
    Route::post('/view/{server:id}/manage/transfer', [Admin\Servers\ServerTransferController::class, 'transfer'])->name('admin.servers.view.manage.transfer');
    Route::post('/view/{server:id}/manage/transfer/force-clear', [Admin\Servers\ServerTransferController::class, 'forceClearTransfer'])->name('admin.servers.view.manage.transfer.force-clear');
    Route::get('/view/{server:id}/manage/transfer/progress', [Admin\Servers\ServerTransferController::class, 'transferProgress'])->name('admin.servers.view.manage.transfer.progress');
    Route::get('/view/{server:id}/manage/transfer/verify-dest', [Admin\Servers\ServerTransferController::class, 'verifyTransferDest'])->name('admin.servers.view.manage.transfer.verify-dest');
    Route::post('/view/{server:id}/manage/transfer/confirm-delete-source', [Admin\Servers\ServerTransferController::class, 'confirmDeleteSource'])->name('admin.servers.view.manage.transfer.confirm-delete-source');
    Route::post('/view/{server:id}/manage/transfer/cancel-confirmation', [Admin\Servers\ServerTransferController::class, 'cancelConfirmation'])->name('admin.servers.view.manage.transfer.cancel-confirmation');
    Route::post('/view/{server:id}/manage/transfer/resync', [Admin\Servers\ServerTransferController::class, 'startResync'])->name('admin.servers.view.manage.transfer.resync');
    Route::get('/view/{server:id}/manage/transfer/resync/progress', [Admin\Servers\ServerTransferController::class, 'resyncProgress'])->name('admin.servers.view.manage.transfer.resync.progress');
    Route::post('/view/{server:id}/manage/transfer/force-transfer', [Admin\Servers\ServerTransferController::class, 'startForceTransfer'])->name('admin.servers.view.manage.transfer.force-transfer');
    Route::get('/view/{server:id}/manage/transfer/force-transfer/progress', [Admin\Servers\ServerTransferController::class, 'forceTransferProgress'])->name('admin.servers.view.manage.transfer.force-transfer.progress');
    Route::post('/view/{server:id}/delete', [Admin\ServersController::class, 'delete'])->name('admin.servers.view.delete.post');

    Route::patch('/view/{server:id}/details', [Admin\ServersController::class, 'setDetails'])->name('admin.servers.view.details.update');
    Route::patch('/view/{server:id}/database', [Admin\ServersController::class, 'resetDatabasePassword'])->name('admin.servers.view.database.update');

    Route::delete('/view/{server:id}/database/{database:id}/delete', [Admin\ServersController::class, 'deleteDatabase'])->name('admin.servers.view.database.delete');
    Route::delete('/view/{server:id}/mounts/{mount:id}', [Admin\ServersController::class, 'deleteMount'])
        ->name('admin.servers.view.mounts.delete');
});

Route::group(['prefix' => 'nodes'], function () {
    Route::get('/', [Admin\Nodes\NodeController::class, 'index'])->name('admin.nodes');
    Route::post('/reorder', [Admin\Nodes\NodeController::class, 'reorder'])->name('admin.nodes.reorder');
    Route::post('/mass/app-name', [Admin\Nodes\WingsNodeStatsController::class, 'massUpdateAppName'])->name('admin.nodes.mass.app-name');
    Route::post('/mass/wings-control', [Admin\Nodes\WingsNodeStatsController::class, 'massWingsControl'])->name('admin.nodes.mass.wings-control');
    Route::get('/new', [Admin\NodesController::class, 'create'])->name('admin.nodes.new');
    Route::get('/view/{node:id}', [Admin\Nodes\NodeViewController::class, 'index'])->name('admin.nodes.view');
    Route::get('/view/{node:id}/settings', [Admin\Nodes\NodeViewController::class, 'settings'])->name('admin.nodes.view.settings');
    Route::get('/view/{node:id}/configuration', [Admin\Nodes\NodeViewController::class, 'configuration'])->name('admin.nodes.view.configuration');
    Route::get('/view/{node:id}/allocation', [Admin\Nodes\NodeViewController::class, 'allocations'])->name('admin.nodes.view.allocation');
    Route::get('/view/{node:id}/servers', [Admin\Nodes\NodeViewController::class, 'servers'])->name('admin.nodes.view.servers');
    Route::get('/view/{node:id}/system-information', Admin\Nodes\SystemInformationController::class)->name('admin.nodes.view.system-information');
    Route::get('/view/{node:id}/wings-stats', [Admin\Nodes\NodeViewController::class, 'wingsStats'])->name('admin.nodes.view.wings-stats');
    Route::get('/view/{node:id}/wings-agent/ticket', [Admin\Nodes\WingsNodeStatsController::class, 'agentTicket'])->name('admin.nodes.wings-agent.ticket');
    Route::get('/view/{node:id}/wings-stats/fetch', [Admin\Nodes\WingsNodeStatsController::class, 'fetch'])->name('admin.nodes.wings-stats.fetch');
    Route::get('/view/{node:id}/wings-stats/ports', [Admin\Nodes\WingsNodeStatsController::class, 'portsList'])->name('admin.nodes.wings-stats.ports');
    Route::get('/view/{node:id}/wings-stats/history', [Admin\Nodes\WingsNodeStatsController::class, 'history'])->name('admin.nodes.wings-stats.history');
    Route::get('/view/{node:id}/wings-stats/port/{port}', [Admin\Nodes\NodeViewController::class, 'wingsPortDetail'])->name('admin.nodes.wings-stats.port-view');
    Route::get('/view/{node:id}/wings-stats/port/{port}/detail', [Admin\Nodes\WingsNodeStatsController::class, 'portDetail'])->name('admin.nodes.wings-stats.port-detail');
    Route::get('/view/{node:id}/wings-stats/port/{port}/history', [Admin\Nodes\WingsNodeStatsController::class, 'portHistory'])->name('admin.nodes.wings-stats.port-history');
    Route::get('/view/{node:id}/wings-service/status', [Admin\Nodes\WingsNodeStatsController::class, 'wingsServiceStatus'])->name('admin.nodes.wings-service.status');
    Route::post('/view/{node:id}/wings-service/control', [Admin\Nodes\WingsNodeStatsController::class, 'wingsServiceControl'])->name('admin.nodes.wings-service.control');
    Route::post('/view/{node:id}/system/reboot', [Admin\Nodes\WingsNodeStatsController::class, 'systemReboot'])->name('admin.nodes.system.reboot');
    Route::get('/view/{node:id}/radar', [Admin\Nodes\NodeViewController::class, 'radar'])->name('admin.nodes.view.radar');
    Route::get('/view/{node:id}/radar/status', [Admin\Nodes\RadarController::class, 'status'])->name('admin.nodes.radar.status');
    Route::get('/view/{node:id}/radar/findings', [Admin\Nodes\RadarController::class, 'findings'])->name('admin.nodes.radar.findings');
    Route::get('/view/{node:id}/radar/network', [Admin\Nodes\RadarController::class, 'network'])->name('admin.nodes.radar.network');
    Route::get('/view/{node:id}/radar/processes', [Admin\Nodes\RadarController::class, 'processes'])->name('admin.nodes.radar.processes');
    Route::post('/view/{node:id}/radar/scan', [Admin\Nodes\RadarController::class, 'scan'])->name('admin.nodes.radar.scan');
    Route::post('/view/{node:id}/radar/scan-all', [Admin\Nodes\RadarController::class, 'scanAll'])->name('admin.nodes.radar.scan-all');
    Route::post('/view/{node:id}/radar/file-action', [Admin\Nodes\RadarController::class, 'fileAction'])->name('admin.nodes.radar.file-action');
    Route::post('/view/{node:id}/radar/baseline', [Admin\Nodes\RadarController::class, 'baseline'])->name('admin.nodes.radar.baseline');
    Route::post('/view/{node:id}/radar/lock/apply', [Admin\Nodes\RadarController::class, 'lockApply'])->name('admin.nodes.radar.lock-apply');
    Route::post('/view/{node:id}/radar/lock/release', [Admin\Nodes\RadarController::class, 'lockRelease'])->name('admin.nodes.radar.lock-release');
        Route::get('/view/{node:id}/firewall', [Admin\Nodes\NodeViewController::class, 'firewall'])->name('admin.nodes.view.firewall');
        Route::get('/view/{node:id}/firewall/rules', [Admin\Nodes\WingsNodeStatsController::class, 'firewallRules'])->name('admin.nodes.firewall.rules');
        Route::get('/view/{node:id}/firewall/chains', [Admin\Nodes\WingsNodeStatsController::class, 'firewallChains'])->name('admin.nodes.firewall.chains');
        Route::post('/view/{node:id}/firewall/rule/add', [Admin\Nodes\WingsNodeStatsController::class, 'firewallAddRule'])->name('admin.nodes.firewall.rule-add');
        Route::post('/view/{node:id}/firewall/rule/delete', [Admin\Nodes\WingsNodeStatsController::class, 'firewallDeleteRule'])->name('admin.nodes.firewall.rule-delete');
        Route::post('/view/{node:id}/firewall/flush', [Admin\Nodes\WingsNodeStatsController::class, 'firewallFlush'])->name('admin.nodes.firewall.flush');
        Route::get('/view/{node:id}/firewall/port/{port}', [Admin\Nodes\WingsNodeStatsController::class, 'firewallPortRules'])->name('admin.nodes.firewall.port-rules');
        Route::post('/view/{node:id}/firewall/port/{port}/block', [Admin\Nodes\WingsNodeStatsController::class, 'firewallBlockPort'])->name('admin.nodes.firewall.port-block');
        Route::post('/view/{node:id}/firewall/port/{port}/allow', [Admin\Nodes\WingsNodeStatsController::class, 'firewallAllowPort'])->name('admin.nodes.firewall.port-allow');
    Route::get('/wings-agent/versions', [Admin\Nodes\WingsNodeStatsController::class, 'agentVersionsAll'])->name('admin.nodes.wings-agent.versions');
    Route::get('/view/{node:id}/wings-agent/version', [Admin\Nodes\WingsNodeStatsController::class, 'agentVersionCheck'])->name('admin.nodes.wings-agent.version');
    Route::post('/view/{node:id}/wings-agent/update', [Admin\Nodes\WingsNodeStatsController::class, 'triggerAgentUpdate'])->name('admin.nodes.wings-agent.update');
    Route::get('/wings-daemon/versions', [Admin\Nodes\WingsNodeStatsController::class, 'wingsDaemonVersionsAll'])->name('admin.nodes.wings-daemon.versions');
    Route::get('/view/{node:id}/wings-daemon/version', [Admin\Nodes\WingsNodeStatsController::class, 'wingsDaemonVersionCheck'])->name('admin.nodes.wings-daemon.version');
    Route::post('/view/{node:id}/wings-daemon/update', [Admin\Nodes\WingsNodeStatsController::class, 'triggerWingsDaemonUpdate'])->name('admin.nodes.wings-daemon.update');
    Route::post('/view/{node:id}/motd/config', [Admin\Nodes\WingsNodeStatsController::class, 'pushMotdConfig'])->name('admin.nodes.motd.push');
    Route::get('/view/{node:id}/motd/status', [Admin\Nodes\WingsNodeStatsController::class, 'getMotdStatus'])->name('admin.nodes.motd.status');

    Route::post('/new', [Admin\NodesController::class, 'store'])->name('admin.nodes.store');

    Route::get('/view/{node:id}/logs', [Admin\Nodes\NodeViewController::class, 'nodeLogs'])->name('admin.nodes.view.logs');
    Route::get('/view/{node:id}/logs/list', [Admin\Nodes\WingsNodeStatsController::class, 'nodeLogsList'])->name('admin.nodes.logs.list');
    Route::get('/view/{node:id}/logs/history', [Admin\Nodes\WingsNodeStatsController::class, 'nodeLogsHistory'])->name('admin.nodes.logs.history');
    Route::get('/view/{node:id}/logs/stream', [Admin\Nodes\WingsNodeStatsController::class, 'nodeLogsStream'])->name('admin.nodes.logs.stream');

    Route::get('/view/{node:id}/backups', [Admin\Nodes\NodeViewController::class, 'backups'])->name('admin.nodes.view.backups');
    Route::post('/view/{node:id}/backups/config', [Admin\Nodes\NodeBackupController::class, 'storeConfig'])->name('admin.nodes.backups.config');
    Route::post('/view/{node:id}/backups/trigger', [Admin\Nodes\NodeBackupController::class, 'trigger'])->name('admin.nodes.backups.trigger');
    Route::post('/view/{node:id}/backups/restore', [Admin\Nodes\NodeBackupController::class, 'restore'])->name('admin.nodes.backups.restore');
    Route::get('/view/{node:id}/backups/restore-status', [Admin\Nodes\NodeBackupController::class, 'restoreStatus'])->name('admin.nodes.backups.restore-status');
    Route::post('/view/{node:id}/backups/test-backend', [Admin\Nodes\NodeBackupController::class, 'testBackend'])->name('admin.nodes.backups.test-backend');
    Route::delete('/view/{node:id}/backups/delete', [Admin\Nodes\NodeBackupController::class, 'deleteBackup'])->name('admin.nodes.backups.delete');
    Route::get('/view/{node:id}/backups/history', [Admin\Nodes\NodeBackupController::class, 'history'])->name('admin.nodes.backups.history');
    Route::get('/view/{node:id}/backups/history-by-run', [Admin\Nodes\NodeBackupController::class, 'historyByRun'])->name('admin.nodes.backups.history-by-run');
    Route::get('/view/{node:id}/backups/run/{runId}', [Admin\Nodes\NodeBackupController::class, 'runDetail'])->name('admin.nodes.backups.run-detail');
    Route::get('/view/{node:id}/backups/progress', [Admin\Nodes\NodeBackupController::class, 'progressStream'])->name('admin.nodes.backups.progress');
    Route::get('/view/{node:id}/backups/status', [Admin\Nodes\NodeBackupController::class, 'backupStatus'])->name('admin.nodes.backups.status');
    Route::get('/view/{node:id}/backups/stats', [Admin\Nodes\NodeBackupController::class, 'stats'])->name('admin.nodes.backups.stats');
    Route::get('/view/{node:id}/backups/list', [Admin\Nodes\NodeBackupController::class, 'getList'])->name('admin.nodes.backups.list');
    Route::post('/view/{node:id}/backups/list/add', [Admin\Nodes\NodeBackupController::class, 'addToList'])->name('admin.nodes.backups.list.add');
    Route::post('/view/{node:id}/backups/list/remove', [Admin\Nodes\NodeBackupController::class, 'removeFromList'])->name('admin.nodes.backups.list.remove');
    Route::get('/view/{node:id}/backups/available-servers', [Admin\Nodes\NodeBackupController::class, 'availableServers'])->name('admin.nodes.backups.available-servers');
    Route::get('/view/{node:id}/backups/check-runs', [Admin\Nodes\NodeBackupController::class, 'checkRuns'])->name('admin.nodes.backups.check-runs');
    Route::delete('/view/{node:id}/backups/run/{runId}', [Admin\Nodes\NodeBackupController::class, 'adminDeleteRun'])->name('admin.nodes.backups.run.delete');
    Route::delete('/view/{node:id}/backups/entry/{backupId}', [Admin\Nodes\NodeBackupController::class, 'adminDeleteEntry'])->name('admin.nodes.backups.entry.delete');
    Route::post('/view/{node:id}/transfer/test', [Admin\Nodes\NodeBackupController::class, 'transferTest'])->name('admin.nodes.transfer.test');
    Route::post('/view/{node:id}/allocation', [Admin\NodesController::class, 'createAllocation'])->name('admin.nodes.view.allocation.store');
    Route::post('/view/{node:id}/allocation/remove', [Admin\NodesController::class, 'allocationRemoveBlock'])->name('admin.nodes.view.allocation.removeBlock');
    Route::post('/view/{node:id}/allocation/alias', [Admin\NodesController::class, 'allocationSetAlias'])->name('admin.nodes.view.allocation.setAlias');
    Route::post('/view/{node:id}/settings/token', Admin\NodeAutoDeployController::class)->name('admin.nodes.view.configuration.token');

    Route::patch('/view/{node:id}/settings', [Admin\NodesController::class, 'updateSettings'])->name('admin.nodes.view.settings.update');

    Route::delete('/view/{node:id}/delete', [Admin\NodesController::class, 'delete'])->name('admin.nodes.view.delete');
    Route::delete('/view/{node:id}/allocation/remove/{allocation:id}', [Admin\NodesController::class, 'allocationRemoveSingle'])->name('admin.nodes.view.allocation.removeSingle');
    Route::delete('/view/{node:id}/allocations', [Admin\NodesController::class, 'allocationRemoveMultiple'])->name('admin.nodes.view.allocation.removeMultiple');
});

Route::prefix('api/global-storage-backends')->group(function () {
    Route::get('/', [Admin\GlobalStorageBackendController::class, 'index'])->name('admin.global-storage-backends.index');
    Route::post('/', [Admin\GlobalStorageBackendController::class, 'store'])->name('admin.global-storage-backends.store');
    Route::put('/{id}', [Admin\GlobalStorageBackendController::class, 'update'])->name('admin.global-storage-backends.update');
    Route::delete('/{id}', [Admin\GlobalStorageBackendController::class, 'destroy'])->name('admin.global-storage-backends.destroy');
    Route::post('/test', [Admin\GlobalStorageBackendController::class, 'test'])->name('admin.global-storage-backends.test');
    Route::post('/{id}/test-secure', [Admin\GlobalStorageBackendController::class, 'testById'])->name('admin.global-storage-backends.test-secure');
    Route::post('/{id}/assign-node', [Admin\GlobalStorageBackendController::class, 'assignNode'])->name('admin.global-storage-backends.assign-node');
    Route::post('/{id}/unassign-node', [Admin\GlobalStorageBackendController::class, 'unassignNode'])->name('admin.global-storage-backends.unassign-node');
    Route::post('/{id}/set-default-for-node', [Admin\GlobalStorageBackendController::class, 'setDefaultForNode'])->name('admin.global-storage-backends.set-default-for-node');
    Route::post('/{id}/clear-default-for-node', [Admin\GlobalStorageBackendController::class, 'clearDefaultForNode'])->name('admin.global-storage-backends.clear-default-for-node');
});

Route::group(['prefix' => 'mounts'], function () {
    Route::get('/', [Admin\MountController::class, 'index'])->name('admin.mounts');
    Route::get('/view/{mount:id}', [Admin\MountController::class, 'view'])->name('admin.mounts.view');

    Route::post('/', [Admin\MountController::class, 'create'])->name('admin.mounts.store');
    Route::post('/{mount:id}/eggs', [Admin\MountController::class, 'addEggs'])->name('admin.mounts.eggs');
    Route::post('/{mount:id}/nodes', [Admin\MountController::class, 'addNodes'])->name('admin.mounts.nodes');

    Route::patch('/view/{mount:id}', [Admin\MountController::class, 'update'])->name('admin.mounts.update');

    Route::delete('/{mount:id}/eggs/{egg_id}', [Admin\MountController::class, 'deleteEgg'])->name('admin.mounts.eggs.delete');
    Route::delete('/{mount:id}/nodes/{node_id}', [Admin\MountController::class, 'deleteNode'])->name('admin.mounts.nodes.delete');
});

Route::group(['prefix' => 'nests'], function () {
    Route::get('/', [Admin\Nests\NestController::class, 'index'])->name('admin.nests');
    Route::get('/new', [Admin\Nests\NestController::class, 'create'])->name('admin.nests.new');
    Route::get('/view/{nest:id}', [Admin\Nests\NestController::class, 'view'])->name('admin.nests.view');
    Route::get('/egg/new', [Admin\Nests\EggController::class, 'create'])->name('admin.nests.egg.new');
    Route::get('/egg/{egg:id}', [Admin\Nests\EggController::class, 'view'])->name('admin.nests.egg.view');
    Route::get('/egg/{egg:id}/export', [Admin\Nests\EggShareController::class, 'export'])->name('admin.nests.egg.export');
    Route::get('/egg/{egg:id}/variables', [Admin\Nests\EggVariableController::class, 'view'])->name('admin.nests.egg.variables');
    Route::get('/egg/{egg:id}/scripts', [Admin\Nests\EggScriptController::class, 'index'])->name('admin.nests.egg.scripts');

    Route::post('/new', [Admin\Nests\NestController::class, 'store'])->name('admin.nests.store');
    Route::post('/import', [Admin\Nests\EggShareController::class, 'import'])->name('admin.nests.egg.import');
    Route::get('/egg/remote', [Admin\Nests\EggRemoteController::class, 'index'])->name('admin.nests.egg.remote.index');
    Route::post('/egg/remote/import', [Admin\Nests\EggRemoteController::class, 'import'])->name('admin.nests.egg.remote.import');
    Route::post('/egg/new', [Admin\Nests\EggController::class, 'store'])->name('admin.nests.egg.store');
    Route::post('/egg/{egg:id}/variables', [Admin\Nests\EggVariableController::class, 'store'])->name('admin.nests.egg.variables.store');

    Route::put('/egg/{egg:id}', [Admin\Nests\EggShareController::class, 'update'])->name('admin.nests.egg.share.update');

    Route::patch('/view/{nest:id}', [Admin\Nests\NestController::class, 'update'])->name('admin.nests.update');
    Route::patch('/egg/{egg:id}', [Admin\Nests\EggController::class, 'update'])->name('admin.nests.egg.update');
    Route::patch('/egg/{egg:id}/scripts', [Admin\Nests\EggScriptController::class, 'update'])->name('admin.nests.egg.scripts.update');
    Route::patch('/egg/{egg:id}/variables/{variable:id}', [Admin\Nests\EggVariableController::class, 'update'])->name('admin.nests.egg.variables.edit');

    Route::delete('/view/{nest:id}', [Admin\Nests\NestController::class, 'destroy'])->name('admin.nests.delete');
    Route::delete('/egg/{egg:id}', [Admin\Nests\EggController::class, 'destroy'])->name('admin.nests.egg.delete');
    Route::delete('/egg/{egg:id}/variables/{variable:id}', [Admin\Nests\EggVariableController::class, 'destroy'])->name('admin.nests.egg.variables.delete');
});

Route::group(['prefix' => 'api/DGEN', 'as' => 'admin.DGEN.'], function () {
    Route::get('/roles', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'index'])->name('roles');
    Route::post('/roles', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'show'])->name('roles.show');
    Route::put('/roles/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/permissions', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'listPermissions'])->name('permissions');
    
    Route::get('/members', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'members'])->name('members');
    Route::post('/members/{user:id}/assign', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'assignUser'])->name('members.assign');
    Route::post('/members/{user:id}/unassign', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\PermissionRoleController::class, 'unassignUser'])->name('members.unassign');

    Route::group(['prefix' => 'billing'], function () {
        Route::get('/categories', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'getCategories']);
        Route::post('/categories', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'upsertCategory']);
        Route::delete('/categories/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'deleteCategory']);

        Route::get('/subcategories', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'getSubcategories']);
        Route::post('/subcategories', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'upsertSubcategory']);
        Route::delete('/subcategories/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'deleteSubcategory']);

        Route::get('/games', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'getGames']);
        Route::post('/games', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'upsertGame']);
        Route::put('/games/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'upsertGame']);
        Route::delete('/games/{id}', [Pterodactyl\Http\Controllers\Api\Client\Admin\DGEN\AdminBillingController::class, 'deleteGame']);

        Route::get('/promocodes', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\PromoCodeController::class, 'index']);
        Route::post('/promocodes', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\PromoCodeController::class, 'store']);
        Route::put('/promocodes/{id}', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\PromoCodeController::class, 'update']);
        Route::delete('/promocodes/{id}', [Pterodactyl\Http\Controllers\Api\Client\DGEN\Billing\PromoCodeController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| Playit.gg integration (Turtle addon)
|--------------------------------------------------------------------------
| Keys are pooled here and handed to game servers. The panel then reads the
| connectable address from the playit API and mirrors it into the server's
| allocation alias (the field the client renders as the connection address).
*/
Route::group(['prefix' => 'playit'], function () {
    Route::get('/', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'index'])->name('admin.playit');
    Route::post('/keys', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'addKeys'])->name('admin.playit.keys');
    Route::post('/assign', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'assign'])->name('admin.playit.assign');
    Route::post('/release', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'release'])->name('admin.playit.release');
    Route::post('/delete', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'destroy'])->name('admin.playit.delete');
    Route::post('/sync', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'sync'])->name('admin.playit.sync');
    Route::post('/token', [\Turtle\Playit\Http\Controllers\Admin\PlayitController::class, 'rotateToken'])->name('admin.playit.token');
});
