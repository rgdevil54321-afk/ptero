<?php

namespace Turtle\Local\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Turtle\Local\LocalService;
use Turtle\Local\Models\LocalServer;

/**
 * Admin UI for native "local node" servers (no Wings, no Docker).
 */
class LocalController extends Controller
{
    public function __construct(private LocalService $local)
    {
    }

    public function index(Request $request): View
    {
        $servers = LocalServer::query()->orderByDesc('id')->get();

        // Reconcile statuses so the list is not stale after a reboot.
        foreach ($servers as $server) {
            $this->local->refresh($server);
        }

        $highest = (int) (LocalServer::query()->max('port') ?: 25564);

        return view('admin.local.index', [
            'title' => 'Local Servers',
            'servers' => $servers,
            'types' => LocalService::TYPES,
            'preflight' => $this->local->preflight(),
            'baseDir' => $this->local->baseDir(),
            'suggestedPort' => min(65535, max(25565, $highest + 1)),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|string|in:' . implode(',', array_keys(LocalService::TYPES)),
            'mc_version' => 'nullable|string|max:32',
            'memory_mb' => 'required|integer|min:512|max:65536',
            'port' => 'required|integer|min:1|max:65535',
            'motd' => 'nullable|string|max:191',
            'playit' => 'nullable|boolean',
        ]);

        $result = $this->local->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'mc_version' => $data['mc_version'] ?? null,
            'memory_mb' => (int) $data['memory_mb'],
            'port' => (int) $data['port'],
            'motd' => $data['motd'] ?? null,
            'playit' => (bool) ($data['playit'] ?? false),
        ], $request->user()?->id);

        if (!$result['ok'] || !$result['server']) {
            return redirect()->route('admin.local')->with('error', $result['message']);
        }

        return redirect()->route('admin.local.show', $result['server']->id)->with('success', $result['message']);
    }

    public function show(Request $request, LocalServer $server): View
    {
        $this->local->refresh($server);

        return view('admin.local.show', [
            'title' => $server->name,
            'server' => $server,
            'stats' => $this->local->stats($server),
            'console' => $this->local->tailConsole((int) $server->id, 250),
            'installLog' => $this->local->tailInstall((int) $server->id, 250),
            'dir' => $this->local->serverDir((int) $server->id),
        ]);
    }

    /** Polled by the console page. */
    public function console(Request $request, LocalServer $server): JsonResponse
    {
        $this->local->refresh($server);
        $server->refresh();

        return response()->json([
            'status' => $server->status,
            'running' => $server->status === LocalService::STATUS_RUNNING,
            'console' => $this->local->tailConsole((int) $server->id, 250),
            'stats' => $this->local->stats($server),
            'playit' => $server->playit_address,
        ]);
    }

    public function start(LocalServer $server): RedirectResponse
    {
        $result = $this->local->start((int) $server->id);

        return redirect()->route('admin.local.show', $server->id)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function stop(LocalServer $server): RedirectResponse
    {
        $result = $this->local->stop((int) $server->id);

        return redirect()->route('admin.local.show', $server->id)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function restart(LocalServer $server): RedirectResponse
    {
        $result = $this->local->restart((int) $server->id);

        return redirect()->route('admin.local.show', $server->id)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function kill(LocalServer $server): RedirectResponse
    {
        $result = $this->local->kill((int) $server->id);

        return redirect()->route('admin.local.show', $server->id)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function command(Request $request, LocalServer $server): RedirectResponse
    {
        $data = $request->validate(['command' => 'required|string|max:1000']);
        $result = $this->local->sendCommand((int) $server->id, (string) $data['command']);

        return redirect()->route('admin.local.show', $server->id)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reinstall(LocalServer $server): RedirectResponse
    {
        // Downloads run in a detached CLI process so the web request never times out.
        $this->local->queueInstall((int) $server->id);

        return redirect()->route('admin.local.show', $server->id)
            ->with('success', 'Re-download started in the background. The install log will update shortly.');
    }

    public function syncPlayit(LocalServer $server): RedirectResponse
    {
        $result = $this->local->syncPlayit((int) $server->id);

        return redirect()->route('admin.local.show', $server->id)
            ->with($result['ok'] ? 'success' : 'error', 'playit: ' . $result['message'] . ($result['address'] ? ' (' . $result['address'] . ')' : ''));
    }

    public function destroy(Request $request, LocalServer $server): RedirectResponse
    {
        $purge = $request->boolean('purge_files', true);
        $result = $this->local->delete((int) $server->id, $purge);

        return redirect()->route('admin.local')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
