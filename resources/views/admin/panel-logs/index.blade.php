@extends('layouts.admin')

@section('title')
    Panel Logs
@endsection

@section('content-header')
    <h1>Panel Logs<small>Live viewer for Nginx, Laravel and Pterodactyl log files.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Panel Logs</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12">
        <div class="box box-default" id="source-browser-box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-folder-open-o"></i> Available Log Files</h3>
                <div class="box-tools pull-right">
                    <button id="btn-reload-sources" class="btn btn-default btn-xs" title="Refresh log file list">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body" style="padding:0;" id="source-table-wrap">
                <div id="source-loading" style="padding:24px; text-align:center; color:#999;">
                    <i class="fa fa-circle-o-notch fa-spin"></i> Loading log sources…
                </div>
                <table class="table table-hover table-condensed" id="source-table" style="display:none; margin:0;">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th>Log File</th>
                            <th>Group</th>
                            <th style="text-align:right;">Size</th>
                            <th>Last Modified</th>
                            <th style="width:100px;"></th>
                        </tr>
                    </thead>
                    <tbody id="source-tbody"></tbody>
                </table>
                <div id="source-empty" style="display:none; padding:24px; text-align:center; color:#999;">
                    <i class="fa fa-exclamation-circle"></i> No log files found.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="viewer-row" style="display:none;">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border" style="background:#3c8dbc; color:#fff;">
                <h3 class="box-title" style="color:#fff;">
                    <i class="fa fa-file-text-o"></i>
                    <span id="viewer-title">Log Viewer</span>
                </h3>
                <div class="box-tools pull-right" style="display:flex; align-items:center; gap:8px;">
                    <div class="btn-group btn-group-sm" id="mode-group">
                        <button id="btn-live" class="btn btn-success btn-sm" title="Live — stream log in real time">
                            <i class="fa fa-play-circle"></i> Live
                        </button>
                        <button id="btn-history" class="btn btn-default btn-sm" title="History — view last N lines">
                            <i class="fa fa-history"></i> History
                        </button>
                    </div>

                    <select id="history-lines-select" class="form-control input-sm" style="width:110px; display:none;"
                            title="Lines to load">
                        <option value="50">50 lines</option>
                        <option value="100" selected>100 lines</option>
                        <option value="200">200 lines</option>
                        <option value="500">500 lines</option>
                        <option value="1000">1000 lines</option>
                        <option value="2000">2000 lines</option>
                    </select>

                    <button id="btn-refresh" class="btn btn-warning btn-sm" title="Refresh / Reconnect">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>

                    <button id="btn-clear" class="btn btn-default btn-sm" title="Clear output">
                        <i class="fa fa-trash-o"></i> Clear
                    </button>

                    <label style="color:#fff; font-weight:normal; margin:0; cursor:pointer;" title="Toggle auto-scroll">
                        <input type="checkbox" id="autoscroll-check" checked style="margin-right:4px;">
                        Auto-scroll
                    </label>

                    <span id="log-status-badge" class="label label-default" style="font-size:12px; padding:4px 10px;">
                        <i class="fa fa-circle-o-notch fa-spin"></i> Connecting…
                    </span>

                    <button id="btn-close-viewer" class="btn btn-default btn-sm" title="Close viewer">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="box-body" style="padding:0;">
                <pre id="log-output"
                     style="background:#0d1117; color:#c9d1d9;
                            font-size:12px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;
                            margin:0; padding:12px 16px; overflow-y:auto; height:580px;
                            border:none; border-radius:0 0 4px 4px;
                            white-space:pre-wrap; word-break:break-all;"></pre>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-default" style="font-size:12px;">
            <div class="box-body" style="padding:10px 16px; color:#666;">
                <i class="fa fa-info-circle text-info"></i>&nbsp;
                <strong>Live mode</strong> follows the log in real time using <code>tail -F</code>.&nbsp;
                <strong>History mode</strong> loads the last N lines on demand.&nbsp;
                Click <strong>Refresh</strong> to reconnect a stalled stream or reload history.&nbsp;
                Logs directories scanned: <code>/var/log/nginx</code>, <code>storage/logs</code>, <code>/var/log/pterodactyl</code>.
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer-scripts')
@parent
<script>
(function () {
    'use strict';

    var listUrl    = '{{ route('admin.panel-logs.list') }}';
    var historyUrl = '{{ route('admin.panel-logs.history') }}';
    var streamUrl  = '{{ route('admin.panel-logs.stream') }}';

    var MAX_LINES = 3000;

    var currentMode   = 'live';
    var currentLogId  = null;
    var currentLabel  = '';
    var sse           = null;
    var lineCount     = 0;

    var elOutput        = document.getElementById('log-output');
    var elViewerRow     = document.getElementById('viewer-row');
    var elViewerTitle   = document.getElementById('viewer-title');
    var elBtnLive       = document.getElementById('btn-live');
    var elBtnHistory    = document.getElementById('btn-history');
    var elBtnRefresh    = document.getElementById('btn-refresh');
    var elBtnClear      = document.getElementById('btn-clear');
    var elBtnClose      = document.getElementById('btn-close-viewer');
    var elLinesSelect   = document.getElementById('history-lines-select');
    var elAutoscroll    = document.getElementById('autoscroll-check');
    var elStatusBadge   = document.getElementById('log-status-badge');
    var elSourceLoading = document.getElementById('source-loading');
    var elSourceTable   = document.getElementById('source-table');
    var elSourceTbody   = document.getElementById('source-tbody');
    var elSourceEmpty   = document.getElementById('source-empty');

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setStatus(html, type) {
        elStatusBadge.className = 'label label-' + (type || 'default');
        elStatusBadge.innerHTML = html;
    }

    function appendLine(line) {
        elOutput.innerHTML += escapeHtml(line) + '\n';
        lineCount++;
        if (lineCount > MAX_LINES + 300) {
            var text = elOutput.innerHTML;
            var excess = lineCount - MAX_LINES;
            var pos = 0;
            for (var i = 0; i < excess; i++) {
                var nl = text.indexOf('\n', pos);
                if (nl === -1) break;
                pos = nl + 1;
            }
            elOutput.innerHTML = text.slice(pos);
            lineCount = MAX_LINES;
        }
        if (elAutoscroll.checked) {
            elOutput.scrollTop = elOutput.scrollHeight;
        }
    }

    function clearOutput() {
        elOutput.innerHTML = '';
        lineCount = 0;
    }

    function showViewer(label) {
        elViewerTitle.textContent = label;
        elViewerRow.style.display = '';
        setTimeout(function () {
            elViewerRow.scrollIntoView({ behavior: 'smooth' });
        }, 80);
    }

    function stopSSE() {
        if (sse) { sse.close(); sse = null; }
    }

    function loadSources() {
        elSourceLoading.style.display = '';
        elSourceTable.style.display = 'none';
        elSourceEmpty.style.display = 'none';

        fetch(listUrl)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var logs = data.logs || [];
                elSourceLoading.style.display = 'none';

                if (logs.length === 0) {
                    elSourceEmpty.style.display = '';
                    return;
                }

                var groups = {};
                logs.forEach(function (l) {
                    if (!groups[l.group]) groups[l.group] = [];
                    groups[l.group].push(l);
                });

                var html = '';
                Object.keys(groups).forEach(function (g) {
                    html += '<tr style="background:#f5f5f5;"><td colspan="6" style="font-weight:600; color:#555; padding:6px 10px; font-size:11px; text-transform:uppercase; letter-spacing:.5px;">' + escapeHtml(g) + '</td></tr>';
                    groups[g].forEach(function (l) {
                        var isActive = l.id === currentLogId;
                        html += '<tr class="' + (isActive ? 'info' : '') + '" data-log-id="' + escapeHtml(l.id) + '" data-log-label="' + escapeHtml(l.label) + '" style="cursor:pointer;">';
                        html += '<td style="text-align:center; color:#3c8dbc;"><i class="fa fa-file-text-o"></i></td>';
                        html += '<td><code style="font-size:12px;">' + escapeHtml(l.name) + '</code></td>';
                        html += '<td><span class="label label-default" style="font-size:11px;">' + escapeHtml(l.group) + '</span></td>';
                        html += '<td style="text-align:right; color:#888; font-size:12px;">' + escapeHtml(l.size_fmt) + '</td>';
                        html += '<td style="color:#888; font-size:12px;">' + escapeHtml(l.mtime_fmt) + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-primary btn-xs btn-open-live" data-id="' + escapeHtml(l.id) + '" data-label="' + escapeHtml(l.label) + '" title="Live stream"><i class="fa fa-play"></i> Live</button> ';
                        html += '<button class="btn btn-default btn-xs btn-open-history" data-id="' + escapeHtml(l.id) + '" data-label="' + escapeHtml(l.label) + '" title="View history"><i class="fa fa-history"></i> History</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                });

                elSourceTbody.innerHTML = html;
                elSourceTable.style.display = '';

                elSourceTbody.querySelectorAll('.btn-open-live').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        openLog(btn.dataset.id, btn.dataset.label, 'live');
                    });
                });
                elSourceTbody.querySelectorAll('.btn-open-history').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        openLog(btn.dataset.id, btn.dataset.label, 'history');
                    });
                });
                elSourceTbody.querySelectorAll('tr[data-log-id]').forEach(function (row) {
                    row.addEventListener('click', function () {
                        openLog(row.dataset.logId, row.dataset.logLabel, 'live');
                    });
                });
            })
            .catch(function () {
                elSourceLoading.innerHTML = '<i class="fa fa-exclamation-triangle text-danger"></i> Failed to load log file list.';
            });
    }

    function openLog(id, label, mode) {
        currentLogId = id;
        currentLabel = label;

        if (mode === 'live') {
            setMode('live', false);
        } else {
            setMode('history', false);
        }

        showViewer(label);
        startViewer();
    }

    function setMode(mode, restart) {
        currentMode = mode;
        if (mode === 'live') {
            elBtnLive.classList.add('active');
            elBtnLive.className = elBtnLive.className.replace('btn-default', 'btn-success');
            elBtnHistory.classList.remove('active');
            elBtnHistory.className = elBtnHistory.className.replace('btn-success', 'btn-default');
            elLinesSelect.style.display = 'none';
        } else {
            elBtnHistory.classList.add('active');
            elBtnHistory.className = elBtnHistory.className.replace('btn-default', 'btn-success');
            elBtnLive.classList.remove('active');
            elBtnLive.className = elBtnLive.className.replace('btn-success', 'btn-default');
            elLinesSelect.style.display = '';
        }
        if (restart) startViewer();
    }

    function startViewer() {
        if (!currentLogId) return;
        stopSSE();
        clearOutput();
        if (currentMode === 'live') {
            startLive(currentLogId, 50);
        } else {
            loadHistory(currentLogId, parseInt(elLinesSelect.value) || 100);
        }
    }

    function startLive(id, lines) {
        setStatus('<i class="fa fa-circle-o-notch fa-spin"></i> Connecting…', 'default');
        var url = streamUrl + '?log=' + encodeURIComponent(id) + '&lines=' + encodeURIComponent(lines);
        sse = new EventSource(url);

        sse.addEventListener('ready', function () {
            setStatus('<i class="fa fa-circle" style="color:#4CAF50"></i> Live', 'success');
        });

        sse.onmessage = function (e) {
            appendLine(e.data);
        };

        sse.addEventListener('end', function () {
            setStatus('<i class="fa fa-stop-circle"></i> Stream ended', 'warning');
            stopSSE();
        });

        sse.addEventListener('error', function (e) {
            appendLine('[ERROR] ' + (e.data || 'log stream error'));
            setStatus('<i class="fa fa-exclamation-triangle"></i> Error', 'danger');
            stopSSE();
        });

        sse.onerror = function () {
            if (sse && sse.readyState === EventSource.CLOSED) {
                setStatus('<i class="fa fa-times-circle"></i> Disconnected', 'danger');
            }
        };
    }

    function loadHistory(id, lines) {
        setStatus('<i class="fa fa-circle-o-notch fa-spin"></i> Loading…', 'default');
        var url = historyUrl + '?log=' + encodeURIComponent(id) + '&lines=' + encodeURIComponent(lines);

        fetch(url)
            .then(function (r) {
                if (!r.ok) return r.json().then(function (d) { throw new Error(d.error || r.statusText); });
                return r.json();
            })
            .then(function (data) {
                var ls = data.lines || [];
                if (ls.length === 0) {
                    appendLine('(no log lines returned)');
                } else {
                    ls.forEach(function (l) { appendLine(l); });
                }
                setStatus('<i class="fa fa-check-circle"></i> Loaded ' + ls.length + ' lines', 'success');
            })
            .catch(function (err) {
                appendLine('[ERROR] ' + err.message);
                setStatus('<i class="fa fa-exclamation-triangle"></i> Error', 'danger');
            });
    }

    elBtnLive.addEventListener('click', function () { setMode('live', true); });
    elBtnHistory.addEventListener('click', function () { setMode('history', true); });
    elBtnRefresh.addEventListener('click', function () { startViewer(); });
    elBtnClear.addEventListener('click', function () { clearOutput(); });
    elBtnClose.addEventListener('click', function () {
        stopSSE();
        elViewerRow.style.display = 'none';
        currentLogId = null;
    });
    elLinesSelect.addEventListener('change', function () {
        if (currentMode === 'history' && currentLogId) {
            loadHistory(currentLogId, parseInt(elLinesSelect.value) || 100);
        }
    });
    document.getElementById('btn-reload-sources').addEventListener('click', loadSources);

    loadSources();
    window.addEventListener('beforeunload', function () { stopSSE(); });
}());
</script>
@endsection
