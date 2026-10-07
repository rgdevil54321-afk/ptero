@extends('layouts.admin')

@section('title')
    Radar — {{ $node->name }}
@endsection

@section('content-header')
    <h1>{{ $node->name }}<small>Radar Security Monitor</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.nodes') }}">Nodes</a></li>
        <li><a href="{{ route('admin.nodes.view', $node->id) }}">{{ $node->name }}</a></li>
        <li class="active">Radar</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.nodes.view', $node->id) }}">About</a></li>
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
                <li class="active"><a href="{{ route('admin.nodes.view.radar', $node->id) }}">Radar</a></li>
                <li><a href="{{ route('admin.nodes.view.logs', $node->id) }}">Logs</a></li>
                <li><a href="{{ route('admin.nodes.view.backups', $node->id) }}">Backups</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-shield"></i> Radar Summary</h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" onclick="radarLoadServers()"><i class="fa fa-refresh"></i></button>
                </div>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal" style="margin-bottom:0;">
                    <dt>Agent</dt><dd id="radar-agent-status">Connecting…</dd>
                    <dt>Mode</dt><dd id="radar-mode">—</dd>
                    <dt>Critical</dt><dd id="radar-critical">—</dd>
                    <dt>High</dt><dd id="radar-high">—</dd>
                    <dt>Flagged Files</dt><dd id="radar-files">—</dd>
                    <dt>Last 24h</dt><dd id="radar-24h">—</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-sm-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search"></i> Scan Controls</h3>
            </div>
            <div class="box-body">
                <p style="font-size:12px;color:#777;margin-bottom:10px;">Selected server: <strong id="radar-selected-label">None</strong></p>
                <div class="btn-group" style="margin-bottom:8px;">
                    <button id="radar-btn-scan" class="btn btn-primary btn-sm" onclick="radarScan()" disabled><i class="fa fa-play"></i> Start Scan</button>
                    <button id="radar-btn-baseline" class="btn btn-default btn-sm" onclick="radarBaseline()" disabled><i class="fa fa-check"></i> Create Baseline</button>
                </div>
                <button class="btn btn-warning btn-sm" onclick="radarScanAll()"><i class="fa fa-globe"></i> Start All Server Scan</button>
                <div id="radar-scan-status" style="margin-top:10px; font-size:12px; color:#777;"></div>
                <hr style="margin:12px 0;">
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;">
                        <strong style="font-size:12px;">Auto All-Server Scan</strong>
                        <label style="font-size:12px;margin:0;font-weight:normal;">
                            <input type="checkbox" id="radar-schedule-enabled"> Enabled
                        </label>
                    </div>
                    <div class="row" style="margin-left:-4px;margin-right:-4px;">
                        <div class="col-xs-4" style="padding-left:4px;padding-right:4px;">
                            <select id="radar-schedule-unit" class="form-control input-sm" onchange="radarScheduleUnitChanged()">
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                            </select>
                        </div>
                        <div class="col-xs-4" style="padding-left:4px;padding-right:4px;">
                            <select id="radar-schedule-value" class="form-control input-sm"></select>
                        </div>
                        <div class="col-xs-4" style="padding-left:4px;padding-right:4px;">
                            <button class="btn btn-primary btn-sm btn-block" onclick="radarSaveSchedule(false)">Save</button>
                        </div>
                    </div>
                    <div style="margin-top:8px;">
                        <button class="btn btn-default btn-xs" onclick="radarSaveSchedule(true)"><i class="fa fa-play"></i> Save & Run Now</button>
                        <button class="btn btn-default btn-xs" onclick="radarLoadSchedule()"><i class="fa fa-refresh"></i> Refresh Report</button>
                    </div>
                    <div id="radar-schedule-report" style="margin-top:8px;font-size:12px;color:#777;">Loading schedule…</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-signal"></i> Selected Server Network</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal" style="margin-bottom:0;">
                    <dt>Ingress</dt><dd id="radar-net-in">—</dd>
                    <dt>Egress</dt><dd id="radar-net-out">—</dd>
                    <dt>Packets</dt><dd id="radar-net-packets">—</dd>
                    <dt>Connections</dt><dd id="radar-net-connections">—</dd>
                    <dt>Locks</dt><dd id="radar-net-locks">—</dd>
                    <dt>Processes</dt><dd id="radar-net-processes">—</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-solid box-default" style="margin-bottom:2px;">
            <div class="box-header with-border" style="padding:8px 15px;">
                <h3 class="box-title" style="font-size:13px;"><i class="fa fa-clock-o"></i> Network Chart Time Window</h3>
                <div class="box-tools pull-right">
                    <div class="btn-group" id="radar-tf-btngroup">
                        <button class="btn btn-xs btn-default" data-tf="1440">24h</button>
                        <button class="btn btn-xs btn-default" data-tf="720">12h</button>
                        <button class="btn btn-xs btn-default" data-tf="300">5h</button>
                        <button class="btn btn-xs btn-primary" data-tf="60">1h</button>
                        <button class="btn btn-xs btn-default" data-tf="30">30m</button>
                        <button class="btn btn-xs btn-default" data-tf="10">10m</button>
                        <button class="btn btn-xs btn-default" data-tf="5">5m</button>
                        <button class="btn btn-xs btn-default" data-tf="1">1m</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-line-chart"></i> Network Traffic <small>(live + history, KB/s)</small></h3>
                <div class="box-tools pull-right">
                    <span id="radar-live-status" class="label label-default"><i class="fa fa-circle-o-notch fa-spin"></i> Connecting</span>
                    <span id="radar-hist-load-status" style="font-size:11px;color:#aaa;margin-left:6px;"></span>
                </div>
            </div>
            <div class="box-body">
                <canvas id="radar-chart-traffic" height="80"></canvas>
                <div class="row" style="margin-top:12px;text-align:center;font-size:12px;">
                    <div class="col-xs-3"><span class="text-muted">↓ Current</span><br><strong id="radar-traf-in-cur" class="text-aqua">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">↓ Peak</span><br><strong id="radar-traf-in-peak" class="text-blue">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">↑ Current</span><br><strong id="radar-traf-out-cur" class="text-green">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">↑ Peak</span><br><strong id="radar-traf-out-peak" class="text-yellow">—</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-area-chart"></i> Packet Rate <small>(live + history, pkts/s)</small></h3>
            </div>
            <div class="box-body">
                <canvas id="radar-chart-packets" height="80"></canvas>
                <div class="row" style="margin-top:12px;text-align:center;font-size:12px;">
                    <div class="col-xs-3"><span class="text-muted">↓ Current</span><br><strong id="radar-pkt-in-cur" class="text-aqua">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">↓ Peak</span><br><strong id="radar-pkt-in-peak" class="text-blue">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">↑ Current</span><br><strong id="radar-pkt-out-cur" class="text-green">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">↑ Peak</span><br><strong id="radar-pkt-out-peak" class="text-yellow">—</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> Connection Count <small>(live + history)</small></h3>
            </div>
            <div class="box-body">
                <canvas id="radar-chart-conns" height="60"></canvas>
                <div class="row" style="margin-top:12px;text-align:center;font-size:12px;">
                    <div class="col-xs-3"><span class="text-muted">Incoming Now</span><br><strong id="radar-conn-in-cur" class="text-aqua">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">Incoming Peak</span><br><strong id="radar-conn-in-peak" class="text-blue">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">Outgoing Now</span><br><strong id="radar-conn-out-cur" class="text-green">—</strong></div>
                    <div class="col-xs-3"><span class="text-muted">Outgoing Peak</span><br><strong id="radar-conn-out-peak" class="text-yellow">—</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger" id="radar-alerts-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bell"></i> Radar Alerts</h3>
                <div class="box-tools pull-right">
                    <select id="radar-alerts-per-page" class="form-control input-sm" style="width:90px;display:inline-block;" onchange="radarChangeAlertsPerPage()">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div style="padding:8px 15px;border-bottom:1px solid #f4f4f4;">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" id="radar-alerts-search" class="form-control" placeholder="Search alerts by ID, server, IP, remote, lock, reason, or direction">
                    <span class="input-group-btn">
                        <button class="btn btn-default" id="radar-alerts-search-clear" title="Clear search"><i class="fa fa-times"></i></button>
                    </span>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Alert</th>
                            <th>Direction</th>
                            <th>Scope</th>
                            <th>Target</th>
                            <th>Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="radar-alerts">
                        <tr><td colspan="7" class="text-center text-muted">Loading Radar alerts…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="box-footer clearfix">
                <span id="radar-alerts-page-label" class="text-muted" style="font-size:12px;">—</span>
                <ul class="pagination pagination-sm no-margin pull-right" id="radar-alerts-pagination"></ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-server"></i> Servers With Radar Findings</h3>
                <div class="box-tools pull-right">
                    <span id="radar-server-count" class="label label-default">—</span>
                </div>
            </div>
            <div style="padding:8px 15px;border-bottom:1px solid #f4f4f4;">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" id="radar-server-search" class="form-control" placeholder="Search by server name, UUID, severity, or scan state">
                    <span class="input-group-btn">
                        <button class="btn btn-default" id="radar-server-search-clear" title="Clear search"><i class="fa fa-times"></i></button>
                    </span>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>Server</th>
                            <th>Total Files</th>
                            <th>Last 24h</th>
                            <th>Critical</th>
                            <th>High</th>
                            <th>Open Findings</th>
                            <th>Scan</th>
                        </tr>
                    </thead>
                    <tbody id="radar-server-list">
                        <tr><td colspan="7" class="text-center text-muted">Connecting to Wings Agent…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="box-footer clearfix">
                <span id="radar-server-page-label" class="text-muted" style="font-size:12px;">—</span>
                <ul class="pagination pagination-sm no-margin pull-right" id="radar-server-pagination"></ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger" id="radar-findings-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Flagged Files <small id="radar-findings-server">Select a server</small></h3>
                <div class="box-tools pull-right">
                    <select id="radar-per-page" class="form-control input-sm" style="width:90px;display:inline-block;" onchange="radarChangePerPage()">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div style="padding:8px 15px;border-bottom:1px solid #f4f4f4;">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" id="radar-findings-search" class="form-control" placeholder="Search flagged files by path, hash, rule, severity, or Radar ID">
                    <span class="input-group-btn">
                        <button class="btn btn-default" id="radar-findings-search-clear" title="Clear search"><i class="fa fa-times"></i></button>
                    </span>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Radar ID</th>
                            <th>Rule</th>
                            <th>Path</th>
                            <th>Status</th>
                            <th style="width:210px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="radar-findings">
                        <tr><td colspan="6" class="text-center text-muted">Select a server to view flagged files.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="box-footer clearfix">
                <span id="radar-page-label" class="text-muted" style="font-size:12px;">—</span>
                <ul class="pagination pagination-sm no-margin pull-right" id="radar-findings-pagination"></ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
