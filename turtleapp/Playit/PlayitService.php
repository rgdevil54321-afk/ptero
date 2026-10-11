<?php

namespace Turtle\Playit;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Turtle\Playit\Models\PlayitAgent;
use Turtle\Playit\Models\PlayitLink;
use Turtle\Playit\Models\PlayitSetting;

/**
 * Playit.gg integration for the Turtle Game Panel.
 *
 * Two independent mechanisms produce a public playit address for a server:
 *
 *  1. "Pool" mode - an administrator pastes playit agent secret keys into the
 *     panel. The panel hands one key to each server (via the egg install script
 *     or the admin UI) and then talks to the playit API directly to discover the
 *     connectable address.
 *
 *  2. "Agent" mode - the playit plugin running on the game server claims its own
 *     key and writes it back into its config.yml. A small node-side bridge reads
 *     that key and reports the address back to this panel over HTTP.
 *
 * Both paths converge on applyToServer(), which is the only thing that ever
 * writes to the panel: it sets allocations.ip_alias (the field the client SPA
 * renders as the connection address) and the PLAYIT_ADDRESS egg variable.
 *
 * This service never throws for network/API problems; callers get a status
 * array instead so a broken playit account can never take the panel down.
 */
class PlayitService
{
    public const SETTING_REPORT_TOKEN = 'report_token';

    public const API_BASE = 'https://api.playit.gg';

    public const VAR_SECRET = 'PLAYIT_AGENT_SECRET';

    public const VAR_ADDRESS = 'PLAYIT_ADDRESS';

    /** Minimum plausible length of a playit agent secret key. */
    private const MIN_SECRET_LENGTH = 16;

    // ---------------------------------------------------------------------
    // Shared secret used by node bridges + egg install scripts.
    // ---------------------------------------------------------------------

