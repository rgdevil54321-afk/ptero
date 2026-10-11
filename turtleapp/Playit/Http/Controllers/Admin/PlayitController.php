<?php

namespace Turtle\Playit\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Turtle\Playit\PlayitService;

class PlayitController extends Controller
{
    public function __construct(private PlayitService $playit)
    {
    }

    public function index(Request $request): View
    {
        $token = $this->playit->ensureReportToken();

        $servers = collect();
        try {
            $servers = \Illuminate\Support\Facades\DB::table('servers')
                ->leftJoin('eggs', 'eggs.id', '=', 'servers.egg_id')
                ->orderBy('servers.id')
                ->get([
                    'servers.id',
                    'servers.uuid',
                    'servers.name',
                    'eggs.name as egg_name',
                ]);
        } catch (\Throwable $e) {
            $servers = collect();
        }

        return view('admin.playit.index', [
            'title' => 'Playit',
            'agents' => $this->playit->agents(),
            'links' => $this->playit->links(),
            'servers' => $servers,
            'token' => $token,
            'poolCount' => $this->playit->poolCount(),
            'panelUrl' => rtrim($request->root(), '/'),
        ]);
    }

    public function addKeys(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'secrets' => 'required|string',
            'label' => 'nullable|string|max:191',
        ]);

        $result = $this->playit->addSecrets($data['secrets'], $data['label'] ?? null);

        return redirect()->route('admin.playit')->with(
            'success',
            sprintf('Added %d playit key(s); skipped %d.', $result['added'], $result['skipped'])
        );
    }

    public function assign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'agent_id' => 'required|integer',
            'server_uuid' => 'required|string|size:36',
        ]);

        if (!$this->playit->assignToServer((int) $data['agent_id'], (string) $data['server_uuid'])) {
            return redirect()->route('admin.playit')->with('error', 'Could not assign that key to that server.');
        }

        return redirect()->route('admin.playit')->with('success', 'Playit key assigned.');
    }

    public function release(Request $request): RedirectResponse
    {
        $data = $request->validate(['agent_id' => 'required|integer']);
        $this->playit->release((int) $data['agent_id']);

        return redirect()->route('admin.playit')->with('success', 'Playit key released back to the pool.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate(['agent_id' => 'required|integer']);
        $this->playit->delete((int) $data['agent_id']);

        return redirect()->route('admin.playit')->with('success', 'Playit key deleted.');
    }

    public function sync(): RedirectResponse
    {
        $results = $this->playit->syncAll();

        $ok = count(array_filter($results, fn (array $r): bool => $r['ok']));
        $failed = count($results) - $ok;

        return redirect()->route('admin.playit')->with(
            $ok > 0 ? 'success' : 'error',
            sprintf('Playit sync finished: %d online, %d without an address yet.', $ok, $failed)
        );
    }

    public function rotateToken(): RedirectResponse
    {
        $this->playit->rotateReportToken();

        return redirect()->route('admin.playit')->with('success', 'Playit report token rotated. Update your node bridge configuration.');
    }
}