@parent
@php
    $radarServerMeta = collect($servers ?? [])->mapWithKeys(function ($server) {
        return [$server->uuid => [
            'name' => $server->name,
            'uuidShort' => $server->uuidShort,
        ]];
    })->toArray();
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
"use strict";

var radarTicketUrl = '{{ route('admin.nodes.wings-agent.ticket', $node->id) }}';
var radarServerMeta = @json($radarServerMeta);
var radarAgentTicket = null;
var radarAgentBaseUrl = null;
var radarAgentWsUrl = null;
var radarTicketRefreshTimer = null;
var radarNodeWs = null;
var radarNodeWsReconnectTimer = null;
var radarNetworkWs = null;
var radarNetworkReconnectTimer = null;
var radarSelectedServer = null;
var radarFindingsPage = 1;
var radarFindingsTotalPages = 0;
var radarFindingsTotal = 0;
var radarPerPage = 10;
var radarFindingsSearch = '';
var radarAlertsPage = 1;
var radarAlertsTotalPages = 0;
var radarAlertsTotal = 0;
var radarAlertsPerPage = 10;
var radarAlertsSearch = '';
var radarAlertsRefreshTimer = null;
var radarServerRows = [];
var radarServerPage = 1;
var radarServerPerPage = 10;
var radarServerSearch = '';
var radarHistoryBuffer = [];
var radarActiveMinutes = 60;
var radarPrevProto = null;
var radarPrevProtoAt = null;
var radarPeaks = { trafficIn: 0, trafficOut: 0, pktsIn: 0, pktsOut: 0 };
var radarChartTraffic = null;
var radarChartPackets = null;
var radarChartConns = null;
var radarLoadingServers = false;
var radarInitialized = false;
var radarHistoryRetries = 0;
var radarHistoryRetryTimer = null;
var radarShuttingDown = false;

function radarEscape(value) {
    return String(value || '').replace(/[&<>"']/g, function (ch) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[ch];
    });
}

function radarJs(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

function radarAppendTicket(path) {
    return path + (path.indexOf('?') >= 0 ? '&' : '?') + 'ticket=' + encodeURIComponent(radarAgentTicket || '');
}

function radarSetLiveStatus(text, cls, spin) {
    var el = document.getElementById('radar-live-status');
    if (!el) return;
    el.className = 'label label-' + (cls || 'default');
    el.innerHTML = (spin ? '<i class="fa fa-circle-o-notch fa-spin"></i> ' : '') + radarEscape(text);
}

function radarFetchTicket(force) {
    if (radarShuttingDown) return Promise.reject(new Error('Page is closing'));
    if (!force && radarAgentTicket && radarAgentBaseUrl) {
        return Promise.resolve();
    }
    return fetch(radarTicketUrl, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (response) {
        if (!response.ok) throw new Error('Unable to obtain Wings Agent ticket.');
        return response.json();
    }).then(function (data) {
        radarAgentTicket = data.ticket;
        radarAgentBaseUrl = data.base_url;
        radarAgentWsUrl = data.ws_url;
        document.getElementById('radar-agent-status').textContent = 'Connected';
        if (radarTicketRefreshTimer) clearTimeout(radarTicketRefreshTimer);
        radarTicketRefreshTimer = setTimeout(function () {
            if (radarShuttingDown) return;
            radarFetchTicket(true).then(function () {
                if (radarNodeWs && radarNodeWs.readyState === WebSocket.OPEN) {
                    radarNodeWs.close(1000, 'ticket refresh');
                } else {
                    radarConnectNodeWs();
                }
                radarConnectNetworkWs();
            }).catch(function () {});
        }, Math.max(30000, ((parseInt(data.ttl, 10) || 120) - 10) * 1000));
    }).catch(function (error) {
        document.getElementById('radar-agent-status').textContent = 'Ticket failed';
        throw error;
    });
}

function radarReadJson(response) {
    return response.text().then(function (text) {
        var data = {};
        try {
            data = text ? JSON.parse(text) : {};
        } catch (err) {
            data = { error: text || 'Invalid JSON response.' };
        }
        if (!response.ok) throw new Error(data.error || ('Request failed (' + response.status + ')'));
        return data;
    });
}

function radarAgentGet(path, retry) {
    return radarFetchTicket(false).then(function () {
        return fetch(radarAgentBaseUrl + radarAppendTicket(path), { headers: { 'Accept': 'application/json' } });
    }).then(function (response) {
        if ((response.status === 401 || response.status === 403) && retry !== false) {
            radarAgentTicket = null;
            return radarFetchTicket(true).then(function () { return radarAgentGet(path, false); });
        }
        return radarReadJson(response);
    });
}

function radarAgentPost(path, body, retry) {
    return radarFetchTicket(false).then(function () {
        return fetch(radarAgentBaseUrl + radarAppendTicket(path), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body || {})
        });
    }).then(function (response) {
        if ((response.status === 401 || response.status === 403) && retry !== false) {
            radarAgentTicket = null;
            return radarFetchTicket(true).then(function () { return radarAgentPost(path, body, false); });
        }
        return radarReadJson(response);
    });
}