    public function reportToken(): ?string
    {
        $token = PlayitSetting::query()->where('key', self::SETTING_REPORT_TOKEN)->value('value');

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function ensureReportToken(): string
    {
        $token = $this->reportToken();
        if ($token !== null) {
            return $token;
        }

        return $this->rotateReportToken();
    }

    public function rotateReportToken(): string
    {
        $token = bin2hex(random_bytes(24));

        PlayitSetting::query()->updateOrCreate(
            ['key' => self::SETTING_REPORT_TOKEN],
            ['value' => $token]
        );

        return $token;
    }

    public function tokenMatches(?string $provided): bool
    {
        $token = $this->reportToken();

        return is_string($token) && $token !== ''
            && is_string($provided) && $provided !== ''
            && hash_equals($token, $provided);
    }

    // ---------------------------------------------------------------------
    // The pool.
    // ---------------------------------------------------------------------

    /** @return Collection<int, PlayitAgent> */
    public function agents(): Collection
    {
        return PlayitAgent::query()->orderBy('id')->get();
    }

    /** @return Collection<int, PlayitLink> */
    public function links(): Collection
    {
        return PlayitLink::query()->orderByDesc('updated_at')->get();
    }

    public function poolCount(): int
    {
        return PlayitAgent::query()->whereNull('server_uuid')->count();
    }

    /**
     * Add one or more secret keys (newline / comma / semicolon separated).
     *
     * @return array{added:int,skipped:int}
     */
    public function addSecrets(string $raw, ?string $label = null): array
    {
        $candidates = preg_split('/[\r\n,;]+/', $raw) ?: [];
        $added = 0;
        $skipped = 0;

        foreach ($candidates as $candidate) {
            $key = trim($candidate);
            if ($key === '') {
                continue;
            }
            if (strlen($key) < self::MIN_SECRET_LENGTH) {
                $skipped++;
                continue;
            }

            $agent = PlayitAgent::query()->firstOrCreate(
                ['secret_key' => $key],
                ['label' => $label !== '' ? $label : null, 'status' => 'available']
            );

            $agent->wasRecentlyCreated ? $added++ : $skipped++;
        }

        return ['added' => $added, 'skipped' => $skipped];
    }

    public function agent(int $id): ?PlayitAgent
    {
        return PlayitAgent::query()->find($id);
    }

    /**
     * Claim the next free key for a server. Idempotent: an already assigned
     * server always gets the same key back.
     */
    public function assignNext(string $serverUuid, ?int $serverId = null): ?PlayitAgent
    {
        $existing = PlayitAgent::query()->where('server_uuid', $serverUuid)->first();
        if ($existing) {
            return $existing;
        }

        $agent = PlayitAgent::query()->whereNull('server_uuid')->orderBy('id')->first();
        if (!$agent) {
            return null;
        }

        $agent->forceFill([
            'server_uuid' => $serverUuid,
            'server_id' => $serverId,
            'status' => 'assigned',
        ])->save();

        return $agent;
    }

    public function assignToServer(int $agentId, string $serverUuid): bool
    {
        $agent = $this->agent($agentId);
        $server = $this->serverByUuid($serverUuid);
        if (!$agent || !$server) {
            return false;
        }

        // Only one key per server: detach any previous assignment.
        PlayitAgent::query()
            ->where('server_uuid', $serverUuid)
            ->where('id', '!=', $agent->id)
            ->update([
                'server_uuid' => null,
                'server_id' => null,
                'status' => 'available',
                'address' => null,
                'host' => null,
                'port' => null,
            ]);

        $agent->forceFill([
            'server_uuid' => $serverUuid,
            'server_id' => (int) $server->id,
            'status' => 'assigned',
        ])->save();

        return true;
    }

    public function release(int $agentId): void
    {
        $agent = $this->agent($agentId);
        if (!$agent) {
            return;
        }

        if ($agent->server_uuid) {
            $this->clearServer($agent->server_uuid);
        }

        $agent->forceFill([
            'server_uuid' => null,
            'server_id' => null,
            'status' => 'available',
            'agent_id' => null,
            'tunnel_id' => null,
            'address' => null,
            'host' => null,
            'port' => null,
            'last_error' => null,
        ])->save();
    }

    public function delete(int $agentId): void
    {
        $agent = $this->agent($agentId);
        if (!$agent) {
            return;
        }

        if ($agent->server_uuid) {
            $this->clearServer($agent->server_uuid);
        }

        $agent->delete();
    }

    public function secretForServer(string $serverUuid): ?string
    {
        return PlayitAgent::query()->where('server_uuid', $serverUuid)->value('secret_key');
    }

    // ---------------------------------------------------------------------
    // Talking to playit.
    // ---------------------------------------------------------------------

    /**
     * Sync every assigned agent.
     *
     * @return array<int, array{server_uuid:?string,ok:bool,address:?string,message:string}>
     */
    public function syncAll(): array
    {
        $results = [];

        foreach (PlayitAgent::query()->whereNotNull('server_uuid')->orderBy('id')->get() as $agent) {
            $result = $this->syncAgent($agent);
            $result['server_uuid'] = $agent->server_uuid;
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Ask playit for the connectable address of a single agent.
     *
     * @return array{ok:bool,address:?string,message:string}
     */
    public function syncAgent(PlayitAgent $agent): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'agent-key ' . $agent->secret_key,
                'Content-Type' => 'application/json',
            ])->timeout(20)->post(self::API_BASE . '/v1/agents/rundata', (object) []);
        } catch (\Throwable $e) {
            $agent->forceFill(['status' => 'error', 'last_error' => 'request failed'])->save();

            return ['ok' => false, 'address' => null, 'message' => 'request failed'];
        }

        if (!$response->successful()) {
            $agent->forceFill(['status' => 'error', 'last_error' => 'HTTP ' . $response->status()])->save();

            return ['ok' => false, 'address' => null, 'message' => 'HTTP ' . $response->status()];
        }

        $data = $response->json();
        $tunnels = is_array($data['tunnels'] ?? null) ? $data['tunnels'] : [];

        if ($tunnels === []) {
            $agent->forceFill([
                'agent_id' => $data['agent_id'] ?? $agent->agent_id,
                'status' => 'assigned',
                'last_error' => 'agent has no tunnel yet',
                'last_synced_at' => now(),
            ])->save();

            return ['ok' => false, 'address' => null, 'message' => 'no tunnel yet'];
        }

        $tunnel = $this->pickTunnel($tunnels, $agent->server_uuid);
        $display = is_string($tunnel['display_address'] ?? null) ? trim($tunnel['display_address']) : '';
        [$host, $port] = $this->splitAddress($display);

        $agent->forceFill([
            'agent_id' => $data['agent_id'] ?? $agent->agent_id,
            'tunnel_id' => $tunnel['id'] ?? null,
            'address' => $display !== '' ? $display : null,
            'host' => $host,
            'port' => $port,
            'status' => $host ? 'online' : 'assigned',
            'last_error' => $host ? null : 'tunnel has no address yet',
            'last_synced_at' => now(),
        ])->save();

        if ($host && $agent->server_uuid) {
            $this->applyToServer($agent->server_uuid, $host, $display, 'sync');
        }

