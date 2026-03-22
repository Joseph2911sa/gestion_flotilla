@extends('layouts.app')

@section('title', 'Rutas')
@section('page-title', 'Gestión de Rutas')

@section('content')

{{-- Botón nueva ruta --}}
<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm"
            data-toggle="modal" data-target="#modalCrear">
        <i class="fas fa-plus mr-1"></i>Nueva Ruta
    </button>
</div>

{{-- Tabla --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-route mr-2"></i>Rutas Registradas
        </h3>
    </div>
    <div class="card-body p-0">
        @if($rutas->isEmpty())
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
                    @foreach($rutas as $ruta)
                    <tr>
                        <td><small class="text-muted">#{{ $ruta->id }}</small></td>
                        <td><strong>{{ $ruta->name }}</strong></td>
                        <td>
                            <i class="fas fa-map-marker-alt text-success mr-1"></i>
                            {{ $ruta->origin }}
                        </td>
                        <td>
                            <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                            {{ $ruta->destination }}
                        </td>
                        <td>
                            {{ $ruta->distance_km ? number_format($ruta->distance_km, 1) . ' km' : '—' }}
                        </td>
                        <td>
                            <small>{{ $ruta->description ? \Illuminate\Support\Str::limit($ruta->description, 50) : '—' }}</small>
                        </td>
                        <td class="text-center">
                            {{-- Editar --}}
                            <button type="button"
                                    class="btn btn-warning btn-sm btn-editar"
                                    data-id="{{ $ruta->id }}"
                                    data-name="{{ $ruta->name }}"
                                    data-origin="{{ $ruta->origin }}"
                                    data-destination="{{ $ruta->destination }}"
                                    data-distance="{{ $ruta->distance_km }}"
                                    data-description="{{ $ruta->description }}"
                                    data-toggle="modal"
                                    data-target="#modalEditar">
                                <i class="fas fa-edit"></i>
                            </button>

                            {{-- Eliminar --}}
                            <form action="{{ route('operador.rutas.destroy', $ruta->id) }}"
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
        <div class="card-footer">
            {{ $rutas->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── MODAL: Crear Ruta ────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Nueva Ruta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('operador.rutas.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Ej: San José - Heredia"
                               value="{{ old('name') }}">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto de Inicio <span class="text-danger">*</span></label>
                                <input type="text" name="origin"
                                       class="form-control @error('origin') is-invalid @enderror"
                                       placeholder="Ej: San José"
                                       value="{{ old('origin') }}">
                                @error('origin')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Punto Final <span class="text-danger">*</span></label>
                                <input type="text" name="destination"
                                       class="form-control @error('destination') is-invalid @enderror"
                                       placeholder="Ej: Heredia"
                                       value="{{ old('destination') }}">
                                @error('destination')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distancia estimada <span class="text-muted">(opcional)</span></label>
                                <div class="input-group">
                                    <input type="number" name="distance_km"
                                           class="form-control"
                                           placeholder="Ej: 12.5"
                                           step="0.1" min="0"
                                           value="{{ old('distance_km') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiempo estimado <span class="text-muted">(opcional)</span></label>
                                <div class="input-group">
                                    <input type="number" name="estimated_minutes"
                                           class="form-control"
                                           placeholder="Ej: 45"
                                           min="0"
                                           value="{{ old('estimated_minutes') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción <span class="text-muted">(opcional)</span></label>
                        <textarea name="description" rows="2" class="form-control"
                                  placeholder="Descripción de la ruta...">{{ old('description') }}</textarea>
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

{{-- ── MODAL: Editar Ruta ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Ruta
                </h5>
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
                                <label>Distancia estimada <span class="text-muted">(opcional)</span></label>
                                <div class="input-group">
                                    <input type="number" name="distance_km" id="editDistancia"
                                           class="form-control" step="0.1" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tiempo estimado <span class="text-muted">(opcional)</span></label>
                                <div class="input-group">
                                    <input type="number" name="estimated_minutes" id="editMinutos"
                                           class="form-control" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción <span class="text-muted">(opcional)</span></label>
                        <textarea name="description" id="editDescripcion"
                                  rows="2" class="form-control"></textarea>
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
    @if($errors->any() && old('name'))
        $('#modalCrear').modal('show');
    @endif

    $(document).on('click', '.btn-editar', function () {
        const id          = $(this).data('id');
        const name        = $(this).data('name');
        const origin      = $(this).data('origin');
        const destination = $(this).data('destination');
        const distance    = $(this).data('distance');
        const description = $(this).data('description');

        $('#formEditar').attr('action', '/operador/rutas/' + id);
        $('#editNombre').val(name);
        $('#editOrigen').val(origin);
        $('#editDestino').val(destination);
        $('#editDistancia').val(distance);
        $('#editDescripcion').val(description);
    });
});
</script>
@endpush