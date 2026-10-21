<?php

namespace Turtle\Local\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A game server run natively on the panel host ("pure local node").
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $name
 * @property string      $type
 * @property string|null $mc_version
 * @property int         $memory_mb
 * @property int         $port
 * @property string      $jar
 * @property string|null $java_bin
 * @property string|null $motd
 * @property string      $status
 * @property string|null $last_error
 * @property int|null    $pid
 * @property bool        $playit
 * @property string|null $playit_address
 * @property string|null $playit_host
 * @property int|null    $playit_port
 */
class LocalServer extends Model
{
    protected $table = 'local_servers';

    protected $guarded = [];

    protected $casts = [
        'memory_mb' => 'integer',
        'port' => 'integer',
        'pid' => 'integer',
        'desired_running' => 'boolean',
        'playit' => 'boolean',
        'playit_agent_id' => 'integer',
        'playit_port' => 'integer',
        'started_at' => 'datetime',
        'installed_at' => 'datetime',
        'playit_synced_at' => 'datetime',
        'created_by' => 'integer',
    ];

    /**
     * Game types that support Bukkit/Paper plugins (and therefore the playit
     * Minecraft plugin).
     */
    public static function pluginTypes(): array
    {
        return ['paper', 'purpur', 'spigot', 'bukkit'];
    }

    public function supportsPlugins(): bool
    {
        return in_array(strtolower((string) $this->type), self::pluginTypes(), true);
    }

    public function isInstalled(): bool
    {
        return in_array($this->status, ['installed', 'running', 'stopped', 'starting'], true);
    }

    public function connectAddress(): string
    {
        if ($this->playit_address) {
            return (string) $this->playit_address;
        }

        return (string) $this->port;
    }
}
