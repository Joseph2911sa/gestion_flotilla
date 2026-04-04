@extends('layouts.app')

@section('title', 'Rutas')
@section('page-title', 'Gestión de Rutas')

@section('content')

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm"
            data-toggle="modal" data-target="#modalCrear">
        <i class="fas fa-plus mr-1"></i>Nueva Ruta
    </button>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-route mr-2"></i>Rutas Registradas
        </h3>
    </div>
    <div class="card-body p-0">
        @php
            // Soporte para array paginado del API o colección Eloquent
            $lista = is_array($rutas) ? collect($rutas['data'] ?? $rutas) : collect($rutas);
        @endphp

        @if($lista->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-route fa-3x mb-3 d-block"></i>
                No hay rutas registradas aún.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Distancia</th>
                        <th>Descripción</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lista as $ruta)
                    @php $r = is_array($ruta) ? $ruta : $ruta->toArray(); @endphp
                    <tr>
                        <td><small class="text-muted">#{{ $r['id'] }}</small></td>
                        <td><strong>{{ $r['name'] }}</strong></td>
                        <td>
                            <i class="fas fa-map-marker-alt text-success mr-1"></i>
                            {{ $r['origin'] }}
                        </td>
                        <td>
                            <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                            {{ $r['destination'] }}
                        </td>
                        <td>
                            {{ !empty($r['distance_km']) ? number_format($r['distance_km'], 1) . ' km' : '—' }}
                        </td>
                        <td>
                            <small>{{ !empty($r['description']) ? Str::limit($r['description'], 50) : '—' }}</small>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm btn-editar"
                                    data-id="{{ $r['id'] }}"
                                    data-name="{{ $r['name'] }}"
                                    data-origin="{{ $r['origin'] }}"
                                    data-destination="{{ $r['destination'] }}"
                                    data-distance="{{ $r['distance_km'] ?? '' }}"
                                    data-description="{{ $r['description'] ?? '' }}"
                                    data-toggle="modal" data-target="#modalEditar">
                                <i class="fas fa-edit"></i>
                            </button>

                            <form action="{{ route('operador.rutas.destroy', $r['id']) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar esta ruta?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Nueva Ruta</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('operador.rutas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               placeholder="Ej: San José - Heredia" value="{{ old('name') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto de Inicio <span class="text-danger">*</span></label>
                                <input type="text" name="origin" class="form-control"
                                       placeholder="Ej: San José" value="{{ old('origin') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto Final <span class="text-danger">*</span></label>
                                <input type="text" name="destination" class="form-control"
                                       placeholder="Ej: Heredia" value="{{ old('destination') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distancia <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="distance_km" class="form-control"
                                           step="0.1" min="0" value="{{ old('distance_km') }}">
                                    <div class="input-group-append"><span class="input-group-text">km</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiempo est. <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="estimated_minutes" class="form-control"
                                           min="0" value="{{ old('estimated_minutes') }}">
                                    <div class="input-group-append"><span class="input-group-text">min</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Descripción <small class="text-muted">(opcional)</small></label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Ruta</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formEditar" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editNombre" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto de Inicio <span class="text-danger">*</span></label>
                                <input type="text" name="origin" id="editOrigen" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto Final <span class="text-danger">*</span></label>
                                <input type="text" name="destination" id="editDestino" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distancia <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="distance_km" id="editDistancia"
                                           class="form-control" step="0.1" min="0">
                                    <div class="input-group-append"><span class="input-group-text">km</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiempo est. <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="estimated_minutes" id="editMinutos"
                                           class="form-control" min="0">
                                    <div class="input-group-append"><span class="input-group-text">min</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Descripción <small class="text-muted">(opcional)</small></label>
                        <textarea name="description" id="editDescripcion" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $(document).on('click', '.btn-editar', function () {
        const d = $(this).data();
        $('#formEditar').attr('action', '/operador/rutas/' + d.id);
        $('#editNombre').val(d.name);
        $('#editOrigen').val(d.origin);
        $('#editDestino').val(d.destination);
        $('#editDistancia').val(d.distance);
        $('#editDescripcion').val(d.description);
    });
});
</script>
@endpush