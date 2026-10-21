<?php

namespace Turtle\Local;

use Illuminate\Support\Facades\Http;
use Turtle\Local\Models\LocalServer;
use Turtle\Playit\PlayitService;

/**
 * "Pure local node" runtime for the Turtle Game Panel.
 *
 * Servers created here run as normal OS processes on the panel host: no Wings,
 * no Docker, no FQDN. This is the same idea as the JTG panel's "local" runtime.
 *
 * How a server is run
 * -------------------
 *   - The working directory is <base>/<id>.
 *   - The process is launched with `setsid` so it survives the PHP request and
 *     is not killed when the web worker exits.
 *   - Console input is a named pipe (FIFO). The game process opens it read-write
 *     (`exec 3<>fifo`) so it never sees EOF and so the panel can push a command
 *     line to it at any time.
 *   - Console output is appended to console.log, which the panel tails.
 *   - The game PID is written to server.pid (the shell `exec`s java, so the PID
 *     does not change).
 *
 * Nothing here throws at the caller for environmental problems; methods return
 * a small status array instead so a broken host cannot take the panel down.
 */
class LocalService
{
    public const STATUS_NEW = 'new';
    public const STATUS_INSTALLING = 'installing';
    public const STATUS_INSTALLED = 'installed';
    public const STATUS_STARTING = 'starting';
    public const STATUS_RUNNING = 'running';
    public const STATUS_STOPPING = 'stopping';
    public const STATUS_STOPPED = 'stopped';
    public const STATUS_ERROR = 'error';

    /** Where playit's Minecraft plugin lives, relative to a server directory. */
    public const PLAYIT_PLUGIN_DIR = 'plugins/playit-gg';
    public const PLAYIT_PLUGIN_JAR = 'playit-minecraft-plugin.jar';
    public const PLAYIT_PLUGIN_URL = 'https://github.com/playit-cloud/playit-minecraft-plugin/releases/latest/download/playit-minecraft-plugin.jar';

    /** Types the panel can install automatically. */
    public const TYPES = [
        'paper' => 'Paper (recommended)',
        'purpur' => 'Purpur',
        'vanilla' => 'Vanilla (Mojang)',
        'custom' => 'Custom jar (place server.jar yourself)',
    ];

    public function __construct(private PlayitService $playit)
    {
    }

    // =====================================================================
    // Paths + small shell helpers
    // =====================================================================

    public function baseDir(): string
    {
        $dir = (string) (env('TURTLE_LOCAL_DIR') ?: '/var/lib/turtle-local');

        return rtrim($dir, '/');
    }

    public function serverDir(int $id): string
    {
        return $this->baseDir() . '/' . $id;
    }

    public function consoleLogPath(int $id): string
    {
        return $this->serverDir($id) . '/console.log';
    }

    public function installLogPath(int $id): string
    {
        return $this->serverDir($id) . '/install.log';
    }

    public function pidPath(int $id): string
    {
        return $this->serverDir($id) . '/server.pid';
    }

    private function fifoPath(int $id): string
    {
        return $this->serverDir($id) . '/stdin.fifo';
    }