function radarFormatBytes(bytes) {
    bytes = Number(bytes || 0);
    if (bytes >= 1024 * 1024 * 1024) return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB';
    if (bytes >= 1024 * 1024) return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return Math.round(bytes) + ' B';
}

function radarRate(bytes) {
    return radarFormatBytes(bytes) + '/s';
}

function radarSeverityLabel(severity) {
    var s = String(severity || '').toLowerCase();
    var cls = s === 'critical' ? 'danger' : s === 'high' ? 'warning' : s === 'medium' ? 'info' : 'default';
    return '<span class="label label-' + cls + '">' + radarEscape(s || 'unknown') + '</span>';
}

function radarServerLabel(uuid) {
    var meta = radarServerMeta[uuid] || {};
    return meta.name ? (meta.name + ' (' + (meta.uuidShort || uuid.substring(0, 8)) + ')') : uuid;
}

function radarScanLabel(scan) {
    if (!scan || !scan.status) return '<span class="text-muted">—</span>';
    var cls = scan.status === 'running' || scan.status === 'queued' ? 'info' : scan.status === 'failed' ? 'danger' : 'success';
    return '<span class="label label-' + cls + '">' + radarEscape(scan.status) + '</span>';
}

function radarZeroSummary() {
    return { total: 0, critical: 0, high: 0, medium: 0, low: 0, open: 0, last_24h: 0, flagged_files: 0, flagged_files_24h: 0 };
}

function radarRenderPagination(containerId, currentPage, totalPages, handlerName) {
    var container = document.getElementById(containerId);
    if (!container) return;
    totalPages = Math.max(1, totalPages || 1);
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    var pages = [];
    var pushPage = function (page) {
        if (page >= 1 && page <= totalPages && pages.indexOf(page) === -1) pages.push(page);
    };
    pushPage(1);
    for (var p = currentPage - 2; p <= currentPage + 2; p++) pushPage(p);
    pushPage(totalPages);
    pages.sort(function (a, b) { return a - b; });

    var html = '';
    html += '<li class="' + (currentPage <= 1 ? 'disabled' : '') + '"><a href="#" onclick="' + handlerName + '(' + (currentPage - 1) + ');return false;"><i class="fa fa-chevron-left"></i></a></li>';
    var previous = 0;
    pages.forEach(function (page) {
        if (previous && page - previous > 1) {
            html += '<li class="disabled"><span>…</span></li>';
        }
        html += '<li class="' + (page === currentPage ? 'active' : '') + '"><a href="#" onclick="' + handlerName + '(' + page + ');return false;">' + page + '</a></li>';
        previous = page;
    });
    html += '<li class="' + (currentPage >= totalPages ? 'disabled' : '') + '"><a href="#" onclick="' + handlerName + '(' + (currentPage + 1) + ');return false;"><i class="fa fa-chevron-right"></i></a></li>';
    container.innerHTML = html;
}

function radarServerMatchesSearch(row) {
    var q = radarServerSearch;
    if (!q) return true;
    var s = row.summary || radarZeroSummary();
    var haystack = [
        radarServerLabel(row.server_uuid),
        row.server_uuid,
        (row.scan || {}).status,
        s.critical ? 'critical' : '',
        s.high ? 'high' : '',
        s.open ? 'open' : '',
        s.flagged_files ? 'flagged files' : ''
    ].join(' ').toLowerCase();
    return haystack.indexOf(q) >= 0;
}

function radarLoadServers() {
    if (radarLoadingServers) return;
    radarLoadingServers = true;
    radarAgentGet('/radar/status').then(function (status) {
        var rowsByUuid = {};
        Object.keys(radarServerMeta).forEach(function (uuid) {
            rowsByUuid[uuid] = { server_uuid: uuid, summary: radarZeroSummary(), scan: null, active_locks: [] };
        });
        if (Array.isArray(status.server_summaries)) {
            status.server_summaries.forEach(function (row) { rowsByUuid[row.server_uuid] = row; });
        } else if (status.servers) {
            Object.keys(status.servers).forEach(function (uuid) {
                rowsByUuid[uuid] = Object.assign({ server_uuid: uuid }, status.servers[uuid]);
            });
        }

        var rows = Object.keys(rowsByUuid).map(function (uuid) {
            var item = rowsByUuid[uuid] || {};
            item.server_uuid = item.server_uuid || uuid;
            item.summary = Object.assign(radarZeroSummary(), item.summary || {});
            return item;
        }).sort(function (a, b) {
            var as = a.summary || {}, bs = b.summary || {};
            return (bs.critical - as.critical) || (bs.high - as.high) || (bs.flagged_files - as.flagged_files) || radarServerLabel(a.server_uuid).localeCompare(radarServerLabel(b.server_uuid));
        });

        var totals = rows.reduce(function (acc, row) {
            var s = row.summary || {};
            acc.critical += s.critical || 0;
            acc.high += s.high || 0;
            acc.files += s.flagged_files || 0;
            acc.day += s.flagged_files_24h || s.last_24h || 0;
            return acc;
        }, { critical: 0, high: 0, files: 0, day: 0 });

        document.getElementById('radar-mode').textContent = status.mode || 'alert_only';
        document.getElementById('radar-critical').textContent = totals.critical;
        document.getElementById('radar-high').textContent = totals.high;
        document.getElementById('radar-files').textContent = totals.files;
        document.getElementById('radar-24h').textContent = totals.day;
        radarServerRows = rows;
        document.getElementById('radar-server-count').textContent = rows.length + ' server(s)';
        radarRenderServerRows();
    }).catch(function (error) {
        document.getElementById('radar-agent-status').textContent = 'Agent error';
        document.getElementById('radar-server-list').innerHTML = '<tr><td colspan="7" class="text-danger text-center">' + radarEscape(error.message || 'Failed to load Radar servers.') + '</td></tr>';
    }).finally(function () {
        radarLoadingServers = false;
    });
}

