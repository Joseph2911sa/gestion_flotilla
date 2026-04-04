@extends('layouts.app')

@section('title', 'Viajes')
@section('page-title', 'Gestión de Viajes')

@section('content')

{{-- Botón registrar salida --}}
<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary btn-sm"
            data-toggle="modal" data-target="#modalSalida">
        <i class="fas fa-car mr-1"></i>Registrar Salida
    </button>
</div>

{{-- Tabla de viajes --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-road mr-2"></i>Historial de Viajes
        </h3>
        <div class="card-tools">
            <span class="badge badge-secondary">
                {{ isset($paginado['total']) ? $paginado['total'] : count($viajes) }} viaje(s)
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        @php $lista = collect($viajes); @endphp

        @if($lista->isEmpty())
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
                    @foreach($lista as $viaje)
                    @php
                        $vj         = is_array($viaje) ? $viaje : $viaje->toArray();
                        $completado = !empty($vj['end_time']) && !empty($vj['end_mileage']);
                        $kmRec      = ($completado && isset($vj['start_mileage'], $vj['end_mileage']))
                                        ? ($vj['end_mileage'] - $vj['start_mileage'])
                                        : null;
                    @endphp
                    <tr>
                        <td><small class="text-muted">#{{ $vj['id'] }}</small></td>
                        <td>
                            {{ $vj['driver']['name'] ?? '—' }}
                            <small class="d-block text-muted">{{ $vj['driver']['email'] ?? '' }}</small>
                        </td>
                        <td>
                            <strong>{{ $vj['vehicle']['plate'] ?? '—' }}</strong>
                            <small class="d-block text-muted">
                                {{ ($vj['vehicle']['brand'] ?? '') . ' ' . ($vj['vehicle']['model'] ?? '') }}
                            </small>
                        </td>
                        <td>{{ $vj['route']['name'] ?? '—' }}</td>
                        <td>
                            <small>
                                {{ !empty($vj['start_time'])
                                    ? \Carbon\Carbon::parse($vj['start_time'])->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                        <td>
                            <small>
                                {{ !empty($vj['end_time'])
                                    ? \Carbon\Carbon::parse($vj['end_time'])->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                        <td>{{ isset($vj['start_mileage']) ? number_format($vj['start_mileage']) . ' km' : '—' }}</td>
                        <td>{{ isset($vj['end_mileage']) ? number_format($vj['end_mileage']) . ' km' : '—' }}</td>
                        <td>
                            @if($kmRec !== null)
                                <span class="badge badge-info">{{ number_format($kmRec) }} km</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($completado)
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
                            @if(!$completado)
                                <button type="button"
                                        class="btn btn-success btn-sm btn-retorno"
                                        data-id="{{ $vj['id'] }}"
                                        data-chofer="{{ $vj['driver']['name'] ?? '' }}"
                                        data-vehiculo="{{ $vj['vehicle']['plate'] ?? '' }}"
                                        data-km="{{ $vj['start_mileage'] ?? 0 }}"
                                        title="Registrar retorno">
                                    <i class="fas fa-flag-checkered mr-1"></i>Retorno
                                </button>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if(isset($paginado['last_page']) && $paginado['last_page'] > 1)
        <div class="card-footer clearfix">
            <ul class="pagination pagination-sm m-0 float-right">
                @if($paginado['current_page'] > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('operador.viajes') }}?page={{ $paginado['current_page']-1 }}">«</a>
                    </li>
                @endif
                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="{{ route('operador.viajes') }}?page={{ $p }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link" href="{{ route('operador.viajes') }}?page={{ $paginado['current_page']+1 }}">»</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- ── MODAL: Registrar Salida ─────────────────────────────────────────────── --}}
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
                    @php $solicitudesLista = collect($solicitudesAprobadas ?? []); @endphp

                    @if($solicitudesLista->isEmpty())
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No hay solicitudes aprobadas pendientes de salida.
                        </div>
                    @else
                    <div class="form-group">
                        <label>Solicitud Aprobada <span class="text-danger">*</span></label>
                        <select name="trip_request_id"
                                class="form-control @error('trip_request_id') is-invalid @enderror">
                            <option value="">-- Seleccione una solicitud --</option>
                            @foreach($solicitudesLista as $sol)
                            @php $s = is_array($sol) ? $sol : $sol->toArray(); @endphp
                                <option value="{{ $s['id'] }}" {{ old('trip_request_id') == $s['id'] ? 'selected' : '' }}>
                                    #{{ $s['id'] }} —
                                    {{ $s['user']['name'] ?? '?' }} |
                                    {{ $s['vehicle']['plate'] ?? '?' }}
                                    {{ $s['vehicle']['brand'] ?? '' }}
                                    {{ $s['vehicle']['model'] ?? '' }} |
                                    {{ !empty($s['departure_date']) ? \Carbon\Carbon::parse($s['departure_date'])->format('d/m/Y H:i') : '' }}
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
                                <input type="datetime-local" name="start_time"
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
                                    <input type="number" name="start_mileage"
                                           class="form-control @error('start_mileage') is-invalid @enderror"
                                           placeholder="Ej: 45000" min="0"
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

                    <div class="form-group mb-0">
                        <label>Observaciones <small class="text-muted">(opcional)</small></label>
                        <textarea name="observations" rows="2" class="form-control"
                                  placeholder="Notas adicionales...">{{ old('observations') }}</textarea>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    @if(!($solicitudesLista ?? collect())->isEmpty())
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-car mr-1"></i>Registrar Salida
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL: Registrar Retorno ────────────────────────────────────────────── --}}
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
                    <div class="callout callout-success py-2 px-3 mb-3 small">
                        Chofer: <strong id="retornoChofer"></strong> &nbsp;|&nbsp;
                        Vehículo: <strong id="retornoVehiculo"></strong> &nbsp;|&nbsp;
                        Km inicial: <strong id="retornoKmInicial"></strong> km
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha/Hora de Retorno <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_time"
                                       class="form-control"
                                       value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kilometraje Final <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="end_mileage" id="end_mileage"
                                           class="form-control" placeholder="Ej: 45300" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Observaciones <small class="text-muted">(opcional)</small></label>
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
    // Abrir modal salida si hay errores
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
        $('#end_mileage').attr('min', parseInt(km) + 1).val('');
        $('#formRetorno').attr('action', '/operador/viajes/' + id + '/retorno');
        $('#modalRetorno').modal('show');
    });
});
</script>
@endpush