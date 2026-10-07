@extends('layouts.admin')

@section('title')
    Nests
@endsection

@section('content-header')
    <h1>Nests<small>All nests currently available on this system.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Nests</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="alert alert-danger">
            Eggs are a powerful feature of Pterodactyl Panel that allow for extreme flexibility and configuration. Please note that while powerful, modifying an egg wrongly can very easily brick your servers and cause more problems. Please avoid editing our default eggs — those provided by <code>support@pterodactyl.io</code> — unless you are absolutely sure of what you are doing.
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Configured Nests</h3>
                <div class="box-tools">
                    @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.eggs.create'))
                    <a href="#" class="btn btn-sm btn-info" id="openRemoteEggImport" role="button"><i class="fa fa-cloud-download"></i> Import from eggs.pterodactyl.io</a>
                    <a href="#" class="btn btn-sm btn-success" data-toggle="modal" data-target="#importServiceOptionModal" role="button"><i class="fa fa-upload"></i> Upload Egg</a>
                    @endif
                    @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.create'))
                    <a href="{{ route('admin.nests.new') }}" class="btn btn-primary btn-sm">Create New</a>
                    @endif
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Eggs</th>
                        <th class="text-center">Servers</th>
                    </tr>
                    @foreach($nests as $nest)
                        <tr>
                            <td class="middle"><code>{{ $nest->id }}</code></td>
                            <td class="middle"><a href="{{ route('admin.nests.view', $nest->id) }}" data-toggle="tooltip" data-placement="right" title="{{ $nest->author }}">{{ $nest->name }}</a></td>
                            <td class="col-xs-6 middle">{{ $nest->description }}</td>
                            <td class="text-center middle">{{ $nest->eggs_count }}</td>
                            <td class="text-center middle">{{ $nest->servers_count }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="importRemoteEggsModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="importRemoteEggsForm" method="POST" action="{{ route('admin.nests.egg.remote.import') }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Import Eggs from eggs.pterodactyl.io</h4>
                </div>
                <div class="modal-body">
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label for="nestSelect">Target Nest</label>
                        <select class="form-control" id="nestSelect" name="nest_id">
                            <option value="0">All Nests</option>
                            @foreach($nests as $nest)
                                <option value="{{ $nest->id }}">{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                            @endforeach
                        </select>
                        <p class="small text-muted">Select where imported eggs should be stored. Choose <strong>All Nests</strong> to import into every nest.</p>
                    </div>

                    <div class="form-group">
                        <label for="modeSelect">Existing Egg Behavior</label>
                        <select id="modeSelect" name="mode" class="form-control">
                            <option value="update">Update existing egg (if it exists)</option>
                            <option value="duplicate">Import as a new egg even if one exists</option>
                        </select>
                        <p class="small text-muted">If you choose to import as a new egg, the panel will append a unique suffix to the egg name when duplicates exist.</p>
                    </div>

                    <div class="form-group">
                        <label for="eggSearch">Search eggs</label>
                        <input id="eggSearch" class="form-control" placeholder="Search by name or slug..." />
                    </div>
                    <div class="alert modal-alert alert-danger" style="display:none;"></div>
                    <div class="table-responsive" style="max-height: 360px; overflow: auto;">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">&nbsp;</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody class="egg-list">
                                <tr><td colspan="3" class="text-center">Click "Import from eggs.pterodactyl.io" to load available eggs.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="importServiceOptionModal" tabindex="-1" role="dialog" aria-labelledby="importServiceOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="importServiceOptionForm" method="POST" action="{{ route('admin.nests.egg.import') }}" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title" id="importServiceOptionModalLabel">Upload Egg</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <div class="form-group">
                        <label for="importNestSelect">Target Nest</label>
                        <select class="form-control" id="importNestSelect" name="import_to_nest" required>
                            @foreach($nests as $nest)
                                <option value="{{ $nest->id }}">{{ $nest->name }} &lt;{{ $nest->author }}&gt;</option>
                            @endforeach
                        </select>
                        <p class="small text-muted">Select which nest the uploaded Egg should be added to.</p>
                    </div>
                    <div class="form-group">
                        <label for="importFile">Egg JSON File</label>
                        <input type="file" class="form-control" id="importFile" name="import_file" accept="application/json,.json,text/plain" required>
                        <p class="small text-muted">Upload a valid Pterodactyl Egg JSON file.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Upload Egg</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('#pImportToNest').select2();

            const remoteEggs = {
                list: null,
                loading: false,
            };

            const fetchRemoteEggs = async () => {
                if (remoteEggs.list) {
                    return remoteEggs.list;
                }

                remoteEggs.loading = true;
                const $alert = $('#importRemoteEggsModal .modal-alert');
                $alert.hide();
                $('#importRemoteEggsModal .egg-list').html('<tr><td colspan="3" class="text-center">Loading…</td></tr>');

                try {
                    const response = await fetch('{{ route('api.public.eggs') }}', { credentials: 'same-origin' });
                    if (!response.ok) {
                        throw new Error('Failed to load available eggs.');
                    }
                    const json = await response.json();
                    remoteEggs.list = json.data || [];
                } catch (error) {
                    $alert.removeClass('alert-success').addClass('alert-danger').text(error.message).show();
                    $('#importRemoteEggsModal .egg-list').html('<tr><td colspan="3" class="text-center">Unable to load eggs.</td></tr>');
                    throw error;
                } finally {
                    remoteEggs.loading = false;
                }

                return remoteEggs.list;
            };

            const renderEggTable = (eggs) => {
                const rows = eggs.map(egg => {
                    return `<tr data-slug="${egg.slug}">
                        <td class="text-center"><input type="checkbox" class="egg-select" value="${egg.slug}" /></td>
                        <td><strong>${egg.name}</strong><br/><small class="text-muted">${egg.slug}</small></td>
                        <td>${egg.description || ''}</td>
                    </tr>`;
                });

                $('#importRemoteEggsModal .egg-list').html(rows.join(''));
            };

            const filterEggs = (term) => {
                const filtered = (remoteEggs.list || []).filter(egg => {
                    const needle = term.toLowerCase();
                    return egg.name.toLowerCase().includes(needle) || egg.slug.toLowerCase().includes(needle) || (egg.description || '').toLowerCase().includes(needle);
                });
                renderEggTable(filtered);
            };

            $('#openRemoteEggImport').on('click', async function () {
                $('#importRemoteEggsModal').modal('show');

                try {
                    const eggs = await fetchRemoteEggs();
                    renderEggTable(eggs);
                } catch (e) {
                }
            });

            $('#importRemoteEggsModal').on('shown.bs.modal', function () {
                $('#eggSearch').trigger('focus');
            });

            $('#eggSearch').on('input', function () {
                filterEggs($(this).val());
            });

            $('#importRemoteEggsForm').on('submit', function (e) {
                const selected = [];
                $('#importRemoteEggsModal .egg-select:checked').each(function () {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    e.preventDefault();
                    $('#importRemoteEggsModal .modal-alert').removeClass('alert-success').addClass('alert-danger').text('Select at least one egg to import.').show();
                    return;
                }

                $('#importRemoteEggsForm input[name="eggs[]"]').remove();
                selected.forEach(function (slug) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'eggs[]',
                        value: slug,
                    }).appendTo('#importRemoteEggsForm');
                });

                $('#importRemoteEggsModal .btn-primary').prop('disabled', true).text('Importing…');
            });

            $('#importRemoteEggsModal').on('hidden.bs.modal', function () {
                $('#importRemoteEggsModal .btn-primary').prop('disabled', false).text('Import Selected');
                $('#importRemoteEggsModal .modal-alert').hide();
            });
        });
    </script>
@endsection