function radarRenderServerRows() {
    var tbody = document.getElementById('radar-server-list');
    var filtered = radarServerRows.filter(radarServerMatchesSearch);
    var totalPages = Math.max(1, Math.ceil(filtered.length / radarServerPerPage));
    if (radarServerPage > totalPages) radarServerPage = totalPages;
    var start = (radarServerPage - 1) * radarServerPerPage;
    var pageRows = filtered.slice(start, start + radarServerPerPage);

    if (!pageRows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No servers found for this node.</td></tr>';
        document.getElementById('radar-server-page-label').textContent = filtered.length + ' server(s)';
        radarRenderPagination('radar-server-pagination', radarServerPage, totalPages, 'radarServerGotoPage');
        return;
    }
    tbody.innerHTML = pageRows.map(function (row) {
        var s = row.summary || radarZeroSummary();
        var selected = row.server_uuid === radarSelectedServer;
        return '<tr class="' + (selected ? 'info' : '') + '" style="cursor:pointer;" onclick="radarSelectServer(\'' + radarJs(row.server_uuid) + '\')">'
            + '<td><strong>' + radarEscape(radarServerLabel(row.server_uuid)) + '</strong><br><small><code>' + radarEscape(row.server_uuid) + '</code></small></td>'
            + '<td>' + (s.flagged_files || 0) + '</td>'
            + '<td>' + (s.flagged_files_24h || s.last_24h || 0) + '</td>'
            + '<td><span class="label label-danger">' + (s.critical || 0) + '</span></td>'
            + '<td><span class="label label-warning">' + (s.high || 0) + '</span></td>'
            + '<td>' + (s.open || 0) + '</td>'
            + '<td>' + radarScanLabel(row.scan) + '</td>'
            + '</tr>';
    }).join('');
    document.getElementById('radar-server-page-label').textContent = (start + 1) + '-' + Math.min(start + pageRows.length, filtered.length) + ' of ' + filtered.length + ' server(s)';
    radarRenderPagination('radar-server-pagination', radarServerPage, totalPages, 'radarServerGotoPage');
}

function radarServerGotoPage(page) {
    var filtered = radarServerRows.filter(radarServerMatchesSearch);
    var totalPages = Math.max(1, Math.ceil(filtered.length / radarServerPerPage));
    radarServerPage = Math.max(1, Math.min(totalPages, parseInt(page, 10) || 1));
    radarRenderServerRows();
}

function radarSelectServer(uuid) {
    radarSelectedServer = uuid;
    radarFindingsPage = 1;
    document.getElementById('radar-selected-label').textContent = radarServerLabel(uuid);
    document.getElementById('radar-findings-server').textContent = radarServerLabel(uuid);
    document.getElementById('radar-btn-scan').disabled = false;
    document.getElementById('radar-btn-baseline').disabled = false;
    radarLoadServers();
    radarLoadFindings();
    radarConnectNetworkWs();
}

function radarLoadFindings() {
    if (!radarSelectedServer) {
        document.getElementById('radar-findings').innerHTML = '<tr><td colspan="6" class="text-center text-muted">Select a server to view flagged files.</td></tr>';
        document.getElementById('radar-page-label').textContent = '—';
        radarRenderPagination('radar-findings-pagination', 1, 1, 'radarFindingsGotoPage');
        return;
    }
    var path = '/radar/findings?server_uuid=' + encodeURIComponent(radarSelectedServer)
        + '&page=' + encodeURIComponent(radarFindingsPage)
        + '&per_page=' + encodeURIComponent(radarPerPage)
        + '&search=' + encodeURIComponent(radarFindingsSearch);
    document.getElementById('radar-findings').innerHTML = '<tr><td colspan="6" class="text-center text-muted">Loading flagged files…</td></tr>';
    radarAgentGet(path).then(function (data) {
        radarFindingsPage = data.page || 1;
        radarFindingsTotalPages = data.total_pages || 0;
        radarFindingsTotal = data.total || 0;
        radarRenderFindings(data.findings || []);
        var start = radarFindingsTotal === 0 ? 0 : ((radarFindingsPage - 1) * radarPerPage) + 1;
        var end = Math.min(radarFindingsPage * radarPerPage, radarFindingsTotal);
        document.getElementById('radar-page-label').textContent = start + '-' + end + ' of ' + radarFindingsTotal + ' finding(s)';
        radarRenderPagination('radar-findings-pagination', radarFindingsPage, Math.max(1, radarFindingsTotalPages || 1), 'radarFindingsGotoPage');
    }).catch(function (error) {
        document.getElementById('radar-findings').innerHTML = '<tr><td colspan="6" class="text-danger text-center">' + radarEscape(error.message || 'Failed to load findings.') + '</td></tr>';
    });
}

function radarRenderFindings(rows) {
    var tbody = document.getElementById('radar-findings');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No Radar findings for this server.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function (f) {
        return '<tr>'
            + '<td>' + radarSeverityLabel(f.severity) + '</td>'
            + '<td><code>' + radarEscape(f.id) + '</code></td>'
            + '<td><code>' + radarEscape(f.rule_id) + '</code><br><small>' + radarEscape(f.admin_summary || f.public_message) + '</small></td>'
            + '<td><code>' + radarEscape(f.path) + '</code><br><small>' + radarEscape(f.sha256) + '</small></td>'
            + '<td>' + radarEscape(f.status || 'open') + '</td>'
            + '<td>'
            + '<button class="btn btn-xs btn-warning" onclick="radarAction(\'' + radarJs(f.server_uuid) + '\', \'' + radarJs(f.id) + '\', \'quarantine\')">Quarantine</button> '
            + '<button class="btn btn-xs btn-danger" onclick="radarAction(\'' + radarJs(f.server_uuid) + '\', \'' + radarJs(f.id) + '\', \'delete\')">Delete</button> '
            + '<button class="btn btn-xs btn-success" onclick="radarAction(\'' + radarJs(f.server_uuid) + '\', \'' + radarJs(f.id) + '\', \'trust_hash\')">Trust Hash</button>'
            + '</td>'
            + '</tr>';
    }).join('');
}

function radarAlertRemote(alert) {
    if (!alert || !alert.remote_ip) return '';
    var remote = alert.remote_ip;
    if (remote.indexOf(':') >= 0 && remote.charAt(0) !== '[') {
        remote = '[' + remote + ']';
    }
    if (alert.protocol) remote = alert.protocol + '://' + remote;
    if (alert.port) remote += ':' + alert.port;
    return remote;
}

function radarAlertRate(alert) {
    var parts = [];
    if (alert.packets_per_second) parts.push(Math.round(alert.packets_per_second) + ' pps');
    if (alert.bytes_per_second) parts.push(radarRate(alert.bytes_per_second));
    if (alert.remote_count) parts.push(alert.remote_count + ' remote(s)');
    return parts.length ? parts.join('<br>') : '<span class="text-muted">—</span>';
}

