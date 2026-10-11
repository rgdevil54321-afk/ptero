<?php

namespace Turtle\Playit\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Turtle\Playit\PlayitService;

/**
 * Token-protected endpoints used by node bridges and egg install scripts.
 *
 * They are deliberately thin: every decision lives in PlayitService.
 */
class PlayitPublicController extends Controller
{
    public function __construct(private PlayitService $playit)
    {
    }

    /**
     * GET /api/public/playit/claim?uuid=<server uuid>
     *
     * Hands a pool key to a server during installation. Returns {secret:null}
     * (HTTP 200) when the pool is empty, so the install script can fall back to
     * the plugin's interactive claim flow instead of failing the install.
     */
    public function claim(Request $request): JsonResponse
    {
        if (!$this->authorized($request)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $uuid = (string) $request->query('uuid', '');
        $server = $this->playit->serverByUuid($uuid);
        if (!$server) {
            return response()->json(['error' => 'unknown server'], 404);
        }

        if (!$this->eggUsesPlayit((int) $server->egg_id)) {
            return response()->json(['error' => 'server does not support playit'], 422);
        }

        $agent = $this->playit->assignNext($uuid, (int) $server->id);
        if (!$agent) {
            return response()->json(['secret' => null]);
        }

        return response()->json(['secret' => $agent->secret_key]);
    }

    /**
     * POST /api/public/playit/report
     * Body: {uuid, address?, host?, port?, tunnel_id?, agent_id?}
     */
    public function report(Request $request): JsonResponse
    {
        if (!$this->authorized($request)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $uuid = (string) $request->input('uuid', '');
        $address = $request->input('address');
        $host = $request->input('host');
        $port = $request->input('port');

        if ($host === null && is_string($address) && $address !== '') {
            [$host, $resolvedPort] = $this->playit->splitAddress($address);
            $port = $port ?? $resolvedPort;
        }

        if ($uuid === '' || !is_string($host) || $host === '') {
            return response()->json(['error' => 'uuid and address are required'], 422);
        }

        $ok = $this->playit->report(
            $uuid,
            $host,
            is_string($address) && $address !== '' ? $address : null,
            is_numeric($port) ? (int) $port : null,
            is_string($request->input('tunnel_id')) ? $request->input('tunnel_id') : null,
            is_string($request->input('agent_id')) ? $request->input('agent_id') : null
        );

        return response()->json(['ok' => $ok], $ok ? 200 : 404);
    }

    /**
     * POST /api/public/playit/sync - ask the panel to refresh pool-backed agents.
     */
    public function sync(Request $request): JsonResponse
    {
        if (!$this->authorized($request)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        return response()->json(['results' => $this->playit->syncAll()]);
    }

    private function authorized(Request $request): bool
    {
        return $this->playit->tokenMatches($request->header('X-Playit-Token'));
    }

    private function eggUsesPlayit(int $eggId): bool
    {
        if ($eggId <= 0) {
            return false;
        }

        return DB::table('egg_variables')
            ->where('egg_id', $eggId)
            ->where('env_variable', PlayitService::VAR_SECRET)
            ->exists();
    }
}
