@extends('layouts.admin')

@section('title')
    Nests &rarr; {{ $nest->name }}
@endsection

@section('content-header')
    <h1>{{ $nest->name }}<small>{{ str_limit($nest->description, 50) }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.nests') }}">Nests</a></li>
        <li class="active">{{ $nest->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <form action="{{ route('admin.nests.view', $nest->id) }}" method="POST">
        <div class="col-md-6">
            <div class="box">
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label">Name <span class="field-required"></span></label>
                        <div>
                            <input type="text" name="name" class="form-control" value="{{ $nest->name }}" />
                            <p class="text-muted"><small>This should be a descriptive category name that encompasses all of the options within the service.</small></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <div>
                            <textarea name="description" class="form-control" rows="7">{{ $nest->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.update'))
                    <button type="submit" name="_method" value="PATCH" class="btn btn-primary btn-sm pull-right">Save</button>
                    @endif
                    @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.delete'))
                    <button id="deleteButton" type="submit" name="_method" value="DELETE" class="btn btn-sm btn-danger muted muted-hover"><i class="fa fa-trash-o"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </form>
    <div class="col-md-6">
        <div class="box">
            <div class="box-body">
                <div class="form-group">
                    <label class="control-label">Nest ID</label>
                    <div>
                        <input type="text" readonly class="form-control" value="{{ $nest->id }}" />
                        <p class="text-muted small">A unique ID used for identification of this nest internally and through the API.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">Author</label>
                    <div>
                        <input type="text" readonly class="form-control" value="{{ $nest->author }}" />
                        <p class="text-muted small">The author of this service option. Please direct questions and issues to them unless this is an official option authored by <code>support@pterodactyl.io</code>.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">UUID</label>
                    <div>
                        <input type="text" readonly class="form-control" value="{{ $nest->uuid }}" />
                        <p class="text-muted small">A UUID that all servers using this option are assigned for identification purposes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Nest Eggs</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Servers</th>
                        <th class="text-center"></th>
                    </tr>
                    @foreach($nest->eggs as $egg)
                        <tr>
                            <td class="align-middle"><code>{{ $egg->id }}</code></td>
                            <td class="align-middle"><a href="{{ route('admin.nests.egg.view', $egg->id) }}" data-toggle="tooltip" data-placement="right" title="{{ $egg->author }}">{{ $egg->name }}</a></td>
                            <td class="col-xs-8 align-middle">{{ $egg->description }}</td>
                            <td class="text-center align-middle"><code>{{ $egg->servers->count() }}</code></td>
                            <td class="align-middle">
                                @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.egg.export'))
                                    <a href="{{ route('admin.nests.egg.export', ['egg' => $egg->id]) }}"><i class="fa fa-download"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="box-footer">
                @if(Auth::user()->root_admin || Auth::user()->hasAdminPermission('admin.nests.eggs.create'))
                <button id="importEggsButton" class="btn btn-info btn-sm pull-right text-white" type="button">Import from eggs.pterodactyl.io</button>
                <a href="{{ route('admin.nests.egg.new') }}"><button class="btn btn-success btn-sm pull-right text-white" type="button">New Egg</button></a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#deleteButton').on('mouseenter', function (event) {
            $(this).find('i').html(' Delete Nest');
        }).on('mouseleave', function (event) {
            $(this).find('i').html('');
        });

        const remoteEggs = {
            list: null,
            loading: false,
        };

        const fetchRemoteEggs = async () => {
            if (remoteEggs.list) {
                return remoteEggs.list;
            }

            remoteEggs.loading = true;
            const $alert = $('#importEggsModal .modal-alert');
            $alert.hide();
            $('#importEggsModal .egg-list').html('<tr><td colspan="3" class="text-center">Loading…</td></tr>');

            try {
                const response = await fetch('{{ route('admin.nests.egg.remote.index') }}', { credentials: 'same-origin' });
                if (!response.ok) {
                    throw new Error('Failed to load available eggs.');
                }
                const json = await response.json();
                remoteEggs.list = json.data || [];
            } catch (error) {
                $alert.removeClass('alert-success').addClass('alert-danger').text(error.message).show();
                $('#importEggsModal .egg-list').html('<tr><td colspan="3" class="text-center">Unable to load eggs.</td></tr>');
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

            $('#importEggsModal .egg-list').html(rows.join(''));
        };

        const filterEggs = (term) => {
            const filtered = (remoteEggs.list || []).filter(egg => {
                const needle = term.toLowerCase();
                return egg.name.toLowerCase().includes(needle) || egg.slug.toLowerCase().includes(needle) || (egg.description || '').toLowerCase().includes(needle);
            });
            renderEggTable(filtered);
        };

        $('#importEggsButton').on('click', async function () {
            $('#importEggsModal').modal('show');

            try {
                const eggs = await fetchRemoteEggs();
                renderEggTable(eggs);
            } catch (e) {
            }
        });

        $('#importEggsModal').on('shown.bs.modal', function () {
            $('#eggSearch').trigger('focus');
        });

        $('#eggSearch').on('input', function () {
            filterEggs($(this).val());
        });

        $('#importEggsForm').on('submit', function (e) {
            const selected = [];
            $('#importEggsModal .egg-select:checked').each(function () {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                e.preventDefault();
                $('#importEggsModal .modal-alert').removeClass('alert-success').addClass('alert-danger').text('Select at least one egg to import.').show();
                return;
            }

            $('#importEggsForm input[name="eggs[]"]').remove();
            selected.forEach(function (slug) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'eggs[]',
                    value: slug,
                }).appendTo('#importEggsForm');
            });

            $('#importEggsModal .btn-primary').prop('disabled', true).text('Importing…');
        });

        $('#importEggsModal').on('hidden.bs.modal', function () {
            $('#importEggsModal .btn-primary').prop('disabled', false).text('Import Selected');
            $('#importEggsModal .modal-alert').hide();
        });
    </script>

    <div class="modal fade" id="importEggsModal" tabindex="-1" role="dialog" aria-labelledby="importEggsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="importEggsForm" method="POST" action="{{ route('admin.nests.egg.remote.import') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importEggsModalLabel">Import Eggs from eggs.pterodactyl.io</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="nestSelect">Target Nest</label>
                            <select class="form-control" id="nestSelect" name="nest_id">
                                @foreach($nests as $n)
                                    <option value="{{ $n->id }}" @if($n->id === $nest->id) selected @endif>{{ $n->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Imports will create or update eggs in this nest.</small>
                        </div>

                        <div class="form-group">
                            <label for="eggSearch">Search eggs</label>
                            <input id="eggSearch" class="form-control" placeholder="Search by name or slug..." />
                        </div>
                        <div class="alert modal-alert alert-danger" style="display:none;"></div>
                        <div class="table-responsive" style="max-height: 380px; overflow: auto;">
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
@endsection