function radarRenderAlerts(rows) {
    var tbody = document.getElementById('radar-alerts');
    if (!tbody) return;
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No Radar alerts found.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function (alert) {
        var remote = radarAlertRemote(alert);
        var target = [];
        if (alert.server_uuid) target.push('<strong>Server:</strong> ' + radarEscape(radarServerLabel(alert.server_uuid)) + '<br><small><code>' + radarEscape(alert.server_uuid) + '</code></small>');
        if (alert.container_ip) target.push('<strong>Container:</strong> <code>' + radarEscape(alert.container_ip) + '</code>');
        if (alert.local_ip) target.push('<strong>Local:</strong> <code>' + radarEscape(alert.local_ip) + '</code>');
        if (remote) target.push('<strong>Remote:</strong> <code>' + radarEscape(remote) + '</code>');
        if (alert.expires_at) target.push('<strong>Expires:</strong> ' + radarEscape(radarFormatScheduleTime(alert.expires_at)));
        if (!target.length) target.push('<span class="text-muted">Node-wide</span>');
        return '<tr>'
            + '<td>' + radarSeverityLabel(alert.severity) + '</td>'
            + '<td><code>' + radarEscape(alert.id) + '</code><br><strong>' + radarEscape(alert.title || alert.kind) + '</strong><br><small>' + radarEscape(alert.message || '') + '</small>'
            + (alert.reason ? '<br><small><strong>Reason:</strong> ' + radarEscape(alert.reason) + '</small>' : '')
            + (alert.lock_id ? '<br><small><strong>Lock:</strong> <code>' + radarEscape(alert.lock_id) + '</code></small>' : '')
            + '</td>'
            + '<td>' + radarEscape(alert.direction || '—') + '</td>'
            + '<td>' + radarEscape(alert.scope || '—') + '</td>'
            + '<td>' + target.join('<br>') + '</td>'
            + '<td>' + radarAlertRate(alert) + '</td>'
            + '<td>' + radarEscape(alert.status || 'open') + '<br><small>' + radarEscape(radarFormatScheduleTime(alert.created_at)) + '</small></td>'
            + '</tr>';
    }).join('');
}

function radarLoadAlerts() {
    var path = '/radar/alerts?page=' + encodeURIComponent(radarAlertsPage)
        + '&per_page=' + encodeURIComponent(radarAlertsPerPage)
        + '&search=' + encodeURIComponent(radarAlertsSearch);
    return radarAgentGet(path).then(function (data) {
        radarAlertsPage = data.page || 1;
        radarAlertsTotalPages = data.total_pages || 0;
        radarAlertsTotal = data.total || 0;
        radarRenderAlerts(data.alerts || []);
        var start = radarAlertsTotal === 0 ? 0 : ((radarAlertsPage - 1) * radarAlertsPerPage) + 1;
        var end = Math.min(radarAlertsPage * radarAlertsPerPage, radarAlertsTotal);
        document.getElementById('radar-alerts-page-label').textContent = start + '-' + end + ' of ' + radarAlertsTotal + ' alert(s)';
        radarRenderPagination('radar-alerts-pagination', radarAlertsPage, Math.max(1, radarAlertsTotalPages || 1), 'radarAlertsGotoPage');
    }).catch(function (error) {
        var tbody = document.getElementById('radar-alerts');
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-danger text-center">' + radarEscape(error.message || 'Failed to load Radar alerts.') + '</td></tr>';
    });
}

function radarAlertsGotoPage(page) {
    var totalPages = Math.max(1, radarAlertsTotalPages || 1);
    radarAlertsPage = Math.max(1, Math.min(totalPages, parseInt(page, 10) || 1));
    radarLoadAlerts();
}

function radarChangeAlertsPerPage() {
    var el = document.getElementById('radar-alerts-per-page');
    radarAlertsPerPage = parseInt(el ? el.value : '10', 10) || 10;
    radarAlertsPage = 1;
    radarLoadAlerts();
}

function radarPrevPage() {
    if (radarFindingsPage <= 1) return;
    radarFindingsGotoPage(radarFindingsPage - 1);
}

function radarNextPage() {
    if (radarFindingsTotalPages && radarFindingsPage >= radarFindingsTotalPages) return;
    radarFindingsGotoPage(radarFindingsPage + 1);
}

function radarFindingsGotoPage(page) {
    var totalPages = Math.max(1, radarFindingsTotalPages || 1);
    radarFindingsPage = Math.max(1, Math.min(totalPages, parseInt(page, 10) || 1));
    radarLoadFindings();
}

function radarChangePerPage() {
    radarPerPage = parseInt(document.getElementById('radar-per-page').value, 10) || 10;
    radarFindingsPage = 1;
    radarLoadFindings();
}

function radarScan() {
    if (!radarSelectedServer) return;
    radarAgentPost('/radar/scan/server', { server_uuid: radarSelectedServer, force: true }).then(function (data) {
        document.getElementById('radar-scan-status').textContent = data.error || ('Scan ' + ((data.scan || {}).status || 'queued') + ' ' + ((data.scan || {}).id || ''));
        radarLoadServers();
        radarLoadFindings();
        radarLoadAlerts();
    }).catch(function (error) {
        document.getElementById('radar-scan-status').textContent = error.message || 'Unable to start scan.';
    });
}

function radarScanAll() {
    radarAgentPost('/radar/scan/all', { force: true }).then(function (data) {
        var failed = data.errors ? Object.keys(data.errors).length : 0;
        document.getElementById('radar-scan-status').textContent = data.error || ('All-server scan started for ' + (data.started || 0) + '/' + (data.total || 0) + ' server(s). ' + (failed ? failed + ' failed.' : ''));
        radarLoadServers();
        radarLoadAlerts();
    }).catch(function (error) {
        document.getElementById('radar-scan-status').textContent = error.message || 'Unable to start all-server scan.';
    });
}

function radarBaseline() {
    if (!radarSelectedServer) return;
    radarAgentPost('/radar/baseline/create', { server_uuid: radarSelectedServer }).then(function (data) {
        document.getElementById('radar-scan-status').textContent = data.error || 'Baseline saved.';
    }).catch(function (error) {
        document.getElementById('radar-scan-status').textContent = error.message || 'Unable to create baseline.';
    });
}

function radarFormatScheduleTime(value) {
    if (!value) return '—';
    var date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleString();
}

function radarScheduleUnitChanged(selectedValue) {
    var unitEl = document.getElementById('radar-schedule-unit');
    var valueEl = document.getElementById('radar-schedule-value');
    if (!unitEl || !valueEl) return;
    var unit = unitEl.value || 'hours';
    var max = unit === 'hours' ? 23 : unit === 'days' ? 6 : 3;
    var current = parseInt(selectedValue || valueEl.value || '1', 10) || 1;
    if (current > max) current = max;
    var html = '';
    for (var i = 1; i <= max; i++) {
        html += '<option value="' + i + '"' + (i === current ? ' selected' : '') + '>' + i + '</option>';
    }
    valueEl.innerHTML = html;
}

