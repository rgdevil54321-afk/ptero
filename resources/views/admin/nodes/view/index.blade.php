@extends('layouts.admin')

@section('title')
    {{ $node->name }}
@endsection

@section('content-header')
    <h1>{{ $node->name }}<small>A quick overview of your node.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.nodes') }}">Nodes</a></li>
        <li class="active">{{ $node->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li class="active"><a href="{{ route('admin.nodes.view', $node->id) }}">About</a></li>
                <li><a href="{{ route('admin.nodes.view.settings', $node->id) }}">Settings</a></li>
                <li><a href="{{ route('admin.nodes.view.configuration', $node->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.nodes.view.allocation', $node->id) }}">Allocation</a></li>
                <li><a href="{{ route('admin.nodes.view.servers', $node->id) }}">Servers</a></li>
                @if($liveNodeStatsEnabled)
                <li><a href="{{ route('admin.nodes.view.wings-stats', $node->id) }}">Wings Stats</a></li>
                @if($firewallManagementEnabled ?? false)
                    <li><a href="{{ route('admin.nodes.view.firewall', $node->id) }}">Firewall</a></li>
                @endif
                @endif
                @if($radarEnabled ?? false)
                    <li><a href="{{ route('admin.nodes.view.radar', $node->id) }}">Radar</a></li>
                @endif
                <li><a href="{{ route('admin.nodes.view.logs', $node->id) }}">Logs</a></li>
                <li><a href="{{ route('admin.nodes.view.backups', $node->id) }}">Backups</a></li>
</ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Information</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <tr>
                                <td>Daemon Version</td>
                                <td><code data-attr="info-version"><i class="fa fa-refresh fa-fw fa-spin"></i></code> (Latest: <code>{{ $version->getDaemon() }}</code>)</td>
                            </tr>
                            <tr>
                                <td>System Information</td>
                                <td data-attr="info-system"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                            <tr>
                                <td>Total CPU Threads</td>
                                <td data-attr="info-cpus"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @if($liveNodeStatsEnabled)
            <div class="col-xs-12">
                <div class="box box-success" id="about-live-node-usage">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-circle fa-fw" id="about-live-indicator" style="color:#f39c12; font-size:10px; vertical-align:middle;"></i>
                            Live Node Usage
                            <small id="about-live-summary" style="margin-left:8px;">Connecting&hellip;</small>
                        </h3>
                        <div class="box-tools pull-right">
                            <span class="badge bg-yellow" id="about-live-status-badge">Connecting&hellip;</span>
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div id="about-live-loading" class="text-center" style="padding:16px;">
                            <i class="fa fa-refresh fa-spin fa-2x"></i>
                            <p style="margin-top:10px; color:#888;">Fetching live data from this node's Wings Agent&hellip;</p>
                        </div>
                        <div id="about-live-content" style="display:none;">
                            <div class="row">
                                <div class="col-lg-2 col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-green" id="about-live-cpu">&mdash;</span>
                                        <h5 class="description-header">CPU</h5>
                                        <span class="description-text">current usage</span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-aqua" id="about-live-mem">&mdash;</span>
                                        <h5 class="description-header">Memory Used</h5>
                                        <span class="description-text" id="about-live-mem-total">&mdash;</span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-yellow" id="about-live-disk">&mdash;</span>
                                        <h5 class="description-header">Disk Used</h5>
                                        <span class="description-text" id="about-live-disk-total">&mdash;</span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-green" id="about-live-netin">&mdash;</span>
                                        <h5 class="description-header">Network In</h5>
                                        <span class="description-text">bytes/s</span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-xs-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-red" id="about-live-netout">&mdash;</span>
                                        <h5 class="description-header">Network Out</h5>
                                        <span class="description-text">bytes/s</span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-xs-6">
                                    <div class="description-block">
                                        <span class="description-percentage text-purple" id="about-live-conns">&mdash;</span>
                                        <h5 class="description-header">Connections</h5>
                                        <span class="description-text" id="about-live-version">&mdash;</span>
                                    </div>
                                </div>
                            </div>

                            <hr style="margin:10px 0;">

                            <div class="table-responsive">
                                <table class="table table-condensed table-hover" style="margin-bottom:0;">
                                    <thead>
                                        <tr>
                                            <th>Node</th>
                                            <th>Status</th>
                                            <th>CPU</th>
                                            <th>Memory</th>
                                            <th>Disk</th>
                                            <th>Net In</th>
                                            <th>Net Out</th>
                                            <th>Conns</th>
                                            <th>Agent Version</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="about-live-row">
                                            <td><strong>{{ $node->name }}</strong></td>
                                            <td colspan="8" class="text-muted">Waiting for first stats snapshot&hellip;</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="about-live-unavailable" style="display:none;">
                            <div class="callout callout-warning">
                                <h4><i class="fa fa-warning"></i> Wings Agent Unreachable</h4>
                                <p>This node's Wings Agent could not be reached. Check that the agent is installed and reachable for this node.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-xs-12" id="wings-agent-update-panel" style="display:none;">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload fa-fw"></i> Wings Agent Update Available</h3>
                    </div>
                    <div class="box-body">
                        <p id="wings-agent-update-msg" class="no-margin"></p>
                    </div>
                    <div class="box-footer">
                        <button id="wings-agent-update-btn" class="btn btn-warning btn-sm">
                            <i class="fa fa-upload"></i> Update Agent Now
                        </button>
                    </div>
                </div>
            </div>
            @if ($node->description)
                <div class="col-xs-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            Description
                        </div>
                        <div class="box-body table-responsive">
                            <pre>{{ $node->description }}</pre>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-xs-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Delete Node</h3>
                    </div>
                    <div class="box-body">
                        <p class="no-margin">Deleting a node is a irreversible action and will immediately remove this node from the panel. There must be no servers associated with this node in order to continue.</p>
                    </div>
                    <div class="box-footer">
                        <form action="{{ route('admin.nodes.view.delete', $node->id) }}" method="POST">
                            {!! csrf_field() !!}
                            {!! method_field('DELETE') !!}
                            <div class="checkbox" style="margin-top:0;">
                                <label for="force_delete_allocations">
                                    <input type="checkbox" id="force_delete_allocations" name="force_delete_allocations" value="1" {{ ($node->servers_count < 1) ?: 'disabled' }}>
                                    Force delete stale allocations assigned to servers on other nodes.
                                </label>
                                <p class="help-block" style="margin-bottom:0;">This removes allocations on this node without deleting the linked server. The node still cannot be deleted while it has servers attached.</p>
                            </div>
                            @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nodes.delete'))
                            <button type="submit" class="btn btn-danger btn-sm pull-right" {{ ($node->servers_count < 1) ?: 'disabled' }}>Yes, Delete This Node</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">At-a-Glance</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    @if($node->maintenance_mode)
                    <div class="col-sm-12">
                        <div class="info-box bg-orange">
                            <span class="info-box-icon"><i class="ion ion-wrench"></i></span>
                            <div class="info-box-content" style="padding: 23px 10px 0;">
                                <span class="info-box-text">This node is under</span>
                                <span class="info-box-number">Maintenance</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-sm-12">
                        <div class="info-box bg-{{ $stats['disk']['css'] }}">
                            <span class="info-box-icon"><i class="ion ion-ios-folder-outline"></i></span>
                            <div class="info-box-content" style="padding: 15px 10px 0;">
                                <span class="info-box-text">Disk Space Allocated</span>
                                <span class="info-box-number">{{ $stats['disk']['value'] }} / {{ $stats['disk']['max'] }} MiB</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $stats['disk']['percent'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="info-box bg-{{ $stats['memory']['css'] }}">
                            <span class="info-box-icon"><i class="ion ion-ios-barcode-outline"></i></span>
                            <div class="info-box-content" style="padding: 15px 10px 0;">
                                <span class="info-box-text">Memory Allocated</span>
                                <span class="info-box-number">{{ $stats['memory']['value'] }} / {{ $stats['memory']['max'] }} MiB</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $stats['memory']['percent'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="ion ion-social-buffer-outline"></i></span>
                            <div class="info-box-content" style="padding: 23px 10px 0;">
                                <span class="info-box-text">Total Servers</span>
                                <span class="info-box-number">
                                    {{ $node->servers_count }}
                                    @if(!is_null($node->server_limit))
                                        / {{ $node->server_limit }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    (function getInformation() {
        $.ajax({
            method: 'GET',
            url: '/admin/nodes/view/{{ $node->id }}/system-information',
            timeout: 5000,
        }).done(function (data) {
            $('[data-attr="info-version"]').html(escapeHtml(data.version));
            $('[data-attr="info-system"]').html(escapeHtml(data.system.type) + ' (' + escapeHtml(data.system.arch) + ') <code>' + escapeHtml(data.system.release) + '</code>');
            $('[data-attr="info-cpus"]').html(data.system.cpus);
        }).fail(function (jqXHR) {

        }).always(function() {
            setTimeout(getInformation, 10000);
        });
    })();

    @if($liveNodeStatsEnabled)
    (function streamAboutLiveNodeUsage() {
        var ticketUrl = '{{ route('admin.nodes.wings-agent.ticket', $node->id) }}';
        var nodeName = @json($node->name);
        var eventSource = null;
        var ticketRefreshTimer = null;
        var reconnectTimer = null;
        var shuttingDown = false;

        function fmtBytes(b) {
            if (b === null || b === undefined) return '&mdash;';
            if (b >= 1073741824) return (b / 1073741824).toFixed(1) + ' GB';
            if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
            if (b >= 1024) return (b / 1024).toFixed(1) + ' KB';
            return b + ' B';
        }

        function fmtBytesPerSec(b) {
            return fmtBytes(b) + '/s';
        }

        function fmtCpu(v) {
            return parseFloat(v || 0).toFixed(1) + '%';
        }

        function statusBadge(online) {
            if (online) {
                return '<span class="label label-success"><i class="fa fa-check-circle"></i> Online</span>';
            }
            return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Offline</span>';
        }

        function parseStats(raw) {
            var cpu = (raw.cpu && raw.cpu.usage_percent) || 0;
            var memUsed = ((raw.memory && raw.memory.used_mb) || 0) * 1048576;
            var memTotal = ((raw.memory && raw.memory.total_mb) || 0) * 1048576;
            var diskUsed = 0, diskTotal = 0;
            (raw.disks || []).forEach(function(d) {
                diskUsed += (d.used_gb || 0) * 1073741824;
                diskTotal += (d.total_gb || 0) * 1073741824;
            });
            var netIn = 0, netOut = 0;
            (raw.network || []).forEach(function(n) {
                netIn += (n.speed_recv_kb || 0) * 1024;
                netOut += (n.speed_send_kb || 0) * 1024;
            });
            var conns = ((raw.connections && raw.connections.tcp_total) || 0) +
                        ((raw.connections && raw.connections.udp_total) || 0);

            return {
                cpu: cpu,
                memory_used: Math.round(memUsed),
                memory_total: Math.round(memTotal),
                disk_used: Math.round(diskUsed),
                disk_total: Math.round(diskTotal),
                net_in: Math.round(netIn),
                net_out: Math.round(netOut),
                connections: conns,
                version: raw.version || null,
            };
        }

        function renderOnline(n) {
            var memTotal = n.memory_total || 0;
            var diskTotal = n.disk_total || 0;
            var memPct = memTotal > 0 ? Math.round((n.memory_used || 0) / memTotal * 100) : 0;
            var diskPct = diskTotal > 0 ? Math.round((n.disk_used || 0) / diskTotal * 100) : 0;
            var memBar = memPct >= 90 ? 'danger' : (memPct >= 70 ? 'warning' : 'success');
            var diskBar = diskPct >= 90 ? 'danger' : (diskPct >= 70 ? 'warning' : 'success');
            var safeVersion = n.version ? escapeHtml(n.version) : null;

            $('#about-live-loading').hide();
            $('#about-live-unavailable').hide();
            $('#about-live-content').show();
            $('#about-live-status-badge').removeClass('bg-yellow bg-red').addClass('bg-green').text('Online');
            $('#about-live-indicator').css('color', '#00a65a');
            $('#about-live-summary').text('live stats active');

            $('#about-live-cpu').text(fmtCpu(n.cpu));
            $('#about-live-mem').html(fmtBytes(n.memory_used));
            $('#about-live-mem-total').html('of ' + fmtBytes(memTotal));
            $('#about-live-disk').html(fmtBytes(n.disk_used));
            $('#about-live-disk-total').html('of ' + fmtBytes(diskTotal));
            $('#about-live-netin').html(fmtBytesPerSec(n.net_in));
            $('#about-live-netout').html(fmtBytesPerSec(n.net_out));
            $('#about-live-conns').text((n.connections || 0).toLocaleString());
            $('#about-live-version').html(safeVersion ? 'agent ' + safeVersion : '&mdash;');

            var row = '<td><strong>' + escapeHtml(nodeName) + '</strong></td>';
            row += '<td>' + statusBadge(true) + '</td>';
            row += '<td>' + fmtCpu(n.cpu) + '</td>';
            row += '<td><div class="progress progress-xs" style="margin-bottom:2px;"><div class="progress-bar progress-bar-' + memBar + '" style="width:' + memPct + '%"></div></div>' + fmtBytes(n.memory_used) + ' / ' + fmtBytes(memTotal) + '</td>';
            row += '<td><div class="progress progress-xs" style="margin-bottom:2px;"><div class="progress-bar progress-bar-' + diskBar + '" style="width:' + diskPct + '%"></div></div>' + fmtBytes(n.disk_used) + ' / ' + fmtBytes(diskTotal) + '</td>';
            row += '<td>' + fmtBytesPerSec(n.net_in) + '</td>';
            row += '<td>' + fmtBytesPerSec(n.net_out) + '</td>';
            row += '<td>' + (n.connections || 0).toLocaleString() + '</td>';
            row += '<td>' + (safeVersion ? '<code>' + safeVersion + '</code>' : '<span class="text-muted">&mdash;</span>') + '</td>';
            $('#about-live-row').html(row);
        }

        function renderOffline() {
            $('#about-live-loading').hide();
            $('#about-live-content').hide();
            $('#about-live-unavailable').show();
            $('#about-live-status-badge').removeClass('bg-green bg-yellow').addClass('bg-red').text('Offline');
            $('#about-live-indicator').css('color', '#dd4b39');
            $('#about-live-summary').text('agent unreachable');
        }

        function closeStream() {
            if (eventSource) {
                eventSource.onmessage = null;
                eventSource.onerror = null;
                eventSource.close();
                eventSource = null;
            }
        }

        function clearReconnectTimer() {
            if (reconnectTimer) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }
        }

        function clearTicketRefreshTimer() {
            if (ticketRefreshTimer) {
                clearTimeout(ticketRefreshTimer);
                ticketRefreshTimer = null;
            }
        }

        function scheduleReconnect(delay) {
            if (shuttingDown) return;
            clearReconnectTimer();
            reconnectTimer = setTimeout(function() {
                reconnectTimer = null;
                if (!document.hidden) {
                    startStream();
                }
            }, delay);
        }

        function fetchTicket() {
            return fetch(ticketUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            });
        }

        function connectStream(ticketData) {
            if (shuttingDown || !ticketData || !ticketData.base_url || !ticketData.ticket) return;

            closeStream();
            clearTicketRefreshTimer();

            var streamUrl = ticketData.base_url + '/stats/stream?interval=5&ticket=' + encodeURIComponent(ticketData.ticket);
            eventSource = new EventSource(streamUrl);

            var refreshMs = Math.max(30000, ((parseInt(ticketData.ttl, 10) || 120) - 10) * 1000);
            ticketRefreshTimer = setTimeout(function() {
                if (!shuttingDown && !document.hidden) {
                    startStream();
                }
            }, refreshMs);

            eventSource.onmessage = function(event) {
                try {
                    renderOnline(parseStats(JSON.parse(event.data) || {}));
                } catch (e) {}
            };

            eventSource.onerror = function() {
                renderOffline();
                closeStream();
                scheduleReconnect(5000);
            };
        }

        function startStream() {
            if (shuttingDown) return;
            clearReconnectTimer();
            $('#about-live-loading').show();
            $('#about-live-unavailable').hide();
            $('#about-live-status-badge').removeClass('bg-green bg-red').addClass('bg-yellow').text('Connecting…');
            $('#about-live-indicator').css('color', '#f39c12');
            $('#about-live-summary').text('connecting to Wings Agent stream');

            fetchTicket()
                .then(connectStream)
                .catch(function() {
                    renderOffline();
                    scheduleReconnect(5000);
                });
        }

        function shutdownStream() {
            shuttingDown = true;
            clearReconnectTimer();
            clearTicketRefreshTimer();
            closeStream();
        }

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                closeStream();
                clearReconnectTimer();
                clearTicketRefreshTimer();
            } else {
                startStream();
            }
        });
        window.addEventListener('beforeunload', shutdownStream);
        window.addEventListener('pagehide', shutdownStream);
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                shuttingDown = false;
                startStream();
            }
        });

        startStream();
    })();
    @endif

    (function checkAgentVersion() {
        var versionUrl = '{{ route('admin.nodes.wings-agent.version', $node->id) }}';
        var updateUrl  = '{{ route('admin.nodes.wings-agent.update', $node->id) }}';
        var csrfToken  = '{{ csrf_token() }}';

        fetch(versionUrl)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.up_to_date || !data.current_version) return;

            var panel = document.getElementById('wings-agent-update-panel');
            var msg   = document.getElementById('wings-agent-update-msg');
            msg.innerHTML = 'Your Wings Agent is running <strong>v' + escapeHtml(data.current_version) + '</strong>.'
                          + ' A newer version <strong>v' + escapeHtml(data.latest_version) + '</strong> is available.';
            panel.style.display = '';

            document.getElementById('wings-agent-update-btn').addEventListener('click', function () {
                var btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating&hellip;';

                fetch(updateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                })
                .then(function (r) { return r.json(); })
                .then(function (resp) {
                    if (resp.error) {
                        btn.innerHTML = '<i class="fa fa-times"></i> Error: ' + escapeHtml(resp.error);
                        btn.disabled = false;
                    } else {
                        btn.innerHTML = '<i class="fa fa-check"></i> Update sent — agent restarting';
                        btn.className = btn.className.replace('btn-warning', 'btn-success');
                        document.querySelector('#wings-agent-update-panel .box').className =
                            document.querySelector('#wings-agent-update-panel .box').className.replace('box-warning', 'box-success');
                    }
                })
                .catch(function () {
                    btn.innerHTML = '<i class="fa fa-times"></i> Request failed';
                    btn.disabled = false;
                });
            });
        })
        .catch(function () {
        });
    })();

    </script>
@endsection