        return ['ok' => (bool) $host, 'address' => $display ?: null, 'message' => $host ? 'ok' : 'no address'];
    }

    /**
     * Choose the tunnel that belongs to this server: the one bound to the
     * allocation port if possible, otherwise the first Minecraft tunnel.
     */
    private function pickTunnel(array $tunnels, ?string $serverUuid): array
    {
        $port = $this->allocationPort($serverUuid);

        if ($port !== null) {
            foreach ($tunnels as $tunnel) {
                if ($this->tunnelLocalPort($tunnel) === $port) {
                    return $tunnel;
                }
            }
        }

        foreach ($tunnels as $tunnel) {
            $type = strtolower((string) ($tunnel['tunnel_type'] ?? ''));
            if (str_contains($type, 'minecraft')) {
                return $tunnel;
            }
        }

        return $tunnels[0];
    }

    private function tunnelLocalPort(array $tunnel): ?int
    {
        $fields = $tunnel['agent_config']['fields'] ?? null;
        if (!is_array($fields)) {
            return null;
        }

        foreach ($fields as $field) {
            if (is_array($field) && ($field['name'] ?? null) === 'local_port') {
                return (int) $field['value'];
            }
        }

        return null;
    }

    /**
     * @return array{0:?string,1:?int}
     */
    public function splitAddress(?string $address): array
    {
        if (!is_string($address) || trim($address) === '') {
            return [null, null];
        }

        $address = trim($address);
        if (preg_match('/^(.*):(\d+)$/', $address, $m)) {
            return [$m[1], (int) $m[2]];
        }

        return [$address, null];
    }

    // ---------------------------------------------------------------------
    // Writing to the panel (the only mutating surface).
    // ---------------------------------------------------------------------

    /**
     * Called by a node bridge and by the panel's own sync.
     */
    public function report(
        string $serverUuid,
        string $host,
        ?string $address = null,
        ?int $port = null,
        ?string $tunnelId = null,
        ?string $agentId = null
    ): bool {
        if (!$this->serverByUuid($serverUuid)) {
            return false;
        }

        $this->applyToServer($serverUuid, $host, $address ?? $host, 'agent', $port, $tunnelId, $agentId);

        return true;
    }

    public function applyToServer(
        string $serverUuid,
        string $host,
        ?string $address,
        string $source = 'sync',
        ?int $port = null,
        ?string $tunnelId = null,
        ?string $agentId = null
    ): void {
        $server = $this->serverByUuid($serverUuid);
        if (!$server) {
            return;
        }

        $this->setAlias((int) $server->allocation_id, $host);
        $this->setVariable((int) $server->id, self::VAR_ADDRESS, (string) ($address ?? $host));

        PlayitLink::query()->updateOrCreate(
            ['server_uuid' => $serverUuid],
            [
                'server_id' => (int) $server->id,
                'address' => (string) ($address ?? $host),
                'host' => $host,
                'port' => $port,
                'tunnel_id' => $tunnelId,
                'agent_id' => $agentId,
                'source' => $source,
                'last_synced_at' => now(),
            ]
        );
    }

    public function clearServer(string $serverUuid): void
    {
        $server = $this->serverByUuid($serverUuid);
        if ($server) {
            $this->setAlias((int) $server->allocation_id, null);
            $this->setVariable((int) $server->id, self::VAR_ADDRESS, '');
        }

        PlayitLink::query()->where('server_uuid', $serverUuid)->delete();
    }

    private function setAlias(int $allocationId, ?string $alias): void
    {
        if ($allocationId <= 0) {
            return;
        }

        DB::table('allocations')
            ->where('id', $allocationId)
            ->update(['ip_alias' => $alias, 'updated_at' => now()]);
    }

    private function setVariable(int $serverId, string $envVariable, string $value): void
    {
        $variableId = DB::table('egg_variables')->where('env_variable', $envVariable)->value('id');
        if (!$variableId) {
            return;
        }

        $now = now();
        DB::table('server_variables')->updateOrInsert(
            ['server_id' => $serverId, 'variable_id' => $variableId],
            ['variable_value' => $value, 'updated_at' => $now, 'created_at' => $now]
        );
    }

    // ---------------------------------------------------------------------
    // Read-only lookups.
    // ---------------------------------------------------------------------

    public function serverByUuid(string $uuid): ?object
    {
        if ($uuid === '') {
            return null;
        }

        return DB::table('servers')->where('uuid', $uuid)->first();
    }

    private function allocationPort(?string $serverUuid): ?int
    {
        if (!$serverUuid) {
            return null;
        }

        $port = DB::table('servers')
            ->join('allocations', 'allocations.id', '=', 'servers.allocation_id')
            ->where('servers.uuid', $serverUuid)
            ->value('allocations.port');

        return $port ? (int) $port : null;
    }
}
