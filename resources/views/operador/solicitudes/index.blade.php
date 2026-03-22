@extends('layouts.app')

@section('title', 'Solicitudes')
@section('page-title', 'Panel Operador — Solicitudes')

@section('content')

@php
$statusConfig = [
    'pending'   => ['label'=>'Pendientes',  'badge'=>'warning',   'icon'=>'clock'],
    'approved'  => ['label'=>'Aprobadas',   'badge'=>'success',   'icon'=>'check-circle'],
    'rejected'  => ['label'=>'Rechazadas',  'badge'=>'danger',    'icon'=>'times-circle'],
    'cancelled' => ['label'=>'Canceladas',  'badge'=>'secondary', 'icon'=>'ban'],
    'completed' => ['label'=>'Completadas', 'badge'=>'primary',   'icon'=>'flag-checkered'],
];
@endphp

{{-- Tabs de estado --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $statusFiltro === 'all' ? 'active' : '' }}"
           href="{{ route('operador.solicitudes') }}?status=all">
            Todas
        </a>
    </li>
    @foreach(['pending','approved','rejected','cancelled'] as $st)
    <li class="nav-item">
        <a class="nav-link {{ $statusFiltro === $st ? 'active' : '' }}"
           href="{{ route('operador.solicitudes') }}?status={{ $st }}">
            <i class="fas fa-{{ $statusConfig[$st]['icon'] }} mr-1"></i>
            {{ $statusConfig[$st]['label'] }}
            @if(isset($contadores[$st]) && $contadores[$st] > 0)
                <span class="badge badge-{{ $statusConfig[$st]['badge'] }} ml-1">
                    {{ $contadores[$st] }}
                </span>
            @endif
        </a>
    </li>
    @endforeach
</ul>

