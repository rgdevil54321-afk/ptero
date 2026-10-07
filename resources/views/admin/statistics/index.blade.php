@extends('layouts.admin')

@section('title')
    Statistics
@endsection

@section('content-header')
    <h1>Statistics<small>Overview of your panel's servers, users, and live node usage.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Statistics</li>
    </ol>
@endsection

@section('content')

@php
    $adminTheme = 'default';
    try {
        $adminTheme = app(\Pterodactyl\Repositories\Eloquent\SettingsRepository::class)
            ->get('settings::app:admin_theme', 'default');
    } catch (\Throwable $e) {
        $adminTheme = 'default';
    }
@endphp

@if($adminTheme === 'default')
<style>
    .statistics-info-box.is-total-servers {
        background-color: #eff7ff;
        border-color: #bfdcff;
        color: #0d3b66;
    }

    .statistics-info-box.is-total-servers .info-box-icon {
        background-color: #1e88e5 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-total-users {
        background-color: #edf7f0;
        border-color: #bfe7ca;
        color: #1b4332;
    }

    .statistics-info-box.is-total-users .info-box-icon {
        background-color: #2e7d32 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-total-nodes {
        background-color: #fff8e8;
        border-color: #ffe1a8;
        color: #7c4a03;
    }

    .statistics-info-box.is-total-nodes .info-box-icon {
        background-color: #fb8c00 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-allocations {
        background-color: #f3edff;
        border-color: #d8c8ff;
        color: #4a2c89;
    }

    .statistics-info-box.is-allocations .info-box-icon {
        background-color: #7e57c2 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-databases {
        background-color: #fff1f2;
        border-color: #ffc9cf;
        color: #8b1e2d;
    }

    .statistics-info-box.is-databases .info-box-icon {
        background-color: #d81b60 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-eggs {
        background-color: #eaf8fb;
        border-color: #bfe7ef;
        color: #0a4f5e;
    }

    .statistics-info-box.is-eggs .info-box-icon {
        background-color: #00acc1 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-suspended {
        background-color: #fff0f0;
        border-color: #ffc5c5;
        color: #8a1c1c;
    }

    .statistics-info-box.is-suspended .info-box-icon {
        background-color: #e53935 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-installing {
        background-color: #f5f6f8;
        border-color: #d8dde6;
        color: #2f3e4d;
    }

    .statistics-info-box.is-installing .info-box-icon {
        background-color: #546e7a !important;
        color: #ffffff !important;
    }

    .statistics-info-box .info-box-number,
    .statistics-info-box .info-box-text,
    .statistics-info-box .progress-description {
        color: inherit;
    }

    .statistics-info-box .progress {
        background: rgba(0, 0, 0, 0.08);
    }

    .statistics-info-box .progress .progress-bar {
        background: currentColor;
        opacity: 0.45;
    }
</style>
@endif