    /** Locate a usable PHP CLI binary for background work. */
    public function phpBinary(): string
    {
        if (PHP_SAPI === 'cli' && is_file(PHP_BINARY)) {
            return PHP_BINARY;
        }

        $candidates = [
            PHP_BINDIR . '/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            PHP_BINDIR . '/php',
            '/usr/bin/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            '/usr/bin/php',
            '/usr/local/bin/php',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return 'php';
    }

    private function run(string $command): string
    {
        if (!function_exists('shell_exec')) {
            return '';
        }

        $out = @shell_exec($command . ' 2>&1');

        return is_string($out) ? $out : '';
    }

    private function runCode(string $command): array
    {
        if (!function_exists('exec')) {
            return [1, []];
        }

        $lines = [];
        $code = 1;
        @exec($command . ' 2>&1', $lines, $code);

        return [$code, $lines];
    }

    public function commandExists(string $binary): bool
    {
        [$code] = $this->runCode('command -v ' . escapeshellarg($binary) . ' >/dev/null 2>&1');

        return $code === 0;
    }

    private function isAlive(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        [$code] = $this->runCode('kill -0 ' . (int) $pid . ' >/dev/null 2>&1');

        return $code === 0;
    }

    private function readPid(int $id): int
    {
        $file = $this->pidPath($id);
        if (!is_file($file)) {
            return 0;
        }

        return (int) trim((string) @file_get_contents($file));
    }

    private function tail(string $path, int $lines = 200, int $maxBytes = 256000): string
    {
        if (!is_file($path)) {
            return '';
        }

        $size = @filesize($path);
        if ($size === false || $size <= 0) {
            return '';
        }

        $read = (int) min($size, $maxBytes);
        $fh = @fopen($path, 'rb');
        if (!$fh) {
            return '';
        }

        @fseek($fh, -$read, SEEK_END);
        $data = (string) @fread($fh, $read);
        @fclose($fh);

        if ($read < $size) {
            $pos = strpos($data, "\n");
            $data = $pos === false ? $data : substr($data, $pos + 1);
        }

        $rows = explode("\n", rtrim($data, "\n"));
        if ($lines > 0 && count($rows) > $lines) {
            $rows = array_slice($rows, -$lines);
        }

        return implode("\n", $rows);
    }

    // =====================================================================
    // Pre-flight
    // =====================================================================

    /**
     * Report whether the host can run native servers at all.
     *
     * @return array{ok:bool,checks:array<int,array{name:string,ok:bool,detail:string}>}
     */
    public function preflight(): array
    {
        $checks = [];

        $base = $this->baseDir();
        $exists = is_dir($base);
        $checks[] = [
            'name' => 'Data directory',
            'ok' => $exists ? is_writable($base) : is_writable(dirname($base)),
            'detail' => $exists
                ? $base . (is_writable($base) ? ' (writable)' : ' (NOT writable by the panel user)')
                : $base . ' does not exist yet (will be created on install)',
        ];

        $java = $this->resolveJavaBinary(null);
        $checks[] = [
            'name' => 'Java runtime',
            'ok' => $java !== null,
            'detail' => $java ?? 'No java binary found. Install a JDK (openjdk-21-jre-headless).',
        ];

        $checks[] = [
            'name' => 'setsid',
            'ok' => $this->commandExists('setsid'),
            'detail' => $this->commandExists('setsid') ? 'available' : 'missing (install util-linux)',
        ];

        $checks[] = [
            'name' => 'Shell execution',
            'ok' => function_exists('exec') && function_exists('shell_exec'),
            'detail' => (function_exists('exec') && function_exists('shell_exec'))
                ? 'exec() enabled'
                : 'exec()/shell_exec() disabled in this PHP install',
        ];

        $ok = true;
        foreach ($checks as $check) {
            if (!$check['ok']) {
                $ok = false;
            }
        }

        return ['ok' => $ok, 'checks' => $checks];
    }

    /**
     * Find a Java binary, preferring one matching the Minecraft version.
     */
    public function resolveJavaBinary(?string $mcVersion): ?string
    {
        $override = (string) (env('TURTLE_JAVA_BIN') ?: '');
        if ($override !== '' && @is_executable($override)) {
            return $override;
        }

        $target = $this->javaMajorFor($mcVersion);
        $candidates = [];

        if ($target !== null) {
            foreach ([$target, 21, 17, 8] as $major) {
                $candidates[] = "/usr/lib/jvm/java-{$major}-openjdk-amd64/bin/java";
                $candidates[] = "/usr/lib/jvm/java-{$major}-openjdk-arm64/bin/java";
                $candidates[] = "/usr/lib/jvm/temurin-{$major}-jdk-amd64/bin/java";
                $candidates[] = "/usr/lib/jvm/java-{$major}-openjdk/bin/java";
            }
        }

        $candidates = array_merge($candidates, [
            '/usr/bin/java',
            '/usr/local/bin/java',
            '/usr/lib/jvm/default-java/bin/java',
            '/opt/java/openjdk/bin/java',
        ]);

        foreach ($candidates as $candidate) {
            if (@is_executable($candidate)) {
                return $candidate;
            }
        }

        if ($this->commandExists('java')) {
            return 'java';
        }

        return null;
    }

    /** Best-effort mapping of a Minecraft version to the needed Java major. */
    private function javaMajorFor(?string $mcVersion): ?int
    {
        if (!is_string($mcVersion) || trim($mcVersion) === '') {
            return null;
        }

        if (!preg_match('/^1\.(\d+)/', trim($mcVersion), $m)) {
            return null;
        }

        $minor = (int) $m[1];
        if ($minor >= 21) {
            return 21;
        }
        if ($minor >= 18) {
            return 17;
        }
        if ($minor >= 17) {
            return 16;
        }

        return 8;
    }

    // =====================================================================
    // Create
    // =====================================================================

    /**
     * @param array{name:string,type:string,mc_version:?string,memory_mb:int,port:int,motd:?string,playit:bool,start_now:bool} $data
     * @return array{ok:bool,server:?LocalServer,message:string}
     */
    public function create(array $data, ?int $userId = null): array
    {
        $type = strtolower((string) $data['type']);
        if (!array_key_exists($type, self::TYPES)) {
            return ['ok' => false, 'server' => null, 'message' => 'Unknown server type.'];
        }

        $port = (int) $data['port'];
        if ($port < 1 || $port > 65535) {
            return ['ok' => false, 'server' => null, 'message' => 'Port must be between 1 and 65535.'];
        }

        if (LocalServer::query()->where('port', $port)->exists()) {
            return ['ok' => false, 'server' => null, 'message' => "Port {$port} is already used by another local server."];
        }

        $server = LocalServer::query()->create([
            'uuid' => $this->uuid4(),
            'name' => mb_substr(trim((string) $data['name']), 0, 191) ?: ('local-' . $port),
            'type' => $type,
            'mc_version' => $data['mc_version'] ?: null,
            'memory_mb' => max(512, min(65536, (int) $data['memory_mb'])),
            'port' => $port,
            'motd' => $data['motd'] ?: null,
            'playit' => (bool) $data['playit'],
            'status' => self::STATUS_NEW,
            'created_by' => $userId,
        ]);

        $dir = $this->serverDir($server->id);
        if (!@is_dir($dir) && !@mkdir($dir, 0775, true) && !@is_dir($dir)) {
            $server->forceFill(['status' => self::STATUS_ERROR, 'last_error' => "Could not create {$dir} (permission denied?)"])->save();

            return ['ok' => false, 'server' => $server, 'message' => "Could not create {$dir}. Check that the panel user can write to " . $this->baseDir() . '.'];
        }

        $this->writeServerFiles($server);

        // Claim a playit key up front so the installer can make it headless.
        if ($server->playit) {
            $agent = $this->playit->assignNextToLocal((int) $server->id);
            if ($agent) {
                $server->forceFill(['playit_agent_id' => $agent->id])->save();
            }
        }

        $this->queueInstall((int) $server->id);

        return ['ok' => true, 'server' => $server, 'message' => 'Server created; installing in the background.'];
    }

    /** Write eula.txt + server.properties (idempotent). */
    private function writeServerFiles(LocalServer $server): void
    {
        $dir = $this->serverDir((int) $server->id);
        @mkdir($dir . '/plugins', 0775, true);

        @file_put_contents($dir . '/eula.txt', "# Accepted via the Turtle panel.\neula=true\n");

        $props = [
            'motd' => $server->motd ?: ($server->name . ' - Turtle'),
            'server-port' => (string) $server->port,
            'enable-query' => 'false',
            'online-mode' => 'true',
            'max-players' => '20',
            'difficulty' => 'easy',
            'gamemode' => 'survival',
            'level-name' => 'world',
            'spawn-protection' => '0',
        ];

        $lines = ["# Managed by the Turtle panel (local runtime)."];
        foreach ($props as $key => $value) {
            $lines[] = $key . '=' . $value;
        }

        @file_put_contents($dir . '/server.properties', implode("\n", $lines) . "\n");
    }

    /**
     * Mark the server as installing and launch the background installer.
     * Safe to call from a web request: it returns immediately.
     */
    public function queueInstall(int $id): void
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return;
        }

