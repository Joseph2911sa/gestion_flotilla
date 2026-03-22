@extends('layouts.app')

@section('title', 'Viajes')
@section('page-title', 'Gestión de Viajes')

@section('content')

{{-- Botón registrar salida --}}
<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalSalida">
        <i class="fas fa-car mr-1"></i>Registrar Salida
    </button>
</div>

{{-- Tabla de viajes --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-road mr-2"></i>Historial de Viajes
        </h3>
    </div>
    <div class="card-body p-0">
        @if($viajes->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-road fa-3x mb-3 d-block"></i>
                No hay viajes registrados aún.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Chofer</th>
                        <th>Vehículo</th>
                        <th>Ruta</th>
                        <th>Salida</th>
                        <th>Retorno</th>
                        <th>Km Salida</th>
                        <th>Km Retorno</th>
                        <th>Km Recorridos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($viajes as $viaje)
                    <tr>
                        <td><small class="text-muted">#{{ $viaje->id }}</small></td>
                        <td>
                            {{ $viaje->driver->name ?? '—' }}
                            <br>
                            <small class="text-muted">{{ $viaje->driver->email ?? '' }}</small>
                        </td>
                        <td>
                            <strong>{{ $viaje->vehicle->plate ?? '—' }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $viaje->vehicle->brand ?? '' }} {{ $viaje->vehicle->model ?? '' }}
                            </small>
                        </td>
                        <td>{{ $viaje->route->name ?? '—' }}</td>
                        <td>
                            <small>
                                {{ $viaje->start_time
                                    ? \Carbon\Carbon::parse($viaje->start_time)->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                        <td>
                            <small>
                                {{ $viaje->end_time
                                    ? \Carbon\Carbon::parse($viaje->end_time)->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                        <td>{{ number_format($viaje->start_mileage) }} km</td>
                        <td>{{ $viaje->end_mileage ? number_format($viaje->end_mileage) . ' km' : '—' }}</td>
                        <td>
                            @if($viaje->end_mileage && $viaje->start_mileage)
                                <span class="badge badge-info">
                                    {{ number_format($viaje->end_mileage - $viaje->start_mileage) }} km
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($viaje->isCompleted())
                                <span class="badge badge-success">
                                    <i class="fas fa-flag-checkered mr-1"></i>Completado
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-road mr-1"></i>En curso
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(!$viaje->isCompleted())
                                <button type="button"
                                        class="btn btn-success btn-sm btn-retorno"
                                        data-id="{{ $viaje->id }}"
                                        data-chofer="{{ $viaje->driver->name ?? '' }}"
                                        data-vehiculo="{{ $viaje->vehicle->plate ?? '' }}"
                                        data-km="{{ $viaje->start_mileage }}"
                                        data-toggle="modal"
                                        data-target="#modalRetorno">
                                    <i class="fas fa-flag-checkered mr-1"></i>Retorno
                                </button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $viajes->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── MODAL: Registrar Salida ──────────────────────────────────────────────── --}}
<div class="modal fade" id="modalSalida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-car mr-2"></i>Registrar Salida de Vehículo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('operador.viajes.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    @if($solicitudesAprobadas->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No hay solicitudes aprobadas pendientes de salida.
                        </div>
                    @else

                    <div class="form-group">
                        <label>Solicitud Aprobada <span class="text-danger">*</span></label>
                        <select name="trip_request_id"
                                class="form-control @error('trip_request_id') is-invalid @enderror">
                            <option value="">-- Seleccione una solicitud --</option>
                            @foreach($solicitudesAprobadas as $sol)
                                <option value="{{ $sol->id }}" {{ old('trip_request_id') == $sol->id ? 'selected' : '' }}>
                                    #{{ $sol->id }} — {{ $sol->user->name ?? '?' }}
                                    | {{ $sol->vehicle->plate ?? '?' }}
                                    {{ $sol->vehicle->brand ?? '' }} {{ $sol->vehicle->model ?? '' }}
                                    | {{ \Carbon\Carbon::parse($sol->departure_date)->format('d/m/Y H:i') }}
                                </option>
                            @endforeach
                        </select>
                        @error('trip_request_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha/Hora de Salida <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                       name="start_time"
                                       class="form-control @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}">
                                @error('start_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kilometraje Inicial <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                           name="start_mileage"
                                           class="form-control @error('start_mileage') is-invalid @enderror"
                                           placeholder="Ej: 45000"
                                           min="0"
                                           value="{{ old('start_mileage') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                                @error('start_mileage')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones <span class="text-muted">(opcional)</span></label>
                        <textarea name="observations" rows="2" class="form-control"
                                  placeholder="Notas adicionales...">{{ old('observations') }}</textarea>
                    </div>

                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    @if(!$solicitudesAprobadas->isEmpty())
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-car mr-1"></i>Registrar Salida
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL: Registrar Retorno ─────────────────────────────────────────────── --}}
<div class="modal fade" id="modalRetorno" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-flag-checkered mr-2"></i>Registrar Retorno
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formRetorno" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>
                        Chofer: <strong id="retornoChofer"></strong> |
                        Vehículo: <strong id="retornoVehiculo"></strong>
                    </p>
                    <p>
                        Km inicial: <strong id="retornoKmInicial"></strong> km
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha/Hora de Retorno <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                       name="end_time"
                                       class="form-control"
                                       value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kilometraje Final <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number"
                                           name="end_mileage"
                                           id="end_mileage"
                                           class="form-control"
                                           placeholder="Ej: 45300"
                                           min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones <span class="text-muted">(opcional)</span></label>
                        <textarea name="observations" rows="2" class="form-control"
                                  placeholder="Notas del retorno..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-flag-checkered mr-1"></i>Registrar Retorno
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
    // Abrir modal de salida si hay errores de validación
    @if($errors->any() && old('trip_request_id'))
        $('#modalSalida').modal('show');
    @endif

    // Modal retorno — cargar datos del viaje
    $(document).on('click', '.btn-retorno', function () {
        const id       = $(this).data('id');
        const chofer   = $(this).data('chofer');
        const vehiculo = $(this).data('vehiculo');
        const km       = $(this).data('km');

        $('#retornoChofer').text(chofer);
        $('#retornoVehiculo').text(vehiculo);
        $('#retornoKmInicial').text(km);
        $('#end_mileage').attr('min', km);
        $('#formRetorno').attr('action', '/operador/viajes/' + id + '/retorno');
    });
});
</script>
@endpush