<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-total-servers">
            <span class="info-box-icon"><i class="fa fa-server"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Servers</span>
                <span class="info-box-number">{{ $totalServers }}</span>
                <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                <span class="progress-description">
                    {{ $activeServers }} active &bull; {{ $suspendedServers }} suspended &bull; {{ $installingServers }} installing
                </span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-total-users">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">{{ $totalUsers }}</span>
                <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                <span class="progress-description">{{ $adminUsers }} admin{{ $adminUsers === 1 ? '' : 's' }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-total-nodes">
            <span class="info-box-icon"><i class="fa fa-sitemap"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Nodes</span>
                <span class="info-box-number">{{ $totalNodes }}</span>
                <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                <span class="progress-description">{{ $wingsAgentConfigured }} with Wings Agent</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-allocations">
            <span class="info-box-icon"><i class="fa fa-link"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Allocations Used</span>
                <span class="info-box-number">{{ $usedAllocations }} / {{ $totalAllocations }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width:{{ $totalAllocations > 0 ? round($usedAllocations / $totalAllocations * 100) : 0 }}%"></div>
                </div>
                <span class="progress-description">{{ $totalAllocations > 0 ? round($usedAllocations / $totalAllocations * 100) : 0 }}% in use</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-databases">
            <span class="info-box-icon"><i class="fa fa-database"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Databases</span>
                <span class="info-box-number">{{ $totalDatabases }}</span>
                <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                <span class="progress-description">server databases created</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-eggs">
            <span class="info-box-icon"><i class="fa fa-th-large"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Eggs Available</span>
                <span class="info-box-number">{{ $totalEggs }}</span>
                <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                <span class="progress-description">server configuration eggs</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-suspended">
            <span class="info-box-icon"><i class="fa fa-lock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Suspended Servers</span>
                <span class="info-box-number">{{ $suspendedServers }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width:{{ $totalServers > 0 ? round($suspendedServers / $totalServers * 100) : 0 }}%"></div>
                </div>
                <span class="progress-description">{{ $totalServers > 0 ? round($suspendedServers / $totalServers * 100) : 0 }}% of all servers</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box statistics-info-box is-installing">
            <span class="info-box-icon"><i class="fa fa-download"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Installing</span>
                <span class="info-box-number">{{ $installingServers }}</span>
                <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                <span class="progress-description">servers currently installing</span>
            </div>
        </div>
    </div>
</div>

@php
    $memPct  = $nodeTotalMemory > 0 ? round($serverTotalMemory / $nodeTotalMemory * 100) : 0;
    $diskPct = $nodeTotalDisk   > 0 ? round($serverTotalDisk   / $nodeTotalDisk   * 100) : 0;

    $fmtMib = function(int $mib): string {
        if ($mib >= 1024 * 1024) return round($mib / 1024 / 1024, 1) . ' TiB';
        if ($mib >= 1024)        return round($mib / 1024, 1) . ' GiB';
        return $mib . ' MiB';
    };
@endphp

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart fa-fw"></i> Resource Allocation Overview</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong><i class="fa fa-microchip fa-fw text-yellow"></i> CPU Allocated to Servers</strong></p>
                        <div class="progress progress-sm">
                            <div class="progress-bar progress-bar-info" style="min-width:2em; width:100%"></div>
                        </div>
                        <p class="text-muted small">
                            <strong>{{ number_format($serverTotalCpu) }}%</strong> total CPU allocated across all servers
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><i class="fa fa-server fa-fw text-aqua"></i> Memory Allocation</strong></p>
                        <div class="progress progress-sm" style="margin-bottom:4px;">
                            <div class="progress-bar progress-bar-{{ $memPct >= 90 ? 'danger' : ($memPct >= 70 ? 'warning' : 'success') }}"
                                 style="width:{{ min($memPct, 100) }}%"></div>
                        </div>
                        <p class="text-muted small">
                            <strong>{{ $fmtMib($serverTotalMemory) }}</strong> / <strong>{{ $fmtMib($nodeTotalMemory) }}</strong>
                            &mdash; <strong>{{ $memPct }}%</strong> allocated
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><i class="fa fa-hdd-o fa-fw text-green"></i> Disk Allocation</strong></p>
                        <div class="progress progress-sm" style="margin-bottom:4px;">
                            <div class="progress-bar progress-bar-{{ $diskPct >= 90 ? 'danger' : ($diskPct >= 70 ? 'warning' : 'success') }}"
                                 style="width:{{ min($diskPct, 100) }}%"></div>
                        </div>
                        <p class="text-muted small">
                            <strong>{{ $fmtMib($serverTotalDisk) }}</strong> / <strong>{{ $fmtMib($nodeTotalDisk) }}</strong>
                            &mdash; <strong>{{ $diskPct }}%</strong> allocated
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-success" id="live-stats-box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-circle fa-fw" id="live-indicator" style="color:#f39c12; font-size:10px; vertical-align:middle;"></i>
                    Live Node Usage
                    <small id="live-nodes-summary" style="margin-left:8px;"></small>
                </h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-green" id="live-status-badge">Connecting…</span>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" id="live-stats-body">
                <div id="live-loading" class="text-center" style="padding:20px;">
                    <i class="fa fa-refresh fa-spin fa-2x"></i>
                    <p style="margin-top:10px; color:#888;">Fetching live data from Wings Agents…</p>
                </div>

                <div id="live-stats-content" style="display:none;">
                    <div class="row" id="live-totals-row">
                        <div class="col-lg-2 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green" id="lt-cpu">–</span>
                                <h5 class="description-header">Total CPU</h5>
                                <span class="description-text">across all nodes</span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-aqua" id="lt-mem">–</span>
                                <h5 class="description-header">Memory Used</h5>
                                <span class="description-text" id="lt-mem-total">–</span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-yellow" id="lt-disk">–</span>
                                <h5 class="description-header">Disk Used</h5>
                                <span class="description-text" id="lt-disk-total">–</span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green" id="lt-netin">–</span>
                                <h5 class="description-header">Network In</h5>
                                <span class="description-text">bytes/s</span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-red" id="lt-netout">–</span>
                                <h5 class="description-header">Network Out</h5>
                                <span class="description-text">bytes/s</span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-xs-6">
                            <div class="description-block">
                                <span class="description-percentage text-purple" id="lt-conns">–</span>
                                <h5 class="description-header">Connections</h5>
                                <span class="description-text">total open</span>
                            </div>
                        </div>
                    </div>

                    <hr style="margin:10px 0;">

                    <div class="table-responsive" style="margin-top:10px;">
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
                            <tbody id="live-nodes-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="live-unavailable" style="display:none;">
                    <div class="callout callout-warning">
                        <h4><i class="fa fa-warning"></i> Wings Agent Not Configured</h4>
                        <p>No Wings Agent nodes are currently reachable. Install and enable Wings Agent on your nodes to view live node statistics here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o fa-fw"></i> Recently Created Servers <small>(last 7 days)</small></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body no-padding">
                @if($recentServers->isEmpty())
                    <div class="box-body">
                        <p class="text-muted text-center" style="margin:20px 0;">No servers created in the last 7 days.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th>Server Name</th>
                                    <th>UUID</th>
                                    <th>Owner</th>
                                    <th>Node</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentServers as $server)
                                <tr>
                                    <td><strong>{{ $server->name }}</strong></td>
                                    <td><code>{{ substr($server->uuid, 0, 8) }}</code></td>
                                    <td>
                                        @if($server->user)
                                            <a href="{{ route('admin.users.view', $server->user->id) }}">
                                                {{ $server->user->name_first }} {{ $server->user->name_last }}
                                                <small class="text-muted">({{ $server->user->username }})</small>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $server->node->name ?? '—' }}</td>
                                    <td title="{{ $server->created_at }}">
                                        {{ $server->created_at->diffForHumans() }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.servers.view', $server->id) }}" class="btn btn-xs btn-default" style="cursor:pointer;">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer-scripts')
    @parent

    <script>
    (function () {
        var agentEndpointsUrl = '{{ route('admin.statistics.agent-endpoints') }}';
        var liveStatsUrl = '{{ route('admin.statistics.live') }}';
        var sources = {};       // node_id → EventSource
        var nodeStats = {};     // node_id → { online, cpu, memory_used, ... }
        var nodeNames = {};     // node_id → name
        var nodeOrder = [];      // node IDs in the same order as Admin → Nodes
        var ticketExpiry = 0;
        var refreshTimer = null;

        function fmtBytes(b) {
            if (b === null || b === undefined) return '—';
            if (b >= 1073741824) return (b / 1073741824).toFixed(1) + ' GB';
            if (b >= 1048576)    return (b / 1048576).toFixed(1) + ' MB';
            if (b >= 1024)       return (b / 1024).toFixed(1) + ' KB';
            return b + ' B';
        }

        function fmtBytesPerSec(b) {
            return fmtBytes(b) + '/s';
        }

        function fmtCpu(v) {
            return parseFloat(v).toFixed(1) + '%';
        }

        function statusBadge(online) {
            if (online) {
                return '<span class="label label-success"><i class="fa fa-check-circle"></i> Online</span>';
            }
            return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Offline</span>';
        }

        function parseAgentStats(raw) {
            var cpu = (raw.cpu && raw.cpu.usage_percent) || 0;
            var memUsed = ((raw.memory && raw.memory.used_mb) || 0) * 1048576;
            var memTotal = ((raw.memory && raw.memory.total_mb) || 0) * 1048576;
            var diskUsed = 0, diskTotal = 0;
            (raw.disks || []).forEach(function(d) {
                diskUsed  += (d.used_gb  || 0) * 1073741824;
                diskTotal += (d.total_gb || 0) * 1073741824;
            });
            var netIn = 0, netOut = 0;
            (raw.network || []).forEach(function(n) {
                netIn  += (n.speed_recv_kb || 0) * 1024;
                netOut += (n.speed_send_kb || 0) * 1024;
            });
            var conns = ((raw.connections && raw.connections.tcp_total) || 0) +
                        ((raw.connections && raw.connections.udp_total) || 0);
            return {
                online: true,
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

        function renderStats() {
            $('#live-loading').hide();
            var nodeIds = nodeOrder.length ? nodeOrder.slice() : Object.keys(nodeNames);
            if (nodeIds.length === 0) {
                $('#live-stats-content').hide();
                $('#live-unavailable').show();
                $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-yellow').text('Not Configured');
                $('#live-indicator').css('color', '#f39c12');
                return;
            }

            $('#live-unavailable').hide();
            $('#live-stats-content').show();

            var totals = { cpu: 0, memory_used: 0, memory_total: 0, disk_used: 0, disk_total: 0, net_in: 0, net_out: 0, connections: 0 };
            var nodesOn = 0;

            nodeIds.forEach(function(id) {
                var n = nodeStats[id];
                if (n && n.online) {
                    nodesOn++;
                    totals.cpu          += n.cpu;
                    totals.memory_used  += n.memory_used;
                    totals.memory_total += n.memory_total;
                    totals.disk_used    += n.disk_used;
                    totals.disk_total   += n.disk_total;
                    totals.net_in       += n.net_in;
                    totals.net_out      += n.net_out;
                    totals.connections  += n.connections;
                }
            });

            var nodesTotal = nodeIds.length;
            $('#lt-cpu').text(fmtCpu(totals.cpu));
            $('#lt-mem').text(fmtBytes(totals.memory_used));
            $('#lt-mem-total').text('of ' + fmtBytes(totals.memory_total));
            $('#lt-disk').text(fmtBytes(totals.disk_used));
            $('#lt-disk-total').text('of ' + fmtBytes(totals.disk_total));
            $('#lt-netin').text(fmtBytesPerSec(totals.net_in));
            $('#lt-netout').text(fmtBytesPerSec(totals.net_out));
            $('#lt-conns').text(totals.connections.toLocaleString());

            $('#live-nodes-summary').text(nodesOn + ' / ' + nodesTotal + ' nodes online');
            if (nodesOn === nodesTotal && nodesTotal > 0) {
                $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-green').text('All Online');
                $('#live-indicator').css('color', '#00a65a');
            } else if (nodesOn === 0) {
                $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-red').text('All Offline');
                $('#live-indicator').css('color', '#dd4b39');
            } else {
                $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-yellow').text('Partial');
                $('#live-indicator').css('color', '#f39c12');
            }

            var tbody = $('#live-nodes-table-body');
            tbody.empty();
            if (nodeIds.length === 0) {
                tbody.append('<tr><td colspan="9" class="text-center text-muted">No nodes data available.</td></tr>');
                return;
            }

            nodeIds.forEach(function(id) {
                var n = nodeStats[id] || { online: false };
                var name = nodeNames[id] || ('Node #' + id);
                var memTotal = n.memory_total || 0;
                var diskTotal = n.disk_total || 0;
                var memPct = memTotal > 0 ? Math.round((n.memory_used || 0) / memTotal * 100) : 0;
                var diskPct = diskTotal > 0 ? Math.round((n.disk_used || 0) / diskTotal * 100) : 0;
                var memBar = memPct >= 90 ? 'danger' : (memPct >= 70 ? 'warning' : 'success');
                var diskBar = diskPct >= 90 ? 'danger' : (diskPct >= 70 ? 'warning' : 'success');

                var row = '<tr>';
                row += '<td><strong>' + $('<span>').text(name).html() + '</strong></td>';
                row += '<td>' + statusBadge(n.online) + '</td>';

                if (n.online) {
                    row += '<td>' + fmtCpu(n.cpu || 0) + '</td>';
                    row += '<td>';
                    row += '<div class="progress progress-xs" style="margin-bottom:2px;"><div class="progress-bar progress-bar-' + memBar + '" style="width:' + memPct + '%"></div></div>';
                    row += fmtBytes(n.memory_used || 0) + ' / ' + fmtBytes(memTotal);
                    row += '</td>';
                    row += '<td>';
                    row += '<div class="progress progress-xs" style="margin-bottom:2px;"><div class="progress-bar progress-bar-' + diskBar + '" style="width:' + diskPct + '%"></div></div>';
                    row += fmtBytes(n.disk_used || 0) + ' / ' + fmtBytes(diskTotal);
                    row += '</td>';
                    row += '<td>' + fmtBytesPerSec(n.net_in || 0) + '</td>';
                    row += '<td>' + fmtBytesPerSec(n.net_out || 0) + '</td>';
                    row += '<td>' + (n.connections || 0) + '</td>';
                    row += '<td>' + (n.version ? '<code>' + $('<span>').text(n.version).html() + '</code>' : '<span class="text-muted">—</span>') + '</td>';
                } else {
                    row += '<td colspan="7" class="text-muted">Agent unreachable</td>';
                }

                row += '</tr>';
                tbody.append(row);
            });
        }

        function closeAllSources() {
            Object.keys(sources).forEach(function(id) {
                if (sources[id]) {
                    sources[id].onmessage = null;
                    sources[id].onerror = null;
                    sources[id].close();
                    sources[id] = null;
                }
            });
            sources = {};
        }

        function connectSSE(endpoints) {
            closeAllSources();
            nodeOrder = endpoints.map(function(ep) { return String(ep.node_id); });
            endpoints.forEach(function(ep) {
                var id = String(ep.node_id);
                nodeNames[id] = ep.node_name;
                if (!nodeStats[id]) {
                    nodeStats[id] = { online: false };
                }

                var sseUrl = ep.base_url + '/stats/stream?interval=5&ticket=' + encodeURIComponent(ep.ticket);
                try {
                    var es = new EventSource(sseUrl);
                    sources[id] = es;

                    es.onmessage = function(event) {
                        try {
                            var raw = JSON.parse(event.data);
                            nodeStats[id] = parseAgentStats(raw);
                            renderStats();
                        } catch(e) { /* ignore parse errors */ }
                    };

                    es.onerror = function() {
                        nodeStats[id] = { online: false };
                        renderStats();
                    };
                } catch(e) {
                    nodeStats[id] = { online: false };
                }
            });
            renderStats();
        }

        function startSSE() {
            $.ajax({
                url: agentEndpointsUrl,
                method: 'GET',
                timeout: 8000,
                success: function(data) {
                    var endpoints = data.endpoints || [];
                    if (endpoints.length === 0) {
                        fallbackToPolling();
                        return;
                    }
                    ticketExpiry = Date.now() + 90 * 1000; // tickets valid ~120s, refresh at 90s
                    connectSSE(endpoints);
                    if (refreshTimer) clearInterval(refreshTimer);
                    refreshTimer = setInterval(function() {
                        if (document.hidden) return;
                        startSSE(); // re-fetch tickets and reconnect
                    }, 90000);
                },
                error: function() {
                    fallbackToPolling();
                }
            });
        }

        var pollTimer = null;
        function fallbackToPolling() {
            closeAllSources();
            if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }

            function fetchLiveStats() {
                $.ajax({
                    url: liveStatsUrl,
                    method: 'GET',
                    timeout: 8000,
                    success: function(data) {
                        $('#live-loading').hide();
                        if (!data.available) {
                            $('#live-stats-content').hide();
                            $('#live-unavailable').show();
                            $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-yellow').text('Not Configured');
                            $('#live-indicator').css('color', '#f39c12');
                            return;
                        }
                        nodeStats = {};
                        nodeNames = {};
                        nodeOrder = [];
                        (data.nodes || []).forEach(function(n) {
                            var id = String(n.node_id);
                            nodeOrder.push(id);
                            nodeNames[id] = n.node_name;
                            nodeStats[id] = {
                                online: n.online,
                                cpu: n.cpu || 0,
                                memory_used: n.memory_used || 0,
                                memory_total: n.memory_total || 0,
                                disk_used: n.disk_used || 0,
                                disk_total: n.disk_total || 0,
                                net_in: n.net_in || 0,
                                net_out: n.net_out || 0,
                                connections: n.connections || 0,
                                version: n.version || null,
                            };
                        });
                        renderStats();
                    },
                    error: function() {
                        $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-red').text('Error');
                        $('#live-indicator').css('color', '#dd4b39');
                    }
                });
            }

            fetchLiveStats();
            pollTimer = setInterval(fetchLiveStats, 6000);
        }

        startSSE();

        function stopLiveConnections() {
            closeAllSources();
            if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopLiveConnections();
            } else {
                startSSE();
            }
        });

        window.addEventListener('beforeunload', stopLiveConnections);
        window.addEventListener('pagehide', stopLiveConnections);
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) startSSE();
        });
    })();
    </script>
@endsection