function radarRenderSchedule(state) {
    var enabled = document.getElementById('radar-schedule-enabled');
    var unit = document.getElementById('radar-schedule-unit');
    var reportEl = document.getElementById('radar-schedule-report');
    if (enabled) enabled.checked = !!state.enabled;
    if (unit) unit.value = state.interval_unit || 'hours';
    radarScheduleUnitChanged(state.interval_value || 1);

    var report = state.last_report || {};
    var lines = [];
    lines.push('<div><strong>Status:</strong> ' + (state.running ? '<span class="label label-info">Running</span>' : (state.enabled ? '<span class="label label-success">Scheduled</span>' : '<span class="label label-default">Disabled</span>')) + '</div>');
    lines.push('<div><strong>Next run:</strong> ' + radarEscape(radarFormatScheduleTime(state.next_run)) + '</div>');
    if (report.id) {
        lines.push('<div><strong>Last report:</strong> ' + radarEscape(report.id) + '</div>');
        lines.push('<div><strong>Scanned:</strong> ' + (report.started || 0) + '/' + (report.total_servers || 0) + ' server(s), <strong>queued:</strong> ' + (report.queued || 0) + ', <strong>failed:</strong> ' + (report.failed || 0) + '</div>');
        lines.push('<div><strong>Findings:</strong> ' + (report.findings || 0) + ', <strong>flagged files:</strong> ' + (report.flagged_files || 0) + '</div>');
        lines.push('<div><strong>Started:</strong> ' + radarEscape(radarFormatScheduleTime(report.started_at)) + ' <strong>Finished:</strong> ' + radarEscape(radarFormatScheduleTime(report.finished_at)) + '</div>');
    } else {
        lines.push('<div>No scheduled scan report yet.</div>');
    }
    if (report.errors && Object.keys(report.errors).length) {
        lines.push('<div class="text-danger"><strong>Errors:</strong> ' + radarEscape(Object.keys(report.errors).length + ' server(s) or node errors') + '</div>');
    }
    if (reportEl) reportEl.innerHTML = lines.join('');
}

function radarLoadSchedule() {
    return radarAgentGet('/radar/schedule').then(radarRenderSchedule).catch(function (error) {
        var reportEl = document.getElementById('radar-schedule-report');
        if (reportEl) reportEl.innerHTML = '<span class="text-danger">' + radarEscape(error.message || 'Unable to load schedule.') + '</span>';
    });
}

function radarSaveSchedule(runNow) {
    var enabled = document.getElementById('radar-schedule-enabled');
    var unit = document.getElementById('radar-schedule-unit');
    var value = document.getElementById('radar-schedule-value');
    var body = {
        enabled: !!(enabled && enabled.checked),
        interval_unit: unit ? unit.value : 'hours',
        interval_value: value ? (parseInt(value.value, 10) || 1) : 1,
        run_now: !!runNow
    };
    radarAgentPost('/radar/schedule', body).then(function (state) {
        radarRenderSchedule(state);
        document.getElementById('radar-scan-status').textContent = runNow ? 'Schedule saved and run queued.' : 'Schedule saved.';
        if (runNow) {
            setTimeout(radarLoadSchedule, 3000);
        }
    }).catch(function (error) {
        document.getElementById('radar-scan-status').textContent = error.message || 'Unable to save schedule.';
    });
}

function radarAction(serverUuid, findingId, action) {
    radarAgentPost('/radar/file/action', { server_uuid: serverUuid, finding_id: findingId, action: action }).then(function () {
        radarLoadServers();
        radarLoadFindings();
        radarLoadAlerts();
    }).catch(function (error) {
        document.getElementById('radar-scan-status').textContent = error.message || 'Unable to apply Radar action.';
    });
}

function radarFmtKB(kb) {
    kb = +kb || 0;
    if (kb < 1024) return kb.toFixed(1) + ' KB/s';
    return (kb / 1024).toFixed(2) + ' MB/s';
}

function radarFmtTime(ms) {
    var d = new Date(ms);
    return d.getHours().toString().padStart(2, '0') + ':' +
        d.getMinutes().toString().padStart(2, '0') + ':' +
        d.getSeconds().toString().padStart(2, '0');
}

function radarMakeDataset(label, color, fill) {
    return {
        label: label,
        data: [],
        borderColor: color,
        backgroundColor: fill || 'transparent',
        borderWidth: 2,
        pointRadius: 0,
        tension: 0.3,
        fill: !!fill
    };
}

