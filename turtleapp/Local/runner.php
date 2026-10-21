<?php

/*
 * Turtle Game Panel - native (local node) runtime runner.
 *
 * Detached CLI entry point used for two things that must not run inside a web
 * request:
 *
 *     php turtleapp/Local/runner.php install <id>    # download jar + plugin
 *     php turtleapp/Local/runner.php restore         # boot: restart servers
 *
 * It bootstraps the panel exactly like artisan does, but without the console
 * application, so it is cheap and its exit code is meaningful.
 */

define('LARAVEL_START', microtime(true));

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require $root . '/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = $argv[1] ?? '';
$id = (int) ($argv[2] ?? 0);

/** @var \Turtle\Local\LocalService $service */
$service = $app->make(\Turtle\Local\LocalService::class);

try {
    switch ($action) {
        case 'install':
            if ($id <= 0) {
                fwrite(STDERR, "usage: runner.php install <id>\n");
                exit(2);
            }
            $service->runInstall($id);
            break;

        case 'restore':
            $service->restoreAll();
            break;

        default:
            fwrite(STDERR, "usage: runner.php <install <id>|restore>\n");
            exit(2);
    }
} catch (\Throwable $e) {
    if ($id > 0) {
        @file_put_contents(
            $service->installLogPath($id),
            '[' . date('Y-m-d H:i:s') . '] FATAL: ' . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }
    fwrite(STDERR, 'runner: ' . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
