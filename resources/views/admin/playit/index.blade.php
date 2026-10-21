@extends('layouts.admin')

@section('title')
    Playit
@endsection

@section('content-header')
    <h1>Playit<small>Give IPv6-only / NAT'd game servers a public address.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Playit</li>
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
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Node bridge</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        A Minecraft server installs the playit plugin automatically. The plugin creates a tunnel and
                        either reads an agent secret from the pool below, or shows a one-time claim link in the server
                        console. Run the bridge on each Wings node so the panel learns the connectable address:
                    </p>
                    <pre>sudo bash turtle/install_playit.sh --panel {{ $panelUrl }} --token {{ $token }}</pre>
                    <dl class="dl-horizontal">
                        <dt>Panel URL</dt>
                        <dd><code>{{ $panelUrl }}</code></dd>
                        <dt>Report token</dt>
                        <dd><code>{{ $token }}</code></dd>
                        <dt>Pool keys free</dt>
                        <dd>{{ $poolCount }}</dd>
                    </dl>
                    <form action="{{ route('admin.playit.token') }}" method="POST" class="form-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-default">Rotate token</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add agent secret keys</h3>
                </div>
                <form action="{{ route('admin.playit.keys') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <p class="text-muted">
                            Create agents at <a href="https://playit.gg" target="_blank" rel="noopener">playit.gg</a>
                            and paste their secret keys here (one per line). One key is handed to one server, so each
                            server gets its own public address.
                        </p>
                        <div class="form-group">
                            <label for="label">Label (optional)</label>
                            <input type="text" class="form-control" id="label" name="label" placeholder="e.g. playit free tier">
                        </div>
                        <div class="form-group">
                            <label for="secrets">Secret keys</label>
                            <textarea class="form-control" id="secrets" name="secrets" rows="6" placeholder="one key per line"></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Add keys</button>
                    </div>
                </form>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Assign a key to a server</h3>
                </div>
                <form action="{{ route('admin.playit.assign') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="agent_id">Key</label>
                            <select class="form-control" id="agent_id" name="agent_id">
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">
                                        #{{ $agent->id }} {{ $agent->label ?: 'key' }} &mdash; {{ $agent->status }}
                                        @if($agent->host) ({{ $agent->host }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="server_uuid">Server</label>
                            <select class="form-control" id="server_uuid" name="server_uuid">
                                @foreach($servers as $server)
                                    <option value="{{ $server->uuid }}">
                                        #{{ $server->id }} {{ $server->name }} ({{ $server->egg_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Keys</h3>
                    <div class="box-tools">
                        <form action="{{ route('admin.playit.sync') }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">Sync now</button>
                        </form>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Server</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        @forelse($agents as $agent)
                            <tr>
                                <td>{{ $agent->id }}</td>
                                <td>{{ $agent->label ?: '—' }}</td>
                                <td>
                                    @if($agent->server_uuid)
                                        <code>{{ $agent->server_uuid }}</code>
                                    @elseif($agent->local_server_id)
                                        <span class="label label-info">local #{{ $agent->local_server_id }}</span>
                                    @else
                                        <span class="text-muted">pool</span>
                                    @endif
                                </td>
                                <td>
                                    @if($agent->address)
                                        <code>{{ $agent->address }}</code>
                                    @else
                                        <span class="text-muted">{{ $agent->last_error ?: '—' }}</span>
                                    @endif
                                </td>
                                <td>{{ $agent->status }}</td>
                                <td class="text-right">
                                    @if($agent->server_uuid || $agent->local_server_id)
                                        <form action="{{ route('admin.playit.release') }}" method="POST" style="display:inline">
                                            @csrf
                                            <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                                            <button type="submit" class="btn btn-xs btn-default">Release</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.playit.delete') }}" method="POST" style="display:inline">
                                        @csrf
                                        <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No keys yet.</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Resolved addresses</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>Server UUID</th>
                            <th>Address</th>
                            <th>Source</th>
                            <th>Updated</th>
                        </tr>
                        @forelse($links as $link)
                            <tr>
                                <td><code>{{ $link->server_uuid }}</code></td>
                                <td><code>{{ $link->address }}</code></td>
                                <td>{{ $link->source }}</td>
                                <td>{{ $link->updated_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No addresses resolved yet.</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