function radarInitLineChart(canvasId, datasets) {
    return new Chart(document.getElementById(canvasId).getContext('2d'), {
        type: 'line',
        data: { labels: [], datasets: datasets },
        options: {
            animation: false,
            responsive: true,
            plugins: { legend: { display: true, labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                x: { display: true, ticks: { maxTicksLimit: 10, font: { size: 9 }, maxRotation: 0 } },
                y: { beginAtZero: true, ticks: { font: { size: 11 } } }
            }
        }
    });
}

function radarInitCharts() {
    if (radarChartTraffic || typeof Chart === 'undefined') return;
    radarChartTraffic = radarInitLineChart('radar-chart-traffic', [
        radarMakeDataset('↓ Recv KB/s', '#00c0ef', 'rgba(0,192,239,0.1)'),
        radarMakeDataset('↑ Send KB/s', '#00a65a', 'rgba(0,166,90,0.1)')
    ]);
    radarChartPackets = radarInitLineChart('radar-chart-packets', [
        radarMakeDataset('↓ Pkts/s', '#3c8dbc', 'rgba(60,141,188,0.1)'),
        radarMakeDataset('↑ Pkts/s', '#f39c12', 'rgba(243,156,18,0.1)')
    ]);
    radarChartConns = radarInitLineChart('radar-chart-conns', [
        radarMakeDataset('Incoming', '#f39c12', 'rgba(243,156,18,0.1)'),
        radarMakeDataset('Outgoing', '#00c0ef', 'rgba(0,192,239,0.1)')
    ]);
}

function radarAddHistory(point) {
    radarHistoryBuffer.push(point);
    if (radarHistoryBuffer.length > 50000) radarHistoryBuffer.shift();
}

function radarApplyTimeframe(minutes) {
    radarActiveMinutes = minutes;
    document.querySelectorAll('#radar-tf-btngroup [data-tf]').forEach(function (btn) {
        var tf = parseInt(btn.getAttribute('data-tf'), 10);
        btn.className = 'btn btn-xs ' + (tf === minutes ? 'btn-primary' : 'btn-default');
    });
    radarRedrawLineCharts();
}

function radarRedrawLineCharts() {
    radarInitCharts();
    if (!radarChartTraffic || !radarChartPackets || !radarChartConns) return;
    var cutoff = Date.now() - (radarActiveMinutes * 60000);
    var points = radarHistoryBuffer.filter(function (point) { return point.at >= cutoff; });
    var labels = points.map(function (point) { return radarFmtTime(point.at); });
    var safeMax = function (values) { return values.length ? Math.max.apply(null, values) : 0; };
    var last = points.length ? points[points.length - 1] : null;

    radarChartTraffic.data.labels = labels;
    radarChartTraffic.data.datasets[0].data = points.map(function (point) { return point.recvKB; });
    radarChartTraffic.data.datasets[1].data = points.map(function (point) { return point.sentKB; });
    radarChartTraffic.update('none');

    radarChartPackets.data.labels = labels;
    radarChartPackets.data.datasets[0].data = points.map(function (point) { return point.pktsIn; });
    radarChartPackets.data.datasets[1].data = points.map(function (point) { return point.pktsOut; });
    radarChartPackets.update('none');

    radarChartConns.data.labels = labels;
    radarChartConns.data.datasets[0].data = points.map(function (point) { return point.connIn || 0; });
    radarChartConns.data.datasets[1].data = points.map(function (point) { return point.connOut || 0; });
    radarChartConns.update('none');

    var recv = points.map(function (point) { return point.recvKB || 0; });
    var sent = points.map(function (point) { return point.sentKB || 0; });
    var pktsIn = points.map(function (point) { return point.pktsIn || 0; });
    var pktsOut = points.map(function (point) { return point.pktsOut || 0; });
    var connIn = points.map(function (point) { return point.connIn || 0; });
    var connOut = points.map(function (point) { return point.connOut || 0; });

    document.getElementById('radar-traf-in-cur').textContent = last ? radarFmtKB(last.recvKB) : '—';
    document.getElementById('radar-traf-out-cur').textContent = last ? radarFmtKB(last.sentKB) : '—';
    document.getElementById('radar-traf-in-peak').textContent = radarFmtKB(safeMax(recv));
    document.getElementById('radar-traf-out-peak').textContent = radarFmtKB(safeMax(sent));
    document.getElementById('radar-pkt-in-cur').textContent = last ? (last.pktsIn || 0).toFixed(1) + '/s' : '—';
    document.getElementById('radar-pkt-out-cur').textContent = last ? (last.pktsOut || 0).toFixed(1) + '/s' : '—';
    document.getElementById('radar-pkt-in-peak').textContent = safeMax(pktsIn).toFixed(1) + '/s';
    document.getElementById('radar-pkt-out-peak').textContent = safeMax(pktsOut).toFixed(1) + '/s';
    document.getElementById('radar-conn-in-cur').textContent = last ? (last.connIn || 0) : '—';
    document.getElementById('radar-conn-out-cur').textContent = last ? (last.connOut || 0) : '—';
    document.getElementById('radar-conn-in-peak').textContent = safeMax(connIn).toString();
    document.getElementById('radar-conn-out-peak').textContent = safeMax(connOut).toString();
}

function radarParseHistoryAt(value) {
    return new Date(String(value || '').replace(/(\.\d{3})\d+/, '$1')).getTime();
}

function radarLoadNodeHistory() {
    var statusEl = document.getElementById('radar-hist-load-status');
    if (statusEl) statusEl.textContent = 'Loading history…';
    radarAgentGet('/history').then(function (points) {
        radarHistoryRetries = 0;
        if (!Array.isArray(points) || points.length < 2) {
            if (statusEl) statusEl.textContent = 'No history yet';
            return;
        }
        var intervalSeconds = 10;
        points.forEach(function (point, index) {
            if (index === 0) return;
            var prev = points[index - 1] || {};
            var connections = point.connections || {};
            var at = radarParseHistoryAt(point.at);
            if (!Number.isFinite(at)) return;
            radarAddHistory({
                at: at,
                recvKB: Math.max(0, (point.net_recv_bytes || 0) - (prev.net_recv_bytes || 0)) / 1024 / intervalSeconds,
                sentKB: Math.max(0, (point.net_sent_bytes || 0) - (prev.net_sent_bytes || 0)) / 1024 / intervalSeconds,
                pktsIn: Math.max(0, (point.net_recv_pkts || 0) - (prev.net_recv_pkts || 0)) / intervalSeconds,
                pktsOut: Math.max(0, (point.net_sent_pkts || 0) - (prev.net_sent_pkts || 0)) / intervalSeconds,
                connIn: (connections.tcp_incoming || 0) + (connections.udp_incoming || 0),
                connOut: (connections.tcp_outgoing || 0) + (connections.udp_outgoing || 0)
            });
        });
        radarHistoryBuffer.sort(function (a, b) { return a.at - b.at; });
        if (statusEl) statusEl.textContent = (points.length - 1) + ' history points loaded';
        radarRedrawLineCharts();
    }).catch(function () {
        radarHistoryRetries++;
        if (radarHistoryRetries <= 3) {
            var delay = radarHistoryRetries * 3000;
            if (statusEl) statusEl.textContent = 'Retrying history (attempt ' + radarHistoryRetries + '/3)…';
            if (radarHistoryRetryTimer) clearTimeout(radarHistoryRetryTimer);
            radarHistoryRetryTimer = setTimeout(radarLoadNodeHistory, delay);
        } else if (statusEl) {
            statusEl.textContent = 'History unavailable';
        }
    });
}

function radarProtoRates(proto) {
    if (!proto) return { inRate: 0, outRate: 0 };
    var now = Date.now();
    var inNow = (proto.tcp_in_segs || 0) + (proto.udp_in_datagrams || 0) + (proto.icmp_in_msgs || 0);
    var outNow = (proto.tcp_out_segs || 0) + (proto.udp_out_datagrams || 0) + (proto.icmp_out_msgs || 0);
    var rates = { inRate: 0, outRate: 0 };
    if (radarPrevProto && radarPrevProtoAt) {
        var dt = Math.max(1, (now - radarPrevProtoAt) / 1000);
        rates.inRate = Math.max(0, inNow - radarPrevProto.inNow) / dt;
        rates.outRate = Math.max(0, outNow - radarPrevProto.outNow) / dt;
    }
    radarPrevProto = { inNow: inNow, outNow: outNow };
    radarPrevProtoAt = now;
    if (radarPeaks.pktsIn < rates.inRate) radarPeaks.pktsIn = rates.inRate;
    if (radarPeaks.pktsOut < rates.outRate) radarPeaks.pktsOut = rates.outRate;
    return rates;
}

function radarRenderNodeStats(data) {
    var recvKB = 0, sentKB = 0;
    (data.network || []).forEach(function (net) {
        recvKB += net.speed_recv_kb || 0;
        sentKB += net.speed_send_kb || 0;
    });
    if (radarPeaks.trafficIn < recvKB) radarPeaks.trafficIn = recvKB;
    if (radarPeaks.trafficOut < sentKB) radarPeaks.trafficOut = sentKB;
    var rates = radarProtoRates(data.proto || {});
    var connData = data.connections || {};
    radarAddHistory({
        at: Date.now(),
        recvKB: recvKB,
        sentKB: sentKB,
        pktsIn: rates.inRate,
        pktsOut: rates.outRate,
        connIn: (connData.tcp_incoming || 0) + (connData.udp_incoming || 0),
        connOut: (connData.tcp_outgoing || 0) + (connData.udp_outgoing || 0)
    });
    radarSetLiveStatus('Live', 'success', false);
    radarRedrawLineCharts();
}

function radarClearNodeReconnectTimer() {
    if (radarNodeWsReconnectTimer) {
        clearTimeout(radarNodeWsReconnectTimer);
        radarNodeWsReconnectTimer = null;
    }
}

function radarScheduleNodeReconnect(delay) {
    if (radarShuttingDown) return;
    radarClearNodeReconnectTimer();
    radarNodeWsReconnectTimer = setTimeout(function () {
        radarNodeWsReconnectTimer = null;
        if (radarShuttingDown) return;
        radarFetchTicket(true).then(radarConnectNodeWs).catch(function () {
            if (radarShuttingDown) return;
            radarSetLiveStatus('Retrying ticket', 'warning', true);
            radarScheduleNodeReconnect(5000);
        });
    }, delay);
}

function radarConnectNodeWs() {
    if (!radarAgentWsUrl || !radarAgentTicket || radarShuttingDown) return;
    radarClearNodeReconnectTimer();
    if (radarNodeWs) {
        radarNodeWs.onclose = null;
        radarNodeWs.close();
        radarNodeWs = null;
    }
    radarSetLiveStatus('Connecting', 'default', true);
    radarNodeWs = new WebSocket(radarAgentWsUrl + '?ticket=' + encodeURIComponent(radarAgentTicket));
    radarNodeWs.onopen = function () {
        if (radarShuttingDown) return;
        radarSetLiveStatus('Live', 'success', false);
    };
    radarNodeWs.onmessage = function (event) {
        if (radarShuttingDown) return;
        try { radarRenderNodeStats(JSON.parse(event.data)); } catch (err) {}
    };
    radarNodeWs.onerror = function () {
        if (radarShuttingDown) return;
        radarSetLiveStatus('WebSocket error', 'warning', false);
    };
    radarNodeWs.onclose = function () {
        if (radarShuttingDown) return;
        radarNodeWs = null;
        radarSetLiveStatus('Reconnecting', 'warning', true);
        radarScheduleNodeReconnect(5000);
    };
}

function radarRenderNetwork(network) {
    if (!network || network.error) return;
    document.getElementById('radar-net-in').textContent = radarRate(network.bytes_in_per_second);
    document.getElementById('radar-net-out').textContent = radarRate(network.bytes_out_per_second);
    document.getElementById('radar-net-packets').textContent = Math.round(network.packets_in_per_second || 0) + ' in / ' + Math.round(network.packets_out_per_second || 0) + ' out pps';
    document.getElementById('radar-net-connections').textContent = (network.connections_in || 0) + ' in / ' + (network.connections_out || 0) + ' out';
    document.getElementById('radar-net-locks').textContent = (network.active_locks || []).length;
    document.getElementById('radar-net-processes').textContent = (network.suspicious_processes || []).length;
}

function radarNetworkWsUrl() {
    var wsBase = radarAgentBaseUrl.replace(/^https:\/\//, 'wss://').replace(/^http:\/\//, 'ws://');
    return wsBase + '/radar/network/ws?server_uuid=' + encodeURIComponent(radarSelectedServer) + '&ticket=' + encodeURIComponent(radarAgentTicket);
}

function radarConnectNetworkWs() {
    if (radarShuttingDown) return;
    if (radarNetworkReconnectTimer) {
        clearTimeout(radarNetworkReconnectTimer);
        radarNetworkReconnectTimer = null;
    }
    if (radarNetworkWs) {
        radarNetworkWs.onclose = null;
        radarNetworkWs.close();
        radarNetworkWs = null;
    }
    if (!radarSelectedServer || !radarAgentTicket || !radarAgentBaseUrl) return;
    radarNetworkWs = new WebSocket(radarNetworkWsUrl());
    radarNetworkWs.onmessage = function (event) {
        try { radarRenderNetwork(JSON.parse(event.data)); } catch (err) {}
    };
    radarNetworkWs.onclose = function () {
        if (radarShuttingDown) return;
        radarNetworkReconnectTimer = setTimeout(function () {
            radarFetchTicket(true).then(radarConnectNetworkWs).catch(function () {});
        }, 5000);
    };
}

window.radarLoadServers = radarLoadServers;
window.radarSelectServer = radarSelectServer;
window.radarPrevPage = radarPrevPage;
window.radarNextPage = radarNextPage;
window.radarServerGotoPage = radarServerGotoPage;
window.radarFindingsGotoPage = radarFindingsGotoPage;
window.radarAlertsGotoPage = radarAlertsGotoPage;
window.radarChangePerPage = radarChangePerPage;
window.radarChangeAlertsPerPage = radarChangeAlertsPerPage;
window.radarScan = radarScan;
window.radarScanAll = radarScanAll;
window.radarBaseline = radarBaseline;
window.radarScheduleUnitChanged = radarScheduleUnitChanged;
window.radarLoadSchedule = radarLoadSchedule;
window.radarSaveSchedule = radarSaveSchedule;
window.radarAction = radarAction;

function radarBindControls() {
    var serverSearch = document.getElementById('radar-server-search');
    var serverClear = document.getElementById('radar-server-search-clear');
    var findingsSearch = document.getElementById('radar-findings-search');
    var findingsClear = document.getElementById('radar-findings-search-clear');
    var alertsSearch = document.getElementById('radar-alerts-search');
    var alertsClear = document.getElementById('radar-alerts-search-clear');

    if (serverSearch) {
        serverSearch.addEventListener('input', function () {
            radarServerSearch = String(serverSearch.value || '').trim().toLowerCase();
            radarServerPage = 1;
            radarRenderServerRows();
        });
    }
    if (serverClear) {
        serverClear.addEventListener('click', function (event) {
            event.preventDefault();
            if (serverSearch) serverSearch.value = '';
            radarServerSearch = '';
            radarServerPage = 1;
            radarRenderServerRows();
        });
    }
    if (findingsSearch) {
        findingsSearch.addEventListener('input', function () {
            radarFindingsSearch = String(findingsSearch.value || '').trim();
            radarFindingsPage = 1;
            radarLoadFindings();
        });
    }
    if (findingsClear) {
        findingsClear.addEventListener('click', function (event) {
            event.preventDefault();
            if (findingsSearch) findingsSearch.value = '';
            radarFindingsSearch = '';
            radarFindingsPage = 1;
            radarLoadFindings();
        });
    }
    if (alertsSearch) {
        alertsSearch.addEventListener('input', function () {
            radarAlertsSearch = String(alertsSearch.value || '').trim();
            radarAlertsPage = 1;
            radarLoadAlerts();
        });
    }
    if (alertsClear) {
        alertsClear.addEventListener('click', function (event) {
            event.preventDefault();
            if (alertsSearch) alertsSearch.value = '';
            radarAlertsSearch = '';
            radarAlertsPage = 1;
            radarLoadAlerts();
        });
    }
    document.querySelectorAll('#radar-tf-btngroup [data-tf]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            radarApplyTimeframe(parseInt(btn.getAttribute('data-tf'), 10));
        });
    });
}