        $server->forceFill(['status' => self::STATUS_INSTALLING, 'last_error' => null])->save();
        $this->spawnInstall($id);
    }

    /** Launch the background installer (turtleapp/Local/runner.php install <id>). */
    private function spawnInstall(int $id): void
    {
        $runner = dirname(__FILE__) . '/runner.php';
        $log = escapeshellarg($this->installLogPath($id));

        // The runner's stdout+stderr go to install.log so a bootstrap failure is
        // still visible in the panel. (run() appends its own 2>&1 to the whole
        // line, which is a no-op after the backgrounding '&'.)
        $cmd = escapeshellarg($this->phpBinary()) . ' ' . escapeshellarg($runner) . ' install ' . (int) $id
            . ' >> ' . $log . ' 2>&1';

        $this->run('cd ' . escapeshellarg($this->serverDir($id)) . ' && nohup ' . $cmd . ' &');
    }

    /**
     * Download the server jar + (optionally) the playit plugin, then mark the
     * server installed. Runs in a detached CLI process, never in a web request.
     */
    public function runInstall(int $id): void
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return;
        }

        $this->logLine($id, 'Starting install for #' . $id . ' (' . $server->type . ' ' . ($server->mc_version ?: 'latest') . ')');
        $server->forceFill(['status' => self::STATUS_INSTALLING, 'last_error' => null])->save();

        $dir = $this->serverDir($id);
        @mkdir($dir . '/plugins', 0775, true);
        $this->writeServerFiles($server);

        try {
            if (strtolower((string) $server->type) !== 'custom') {
                $this->downloadJar($server);
            } else {
                if (!is_file($dir . '/' . $server->jar)) {
                    throw new \RuntimeException("Custom server: place your jar at {$dir}/{$server->jar} first.");
                }
                $this->logLine($id, 'Custom jar present; skipping download.');
            }

            if ($server->playit && $server->supportsPlugins()) {
                $this->installPlayitPlugin($server);
            } elseif ($server->playit) {
                $this->logLine($id, 'Playit: ' . $server->type . ' does not support the playit plugin; the address must be tunneled another way.');
            }

            $server->forceFill([
                'status' => self::STATUS_INSTALLED,
                'installed_at' => now(),
                'last_error' => null,
            ])->save();

            $this->logLine($id, 'Install finished.');
        } catch (\Throwable $e) {
            $this->logLine($id, 'ERROR: ' . $e->getMessage());
            $server->forceFill(['status' => self::STATUS_ERROR, 'last_error' => mb_substr($e->getMessage(), 0, 500)])->save();
        }
    }

    private function logLine(int $id, string $message): void
    {
        @file_put_contents(
            $this->installLogPath($id),
            '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n",
            FILE_APPEND
        );
    }

    // =====================================================================
    // Jar download
    // =====================================================================

    private function downloadJar(LocalServer $server): void
    {
        $id = (int) $server->id;
        [$url, $version] = $this->resolveJarUrl($server);

        $this->logLine($id, 'Downloading ' . $server->type . ' ' . $version . ' from ' . $url);
        $this->logLine($id, 'Target: ' . $this->serverDir($id) . '/' . $server->jar);

        $tmp = $this->serverDir($id) . '/' . $server->jar . '.new';
        @unlink($tmp);

        $response = Http::timeout(900)->sink($tmp)->get($url);
        if (!$response->successful()) {
            @unlink($tmp);
            throw new \RuntimeException('Download failed with HTTP ' . $response->status() . ' for ' . $url);
        }

        $size = @filesize($tmp);
        if ($size === false || $size < 1024) {
            @unlink($tmp);
            throw new \RuntimeException('Downloaded jar looks empty (' . var_export($size, true) . ' bytes).');
        }

        if (!@rename($tmp, $this->serverDir($id) . '/' . $server->jar)) {
            @unlink($tmp);
            throw new \RuntimeException('Could not move the downloaded jar into place.');
        }

        if ($server->mc_version === null && $version !== '') {
            $server->forceFill(['mc_version' => $version])->save();
        }

        $this->logLine($id, 'Downloaded ' . number_format((float) ($size / 1048576), 1) . ' MiB.');
    }

    /**
     * @return array{0:string,1:string} [download url, resolved version]
     */
    private function resolveJarUrl(LocalServer $server): array
    {
        $type = strtolower((string) $server->type);
        $version = trim((string) $server->mc_version);

        if ($type === 'paper') {
            if ($version === '') {
                $version = $this->lastOf(Http::timeout(30)->get('https://api.papermc.io/v2/projects/paper')->json('versions', []));
            }
            if ($version === '') {
                throw new \RuntimeException('Could not determine a Paper version.');
            }

            $builds = Http::timeout(30)->get("https://api.papermc.io/v2/projects/paper/versions/{$version}/builds")->json('builds', []);
            if (!is_array($builds) || $builds === []) {
                throw new \RuntimeException("No Paper builds found for {$version}.");
            }

            $build = $builds[count($builds) - 1];
            $buildNumber = $build['build'] ?? null;
            $name = $build['downloads']['application']['name'] ?? null;
            if (!$buildNumber || !$name) {
                throw new \RuntimeException('Paper build metadata is incomplete.');
            }

            return ["https://api.papermc.io/v2/projects/paper/versions/{$version}/builds/{$buildNumber}/downloads/{$name}", (string) $version];
        }

        if ($type === 'purpur') {
            if ($version === '') {
                $version = $this->lastOf(Http::timeout(30)->get('https://api.purpurmc.org/v2/purpur')->json('versions', []));
            }
            if ($version === '') {
                throw new \RuntimeException('Could not determine a Purpur version.');
            }

            return ["https://api.purpurmc.org/v2/purpur/{$version}/latest/download", (string) $version];
        }

        if ($type === 'vanilla') {
            $manifest = Http::timeout(30)->get('https://launchermeta.mojang.com/mc/game/version_manifest_v2.json')->json();
            if ($version === '') {
                $version = (string) ($manifest['latest']['release'] ?? '');
            }
            if ($version === '') {
                throw new \RuntimeException('Could not determine a Minecraft release version.');
            }

            $entry = null;
            foreach (($manifest['versions'] ?? []) as $candidate) {
                if (($candidate['id'] ?? null) === $version) {
                    $entry = $candidate;
                    break;
                }
            }
            if (!$entry || empty($entry['url'])) {
                throw new \RuntimeException("Minecraft version {$version} was not found in the Mojang manifest.");
            }

            $detail = Http::timeout(30)->get((string) $entry['url'])->json();
            $url = $detail['downloads']['server']['url'] ?? null;
            if (!$url) {
                throw new \RuntimeException("No server download for Minecraft {$version} (too old?).");
            }

            return [(string) $url, (string) $version];
        }

        throw new \RuntimeException('Unsupported server type: ' . $type);
    }

    private function lastOf($value): string
    {
        if (!is_array($value) || $value === []) {
            return '';
        }

        return (string) end($value);
    }

    // =====================================================================
    // playit plugin (local)
    // =====================================================================

    private function installPlayitPlugin(LocalServer $server): void
    {
        $id = (int) $server->id;
        $dir = $this->serverDir($id) . '/' . self::PLAYIT_PLUGIN_DIR;
        @mkdir($dir, 0775, true);

        $jar = $dir . '/' . self::PLAYIT_PLUGIN_JAR;
        $tmp = $jar . '.new';

        $this->logLine($id, 'Downloading the playit Minecraft plugin.');
        $response = Http::timeout(300)->sink($tmp)->get(self::PLAYIT_PLUGIN_URL);
        if (!$response->successful() || !is_file($tmp) || @filesize($tmp) < 1024) {
            @unlink($tmp);
            throw new \RuntimeException('Could not download the playit plugin.');
        }
        @rename($tmp, $jar);

        $secret = $this->playit->secretForLocal($id);

        // No key at create time (pool was empty)? Claim one now if we can.
        if ($secret === null || $secret === '') {
            $agent = $this->playit->assignNextToLocal($id);
            if ($agent) {
                $secret = (string) $agent->secret_key;
                $server->forceFill(['playit_agent_id' => $agent->id])->save();
            }
        }

        if ($secret === null || $secret === '') {
            $this->logLine($id, 'Playit: no pool key assigned. Start the server and claim the address from the console.');
            return;
        }

        @file_put_contents(
            $dir . '/config.yml',
            "mc-timeout-sec: 30\nagent-secret: \"{$secret}\"\n"
        );

        $this->logLine($id, 'Playit: wrote plugin config with the assigned agent key.');
    }

    /** Pull the connectable address from playit and store it on the server. */
    public function syncPlayit(int $id): array
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return ['ok' => false, 'address' => null, 'message' => 'Server not found.'];
        }

        $secret = $this->playit->secretForLocal($id);
        if (($secret === null || $secret === '') && is_file($this->serverDir($id) . '/' . self::PLAYIT_PLUGIN_DIR . '/config.yml')) {
            $secret = $this->playit->extractSecretFromYaml(
                (string) @file_get_contents($this->serverDir($id) . '/' . self::PLAYIT_PLUGIN_DIR . '/config.yml')
            );
        }

        if ($secret === null || $secret === '') {
            return ['ok' => false, 'address' => null, 'message' => 'No playit key assigned to this server yet.'];
        }

        $result = $this->playit->resolveAddress($secret);
        if (!$result['ok']) {
            $server->forceFill(['last_error' => 'playit: ' . $result['message']])->save();

            return $result;
        }

        $server->forceFill([
            'playit_address' => $result['address'],
            'playit_host' => $result['host'],
            'playit_port' => $result['port'],
            'playit_synced_at' => now(),
        ])->save();

        return $result;
    }

    // =====================================================================
    // Lifecycle
    // =====================================================================

    /** @return array{ok:bool,message:string} */
    public function start(int $id): array
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return ['ok' => false, 'message' => 'Server not found.'];
        }

        if ($this->refresh($server)->status === self::STATUS_RUNNING) {
            return ['ok' => true, 'message' => 'Server is already running.'];
        }

        if (!is_file($this->serverDir($id) . '/' . $server->jar)) {
            return ['ok' => false, 'message' => 'The server jar is missing. Run the install again.'];
        }

        $java = $this->resolveJavaBinary($server->mc_version);
        if ($java === null) {
            return ['ok' => false, 'message' => 'No Java runtime found on this host.'];
        }

        $dir = $this->serverDir($id);
        @mkdir($dir, 0775, true);
        $fifo = $dir . '/stdin.fifo';

        // Recreate the console pipe if it is missing/stale.
        if (file_exists($fifo) && !$this->isFifo($fifo)) {
            @unlink($fifo);
        }

        $flags = $this->javaFlags($server);
        $inner = 'echo $$ > ' . escapeshellarg($this->pidPath($id))
            . '; [ -p ' . escapeshellarg($fifo) . ' ] || mkfifo ' . escapeshellarg($fifo)
            . '; exec 3<>' . escapeshellarg($fifo)
            . '; exec ' . escapeshellarg($java) . ' ' . $flags . ' -jar ' . escapeshellarg($server->jar)
            . ' nogui <&3 >> ' . escapeshellarg($this->consoleLogPath($id)) . ' 2>&1';

        $this->logLine($id, 'Starting: ' . $java . ' ' . $flags . ' -jar ' . $server->jar . ' nogui');
        $cmd = 'cd ' . escapeshellarg($dir) . ' && setsid bash -c ' . escapeshellarg($inner) . ' >/dev/null 2>&1 &';
        $this->run($cmd);

        // Give the process a moment to appear, then confirm it is really alive.
        usleep(900000);
        clearstatcache();

        $pid = $this->readPid($id);
        if ($pid <= 0 || !$this->isAlive($pid)) {
            $tail = mb_substr($this->tail($this->consoleLogPath($id), 20), 0, 400);
            $server->forceFill([
                'status' => self::STATUS_ERROR,
                'last_error' => 'The process exited immediately. Last console output: ' . $tail,
            ])->save();

            return ['ok' => false, 'message' => 'The server did not stay up. Check the console output.'];
        }

        $server->forceFill([
            'status' => self::STATUS_RUNNING,
            'pid' => $pid,
            'started_at' => now(),
            'desired_running' => true,
            'last_error' => null,
        ])->save();

        return ['ok' => true, 'message' => 'Server started (pid ' . $pid . ').'];
    }

    private function isFifo(string $path): bool
    {
        $out = $this->run('[ -p ' . escapeshellarg($path) . ' ] && echo yes || echo no');

        return trim($out) === 'yes';
    }

    private function javaFlags(LocalServer $server): string
    {
        $max = max(512, (int) $server->memory_mb);
        $min = max(256, (int) min(512, intdiv($max, 2)));

        return '-Xms' . $min . 'M -Xmx' . $max . 'M'
            . ' -XX:+UseG1GC -XX:+ParallelRefProcEnabled -XX:MaxGCPauseMillis=200'
            . ' -XX:+DisableExplicitGC -XX:+AlwaysPreTouch';
    }

    /** @return array{ok:bool,message:string} */
    public function stop(int $id, bool $graceful = true): array
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return ['ok' => false, 'message' => 'Server not found.'];
        }

        $pid = $this->readPid($id);
        if ($pid <= 0 || !$this->isAlive($pid)) {
            $this->markStopped($server);

            return ['ok' => true, 'message' => 'Server was not running.'];
        }

        $server->forceFill(['status' => self::STATUS_STOPPING])->save();

        if ($graceful && $this->isFifo($this->fifoPath($id))) {
            $this->sendCommand($id, 'stop');
        }

        // Wait for a clean exit, then escalate.
        for ($i = 0; $i < 12; $i++) {
            usleep(1000000);
            if (!$this->isAlive($pid)) {
                $this->markStopped($server);

                return ['ok' => true, 'message' => 'Server stopped.'];
            }
        }

        $this->run('kill -TERM ' . (int) $pid . ' >/dev/null 2>&1');
        for ($i = 0; $i < 4; $i++) {
            usleep(1000000);
            if (!$this->isAlive($pid)) {
                $this->markStopped($server);

                return ['ok' => true, 'message' => 'Server stopped (terminated).'];
            }
        }

        $this->run('kill -KILL ' . (int) $pid . ' >/dev/null 2>&1');
        $this->markStopped($server);

        return ['ok' => true, 'message' => 'Server killed.'];
    }

    /** @return array{ok:bool,message:string} */
    public function kill(int $id): array
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return ['ok' => false, 'message' => 'Server not found.'];
        }

        $pid = $this->readPid($id);
        if ($pid > 0) {
            $this->run('kill -KILL ' . (int) $pid . ' >/dev/null 2>&1');
        }
        $this->markStopped($server);

        return ['ok' => true, 'message' => 'Server killed.'];
    }

    private function markStopped(LocalServer $server): void
    {
        @unlink($this->pidPath((int) $server->id));
        $server->forceFill(['status' => self::STATUS_STOPPED, 'pid' => null, 'desired_running' => false])->save();
    }

    /** @return array{ok:bool,message:string} */
    public function restart(int $id): array
    {
        $this->stop($id, true);
        sleep(1);

        return $this->start($id);
    }

    /** @return array{ok:bool,message:string} */
    public function sendCommand(int $id, string $command): array
    {
        $command = trim($command);
        if ($command === '') {
            return ['ok' => false, 'message' => 'Empty command.'];
        }

        $pid = $this->readPid($id);
        if ($pid <= 0 || !$this->isAlive($pid)) {
            return ['ok' => false, 'message' => 'Server is not running.'];
        }

        $fifo = $this->fifoPath($id);
        if (!$this->isFifo($fifo)) {
            return ['ok' => false, 'message' => 'Console pipe is not available.'];
        }

        // `timeout` guarantees the request cannot hang if the reader is gone.
        $timeout = $this->commandExists('timeout') ? 'timeout 3 ' : '';
        $inner = 'printf ' . escapeshellarg("%s\n") . ' ' . escapeshellarg($command) . ' > ' . escapeshellarg($fifo);
        $this->run($timeout . 'sh -c ' . escapeshellarg($inner));

        return ['ok' => true, 'message' => 'Sent.'];
    }

    /** @return array{ok:bool,message:string} */
    public function delete(int $id, bool $purgeFiles = true): array
    {
        $server = LocalServer::query()->find($id);
        if (!$server) {
            return ['ok' => false, 'message' => 'Server not found.'];
        }

        $this->kill($id);

        if ($server->playit_agent_id) {
            $this->playit->clearLocal($id);
        }

        if ($purgeFiles) {
            $dir = $this->serverDir($id);
            $this->run('rm -rf ' . escapeshellarg($dir));
        }

        $server->delete();

        return ['ok' => true, 'message' => 'Server deleted.'];
    }

    // =====================================================================
    // Status / stats
    // =====================================================================

    /** Reconcile the stored status with reality and return the fresh model. */
    public function refresh(LocalServer $server): LocalServer
    {
        $pid = $this->readPid((int) $server->id);
        $alive = $pid > 0 && $this->isAlive($pid);

        if ($alive) {
            if ($server->status !== self::STATUS_RUNNING || $server->pid !== $pid) {
                $server->forceFill(['status' => self::STATUS_RUNNING, 'pid' => $pid])->save();
            }

            return $server;
        }

        if (in_array($server->status, [self::STATUS_RUNNING, self::STATUS_STARTING, self::STATUS_STOPPING], true)) {
            @unlink($this->pidPath((int) $server->id));
            $server->forceFill(['status' => self::STATUS_STOPPED, 'pid' => null])->save();
        }

        return $server;
    }

    /** @return array{running:bool,pid:int,memory_mb:float,uptime_seconds:int} */
    public function stats(LocalServer $server): array
    {
        $pid = $this->readPid((int) $server->id);
        $running = $pid > 0 && $this->isAlive($pid);

        $memory = 0.0;
        $uptime = 0;

        if ($running) {
            $rss = $this->run('ps -o rss= -p ' . (int) $pid);
            if (is_numeric(trim($rss))) {
                $memory = round(((float) trim($rss)) / 1024, 1);
            }

            $etimes = $this->run('ps -o etimes= -p ' . (int) $pid);
            if (is_numeric(trim($etimes))) {
                $uptime = (int) trim($etimes);
            }
        }

        return ['running' => $running, 'pid' => $pid, 'memory_mb' => $memory, 'uptime_seconds' => $uptime];
    }

    public function tailConsole(int $id, int $lines = 200): string
    {
        return $this->tail($this->consoleLogPath($id), $lines);
    }

    public function tailInstall(int $id, int $lines = 200): string
    {
        return $this->tail($this->installLogPath($id), $lines);
    }

    /**
     * Start every server that was running before the host rebooted. Called from
     * the boot unit installed by turtle/install.sh.
     */
    public function restoreAll(): void
    {
        foreach (LocalServer::query()->orderBy('id')->get() as $server) {
            $this->refresh($server);

            if ($server->status === self::STATUS_RUNNING) {
                $this->logLine((int) $server->id, 'Boot: already running.');
                continue;
            }

            // Only bring back servers the operator had left running.
            if ($server->desired_running && in_array($server->status, [self::STATUS_INSTALLED, self::STATUS_STOPPED], true)) {
                $this->logLine((int) $server->id, 'Boot: restarting.');
                $this->start((int) $server->id);
            }
        }
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function uuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
