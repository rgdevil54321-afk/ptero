@extends('layouts.admin')

@section('title')
    {{ $server->name }}
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>{{ $server->type }} @if($server->mc_version){{ $server->mc_version }}@endif &middot; local runtime</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.local') }}">Local Servers</a></li>
        <li class="active">{{ $server->name }}</li>
    </ol>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Console
                        <span id="status-label" class="label label-default" style="margin-left:8px;">{{ $server->status }}</span>
                    </h3>
                    <div class="box-tools">
                        <span class="text-muted small" id="stat-line">
                            @if($stats['running'])
                                pid {{ $stats['pid'] }} &middot; {{ $stats['memory_mb'] }} MB
                            @else
                                stopped
                            @endif
                        </span>
                    </div>
                </div>
                <div class="box-body" style="padding:0;">
                    <pre id="console" style="margin:0;max-height:460px;overflow:auto;background:#111;color:#ddd;padding:12px;font-size:12px;">{{ $console }}</pre>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.local.command', $server->id) }}" method="POST" class="form-inline">
                        @csrf
                        <div class="input-group" style="width:100%;">
                            <input type="text" name="command" class="form-control" placeholder="Type a server command and press Enter (e.g. say hello)" autocomplete="off">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="box box-default collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title">Install log</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <pre style="margin:0;max-height:260px;overflow:auto;font-size:12px;">{{ $installLog ?: 'No install output yet.' }}</pre>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Power</h3>
                </div>
                <div class="box-body">
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group">
                            <form action="{{ route('admin.local.start', $server->id) }}" method="POST">@csrf
                                <button class="btn btn-success btn-block" @if($stats['running']) disabled @endif>
                                    <i class="fa fa-play"></i> Start
                                </button>
                            </form>
                        </div>
                        <div class="btn-group">
                            <form action="{{ route('admin.local.restart', $server->id) }}" method="POST">@csrf
                                <button class="btn btn-warning btn-block"><i class="fa fa-refresh"></i> Restart</button>
                            </form>
                        </div>
                    </div>
                    <div class="btn-group btn-group-justified" style="margin-top:8px;">
                        <div class="btn-group">
                            <form action="{{ route('admin.local.stop', $server->id) }}" method="POST">@csrf
                                <button class="btn btn-default btn-block" @unless($stats['running']) disabled @endunless>
                                    <i class="fa fa-stop"></i> Stop
                                </button>
                            </form>
                        </div>
                        <div class="btn-group">
                            <form action="{{ route('admin.local.kill', $server->id) }}" method="POST">@csrf
                                <button class="btn btn-danger btn-block" @unless($stats['running']) disabled @endunless>
                                    <i class="fa fa-bolt"></i> Kill
                                </button>
                            </form>
                        </div>
                    </div>
                    <form action="{{ route('admin.local.reinstall', $server->id) }}" method="POST" style="margin-top:8px;">@csrf
                        <button class="btn btn-default btn-block"><i class="fa fa-download"></i> Re-download jar &amp; plugin</button>
                    </form>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Address</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted" style="margin-bottom:6px;">Direct (same network):</p>
                    <p><code>{{ $server->port }}</code> on this host</p>

                    <p class="text-muted" style="margin-bottom:6px;">playit.gg:</p>
                    @if($server->playit_address)
                        <p><code id="playit-address">{{ $server->playit_address }}</code></p>
                        @if($server->playit_synced_at)
                            <p class="text-muted small">synced {{ $server->playit_synced_at->diffForHumans() }}</p>
                        @endif
                    @else
                        <p><code id="playit-address">&ndash;</code></p>
                    @endif
                    <form action="{{ route('admin.local.playit', $server->id) }}" method="POST">@csrf
                        <button class="btn btn-sm btn-primary"><i class="fa fa-magic"></i> Sync playit address</button>
                    </form>
                    @unless($server->supportsPlugins())
                        <p class="text-muted small" style="margin-top:8px;">
                            {{ $server->type }} does not load plugins, so playit needs a different tunnel
                            (an agent on the host) &mdash; the plugin path only works for Paper/Purpur/Spigot.
                        </p>
                    @endunless
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed">
                        <tr><th>Status</th><td>{{ $server->status }}</td></tr>
                        <tr><th>PID</th><td>{{ $stats['pid'] ?: '&ndash;' }}</td></tr>
                        <tr><th>Memory cap</th><td>{{ $server->memory_mb }} MB</td></tr>
                        <tr><th>Port</th><td>{{ $server->port }}</td></tr>
                        <tr><th>Directory</th><td><code style="word-break:break-all;">{{ $dir }}</code></td></tr>
                    </table>
                    @if($server->last_error)
                        <div class="alert alert-danger small" style="margin-bottom:0;">
                            {{ $server->last_error }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Delete</h3>
                </div>
                <form action="{{ route('admin.local.delete', $server->id) }}" method="POST"
                      onsubmit="return confirm('Delete this server? The files under {{ $dir }} will be removed.');">
                    @csrf
                    @method('DELETE')
                    <div class="box-body">
                        <div class="checkbox">
                            <label><input type="checkbox" name="purge_files" value="1" checked> Also delete the files on disk</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button class="btn btn-danger btn-block"><i class="fa fa-trash"></i> Delete server</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var consoleEl = document.getElementById('console');
            var statusEl = document.getElementById('status-label');
            var statEl = document.getElementById('stat-line');
            var playitEl = document.getElementById('playit-address');
            var url = @json(route('admin.local.console', $server->id));

            function label(status) {
                var map = {
                    running: 'label-success',
                    error: 'label-danger',
                    installing: 'label-warning',
                    starting: 'label-warning',
                    stopping: 'label-warning'
                };
                return map[status] || 'label-default';
            }

            function tick() {
                fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (typeof data.console === 'string') {
                            var atBottom = consoleEl.scrollTop + consoleEl.clientHeight >= consoleEl.scrollHeight - 30;
                            consoleEl.textContent = data.console;
                            if (atBottom) { consoleEl.scrollTop = consoleEl.scrollHeight; }
                        }
                        statusEl.className = 'label ' + label(data.status) ;
                        statusEl.textContent = data.status;
                        if (data.stats && data.stats.running) {
                            statEl.textContent = 'pid ' + data.stats.pid + ' \u00b7 ' + data.stats.memory_mb + ' MB';
                        } else {
                            statEl.textContent = 'stopped';
                        }
                        if (data.playit && playitEl) { playitEl.textContent = data.playit; }
                    })
                    .catch(function () {});
            }

            consoleEl.scrollTop = consoleEl.scrollHeight;
            setInterval(tick, 3000);
        })();
    </script>
@endsection
