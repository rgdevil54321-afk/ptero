@extends('layouts.admin')

@section('title')
    Local Servers
@endsection

@section('content-header')
    <h1>Local Servers<small>Run Minecraft natively on this host &mdash; no Wings, no Docker, no FQDN.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Local Servers</li>
    </ol>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @unless($preflight['ok'])
        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> This host is not ready</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Fix the checks below, then reload. The installer (<code>turtle/install.sh</code>) sets all of this up for you.</p>
                        <table class="table table-condensed">
                            @foreach($preflight['checks'] as $check)
                                <tr>
                                    <td style="width:24px;">
                                        @if($check['ok'])
                                            <i class="fa fa-check text-green"></i>
                                        @else
                                            <i class="fa fa-times text-red"></i>
                                        @endif
                                    </td>
                                    <td style="width:180px;"><strong>{{ $check['name'] }}</strong></td>
                                    <td class="text-muted">{{ $check['detail'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endunless

    <div class="row">
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Create a local server</h3>
                </div>
                <form action="{{ route('admin.local.create') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="My Survival Server" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" @if(old('type', 'paper') === $key) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Minecraft version <small class="text-muted">(blank = latest)</small></label>
                            <input type="text" name="mc_version" class="form-control" value="{{ old('mc_version') }}" placeholder="e.g. 1.21.1">
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Memory (MB)</label>
                                    <input type="number" name="memory_mb" class="form-control" value="{{ old('memory_mb', 2048) }}" min="512" max="65536" required>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Port</label>
                                    <input type="number" name="port" class="form-control" value="{{ old('port', $suggestedPort) }}" min="1" max="65535" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>MOTD <small class="text-muted">(optional)</small></label>
                            <input type="text" name="motd" class="form-control" value="{{ old('motd') }}">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="playit" value="1" @if(old('playit', true)) checked @endif>
                                Give it a public playit.gg address
                            </label>
                        </div>
                        <p class="text-muted small">
                            Files live in <code>{{ $baseDir }}/&lt;id&gt;</code>. The panel downloads the jar and
                            starts the process for you.
                        </p>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Create &amp; install</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Servers</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Port</th>
                            <th>Status</th>
                            <th>playit</th>
                            <th></th>
                        </tr>
                        @forelse($servers as $server)
                            <tr>
                                <td>{{ $server->id }}</td>
                                <td>{{ $server->name }}</td>
                                <td>{{ $server->type }} @if($server->mc_version)<small class="text-muted">{{ $server->mc_version }}</small>@endif</td>
                                <td>{{ $server->port }}</td>
                                <td>
                                    @if($server->status === 'running')
                                        <span class="label label-success">running</span>
                                    @elseif($server->status === 'error')
                                        <span class="label label-danger">error</span>
                                    @elseif($server->status === 'installing')
                                        <span class="label label-warning">installing</span>
                                    @else
                                        <span class="label label-default">{{ $server->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($server->playit_address)
                                        <code>{{ $server->playit_address }}</code>
                                    @else
                                        <span class="text-muted">&ndash;</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.local.show', $server->id) }}" class="btn btn-xs btn-default">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No local servers yet.</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