function radarInitialize() {
    if (radarInitialized) return;
    radarInitialized = true;
    radarInitCharts();
    radarBindControls();
    radarApplyTimeframe(radarActiveMinutes);
    radarFetchTicket(false).then(function () {
        radarLoadServers();
        radarLoadSchedule();
        radarLoadAlerts();
        radarLoadNodeHistory();
        radarConnectNodeWs();
        radarAlertsRefreshTimer = setInterval(function () {
            if (!radarShuttingDown) radarLoadAlerts();
        }, 10000);
    }).catch(function (error) {
        document.getElementById('radar-server-list').innerHTML = '<tr><td colspan="7" class="text-danger text-center">' + radarEscape(error.message || 'Failed to connect to Wings Agent.') + '</td></tr>';
    });
}

document.addEventListener('DOMContentLoaded', radarInitialize);

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(radarInitialize, 0);
}

window.addEventListener('beforeunload', function () {
    radarShuttingDown = true;
    if (radarTicketRefreshTimer) clearTimeout(radarTicketRefreshTimer);
    radarClearNodeReconnectTimer();
    if (radarNetworkReconnectTimer) clearTimeout(radarNetworkReconnectTimer);
    if (radarHistoryRetryTimer) clearTimeout(radarHistoryRetryTimer);
    if (radarAlertsRefreshTimer) clearInterval(radarAlertsRefreshTimer);
    if (radarNodeWs) radarNodeWs.close();
    if (radarNetworkWs) radarNetworkWs.close();
});

})();
</script>
@endsection