{{-- Botón asignación directa --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <small class="text-muted">
        Total: <strong>{{ $paginado['total'] }}</strong> solicitud(es)
    </small>
    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAsignacion">
        <i class="fas fa-car-side mr-1"></i> Asignación Directa
    </button>
</div>

{{-- Tabla de solicitudes --}}
<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-list mr-2"></i>
            {{ $statusConfig[$statusFiltro]['label'] ?? 'Solicitudes' }}
        </h3>
        <div class="card-tools">
            <span class="badge badge-secondary">
                Página {{ $paginado['current_page'] }} de {{ $paginado['last_page'] }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(count($solicitudes) === 0)
            <div class="text-center py-5 text-muted">
                <i class="fas fa-clipboard fa-3x mb-3 d-block"></i>
                No hay solicitudes {{ $statusFiltro !== 'all' ? 'con este estado' : '' }}.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Chofer</th>
                        <th>Vehículo</th>
                        <th>Salida</th>
                        <th>Retorno</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudes as $s)
                    @php
                        $cfg = $statusConfig[$s['status']] ?? ['label'=>$s['status'],'badge'=>'secondary'];
                    @endphp
                    <tr>
                        <td><small class="text-muted">#{{ $s['id'] }}</small></td>
                        <td>
                            <div>{{ $s['user']['name'] ?? '—' }}</div>
                            <small class="text-muted">{{ $s['user']['email'] ?? '' }}</small>
                        </td>
                        <td>
                            @if($s['vehicle'])
                                <strong>{{ $s['vehicle']['plate'] }}</strong>
                                <small class="d-block text-muted">
                                    {{ $s['vehicle']['brand'] }} {{ $s['vehicle']['model'] }}
                                </small>
                            @else
                                <span class="text-muted">Sin vehículo</span>
                            @endif
                        </td>
                        <td>
                            <small>
                                {{ $s['departure_date']
                                    ? \Carbon\Carbon::parse($s['departure_date'])->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                        <td>
                            <small>
                                {{ $s['return_date']
                                    ? \Carbon\Carbon::parse($s['return_date'])->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                        <td>
                            <small>{{ Str::limit($s['reason'] ?? '—', 40) }}</small>
                        </td>
                        <td>
                            <span class="badge badge-{{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                            @if($s['rejection_reason'])
                                <i class="fas fa-info-circle text-muted ml-1"
                                   title="{{ $s['rejection_reason'] }}"
                                   data-toggle="tooltip"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($s['status'] === 'pending')
                                {{-- Aprobar --}}
                                <form action="{{ route('operador.solicitudes.aprobar', $s['id']) }}"
                                      method="POST" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-xs btn-success"
                                            title="Aprobar"
                                            onclick="return confirm('¿Aprobar esta solicitud?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                {{-- Rechazar --}}
                                <button type="button" class="btn btn-xs btn-danger btn-rechazar"
                                        data-id="{{ $s['id'] }}"
                                        data-chofer="{{ $s['user']['name'] ?? '' }}"
                                        title="Rechazar">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <span class="text-muted small">
                                    <i class="fas fa-lock"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($paginado['last_page'] > 1)
        <div class="card-footer clearfix">
            <ul class="pagination pagination-sm m-0 float-right">
                @if($paginado['current_page'] > 1)
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ route('operador.solicitudes') }}?status={{ $statusFiltro }}&page={{ $paginado['current_page']-1 }}">«</a>
                    </li>
                @endif
                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link"
                           href="{{ route('operador.solicitudes') }}?status={{ $statusFiltro }}&page={{ $p }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ route('operador.solicitudes') }}?status={{ $statusFiltro }}&page={{ $paginado['current_page']+1 }}">»</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- ── MODAL: Rechazar solicitud ──────────────────────────────────────────── --}}
<div class="modal fade" id="modalRechazar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle mr-2"></i>Rechazar Solicitud
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formRechazar" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Rechazando solicitud de: <strong id="choferRechazar"></strong></p>
                    <div class="form-group mb-0">
                        <label for="rejection_reason">
                            Motivo del rechazo <small class="text-muted">(opcional)</small>
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason"
                                  class="form-control" rows="3"
                                  placeholder="Describe el motivo del rechazo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>Rechazar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL: Asignación directa ──────────────────────────────────────────── --}}
<div class="modal fade" id="modalAsignacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-car-side mr-2"></i>Asignación Directa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('operador.solicitudes.directa') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="callout callout-info py-2 px-3 mb-3 small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Crea una asignación directamente aprobada sin pasar por solicitud del chofer.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">Chofer <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">— Seleccione chofer —</option>
                                    @foreach(\App\Models\User::whereHas('role', fn($q) => $q->where('name','Chofer'))->get() as $chofer)
                                        <option value="{{ $chofer->id }}" {{ old('user_id') == $chofer->id ? 'selected' : '' }}>
                                            {{ $chofer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_id">Vehículo <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control">
                                    <option value="">— Seleccione vehículo —</option>
                                    @foreach(\App\Models\Vehicle::where('status','available')->get() as $v)
                                        <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                            {{ $v->plate }} — {{ $v->brand }} {{ $v->model }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="departure_date">Fecha/Hora Salida <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="departure_date" id="departure_date"
                                       class="form-control" value="{{ old('departure_date') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="return_date">Fecha/Hora Retorno <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="return_date" id="return_date"
                                       class="form-control" value="{{ old('return_date') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="route_id">Ruta <small class="text-muted">(opcional)</small></label>
                                <select name="route_id" id="route_id" class="form-control">
                                    <option value="">— Sin ruta específica —</option>
                                    @foreach(\App\Models\Route::all() as $ruta)
                                        <option value="{{ $ruta->id }}" {{ old('route_id') == $ruta->id ? 'selected' : '' }}>
                                            {{ $ruta->name }} ({{ $ruta->origin }} → {{ $ruta->destination }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reason">Motivo <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="reason" id="reason"
                                       class="form-control" placeholder="Ej: Viaje urgente a sede central"
                                       value="{{ old('reason') }}" maxlength="500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i>Crear Asignación
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
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Modal rechazar
    $(document).on('click', '.btn-rechazar', function () {
        const id     = $(this).data('id');
        const chofer = $(this).data('chofer');
        $('#choferRechazar').text(chofer);
        $('#formRechazar').attr('action', '/operador/solicitudes/' + id + '/rechazar');
        $('#rejection_reason').val('');
        $('#modalRechazar').modal('show');
    });

    // Abrir modal asignación directa si hay errores de validación
    @if($errors->any() && old('user_id'))
        $('#modalAsignacion').modal('show');
    @endif

    // Validar que retorno > salida
    $('#return_date').on('change', function () {
        const salida  = $('#departure_date').val();
        const retorno = $(this).val();
        if (salida && retorno && retorno <= salida) {
            alert('La fecha de retorno debe ser posterior a la fecha de salida.');
            $(this).val('');
        }
    });
});
</script>
@endpush