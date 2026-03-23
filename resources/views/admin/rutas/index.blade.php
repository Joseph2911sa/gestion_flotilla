@extends('layouts.app')

@section('title', 'Rutas')
@section('page-title', 'Gestión de Rutas')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <small class="text-muted">Total: <strong>{{ $paginado['total'] }}</strong> ruta(s)</small>
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCrear">
        <i class="fas fa-plus mr-1"></i> Nueva Ruta
    </button>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-route mr-2"></i>Listado de Rutas</h3>
        <div class="card-tools">
            <span class="badge badge-secondary">
                Página {{ $paginado['current_page'] }} de {{ $paginado['last_page'] }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(count($rutas) === 0)
            <div class="text-center py-5 text-muted">
                <i class="fas fa-route fa-3x mb-3 d-block"></i>
                No hay rutas registradas.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Distancia</th>
                        <th>Tiempo est.</th>
                        <th>Descripción</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rutas as $r)
                    <tr>
                        <td><small class="text-muted">#{{ $r['id'] }}</small></td>
                        <td><strong>{{ $r['name'] }}</strong></td>
                        <td>
                            <i class="fas fa-map-marker-alt text-success mr-1"></i>
                            {{ $r['origin'] }}
                        </td>
                        <td>
                            <i class="fas fa-flag text-danger mr-1"></i>
                            {{ $r['destination'] }}
                        </td>
                        <td>
                            {{ $r['distance_km'] ? number_format($r['distance_km'], 1) . ' km' : '—' }}
                        </td>
                        <td>
                            @if($r['estimated_minutes'])
                                @php $h = intdiv($r['estimated_minutes'],60); $m = $r['estimated_minutes']%60; @endphp
                                {{ $h > 0 ? $h.'h ' : '' }}{{ $m > 0 ? $m.'min' : '' }}
                            @else —
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $r['description'] ? Str::limit($r['description'], 50) : '—' }}
                            </small>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-info btn-editar"
                                    data-id="{{ $r['id'] }}"
                                    data-name="{{ $r['name'] }}"
                                    data-origin="{{ $r['origin'] }}"
                                    data-destination="{{ $r['destination'] }}"
                                    data-distance="{{ $r['distance_km'] }}"
                                    data-minutes="{{ $r['estimated_minutes'] }}"
                                    data-description="{{ $r['description'] }}"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-danger btn-eliminar"
                                    data-id="{{ $r['id'] }}"
                                    data-nombre="{{ $r['name'] }}"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($paginado['last_page'] > 1)
        <div class="card-footer clearfix">
            <ul class="pagination pagination-sm m-0 float-right">
                @if($paginado['current_page'] > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('admin.rutas') }}?page={{ $paginado['current_page']-1 }}">«</a>
                    </li>
                @endif
                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="{{ route('admin.rutas') }}?page={{ $p }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link" href="{{ route('admin.rutas') }}?page={{ $paginado['current_page']+1 }}">»</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Nueva Ruta</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.rutas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Ej: San José - Heredia" maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto de inicio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt text-success"></i></span>
                                    </div>
                                    <input type="text" name="origin"
                                           class="form-control @error('origin') is-invalid @enderror"
                                           value="{{ old('origin') }}" placeholder="Ej: Terminal San José">
                                </div>
                                @error('origin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto final <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-flag text-danger"></i></span>
                                    </div>
                                    <input type="text" name="destination"
                                           class="form-control @error('destination') is-invalid @enderror"
                                           value="{{ old('destination') }}" placeholder="Ej: Heredia Centro">
                                </div>
                                @error('destination')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distancia <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="distance_km" class="form-control"
                                           value="{{ old('distance_km') }}" step="0.1" min="0" placeholder="0.0">
                                    <div class="input-group-append"><span class="input-group-text">km</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiempo estimado <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="estimated_minutes" class="form-control"
                                           value="{{ old('estimated_minutes') }}" min="1" placeholder="0">
                                    <div class="input-group-append"><span class="input-group-text">min</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Descripción <small class="text-muted">(opcional)</small></label>
                        <textarea name="description" rows="2" class="form-control"
                                  placeholder="Observaciones..." maxlength="500">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Crear Ruta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Ruta</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formEditar" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" maxlength="150">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto de inicio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt text-success"></i></span>
                                    </div>
                                    <input type="text" name="origin" id="editOrigin" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto final <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-flag text-danger"></i></span>
                                    </div>
                                    <input type="text" name="destination" id="editDestination" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distancia <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="distance_km" id="editDistance"
                                           class="form-control" step="0.1" min="0">
                                    <div class="input-group-append"><span class="input-group-text">km</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiempo estimado <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="estimated_minutes" id="editMinutes"
                                           class="form-control" min="1">
                                    <div class="input-group-append"><span class="input-group-text">min</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Descripción <small class="text-muted">(opcional)</small></label>
                        <textarea name="description" id="editDescription" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar eliminación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar la ruta <strong id="nombreEliminar"></strong>?</p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle mr-1"></i>Borrado lógico — el registro se conserva en BD.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $(document).on('click', '.btn-editar', function () {
        const d = $(this).data();
        $('#formEditar').attr('action', '/admin/rutas/' + d.id);
        $('#editName').val(d.name);
        $('#editOrigin').val(d.origin);
        $('#editDestination').val(d.destination);
        $('#editDistance').val(d.distance);
        $('#editMinutes').val(d.minutes);
        $('#editDescription').val(d.description);
        $('#modalEditar').modal('show');
    });

    $(document).on('click', '.btn-eliminar', function () {
        $('#nombreEliminar').text($(this).data('nombre'));
        $('#formEliminar').attr('action', '/admin/rutas/' + $(this).data('id'));
        $('#modalEliminar').modal('show');
    });

    @if($errors->any())
        $('#modalCrear').modal('show');
    @endif
});
</script>
@endpush