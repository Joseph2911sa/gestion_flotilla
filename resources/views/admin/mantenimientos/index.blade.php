@extends('layouts.app')

@section('title', 'Mantenimientos')
@section('page-title', 'Gestión de Mantenimientos')

@section('content')

@php
$tipoLabels = [
    'preventive' => ['label' => 'Preventivo', 'badge' => 'info'],
    'corrective' => ['label' => 'Correctivo', 'badge' => 'danger'],
    'inspection' => ['label' => 'Inspección', 'badge' => 'secondary'],
];
@endphp

{{-- Filtros --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form action="{{ route('admin.mantenimientos') }}" method="GET"
              class="form-inline flex-wrap" style="gap:8px">
            <select name="vehicle_id" class="form-control form-control-sm">
                <option value="">Todos los vehículos</option>
                @foreach($vehiculos as $v)
                    <option value="{{ $v['id'] ?? $v["id"] }}" {{ $vehiculoFiltro == ($v['id'] ?? $v["id"]) ? 'selected' : '' }}>
                        {{ $v['plate'] }} — {{ $v['brand'] }} {{ $v['model'] }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm">
                <option value="">Todos los estados</option>
                <option value="open"   {{ $statusFiltro === 'open'   ? 'selected' : '' }}>Abierto</option>
                <option value="closed" {{ $statusFiltro === 'closed' ? 'selected' : '' }}>Cerrado</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-filter mr-1"></i>Filtrar
            </button>
            @if($vehiculoFiltro || $statusFiltro)
                <a href="{{ route('admin.mantenimientos') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times mr-1"></i>Limpiar
                </a>
            @endif
            <div class="ml-auto">
                <button type="button" class="btn btn-warning btn-sm"
                        data-toggle="modal" data-target="#modalAbrir">
                    <i class="fas fa-tools mr-1"></i>Abrir Mantenimiento
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tools mr-2"></i>Mantenimientos</h3>
        <div class="card-tools">
            <span class="badge badge-secondary">{{ $paginado['total'] }} registro(s)</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(count($mantenimientos) === 0)
            <div class="text-center py-5 text-muted">
                <i class="fas fa-tools fa-3x mb-3 d-block"></i>
                No se encontraron mantenimientos.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Vehículo</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Inicio</th>
                        <th>Cierre</th>
                        <th>Costo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mantenimientos as $m)
                    @php $tipo = $tipoLabels[$m['type']] ?? ['label'=>$m['type'],'badge'=>'secondary']; @endphp
                    <tr>
                        <td><small class="text-muted">#{{ $m['id'] }}</small></td>
                        <td>
                            <strong>{{ $m['vehicle']['plate'] ?? '—' }}</strong>
                            <small class="d-block text-muted">
                                {{ ($m['vehicle']['brand'] ?? '') . ' ' . ($m['vehicle']['model'] ?? '') }}
                            </small>
                        </td>
                        <td><span class="badge badge-{{ $tipo['badge'] }}">{{ $tipo['label'] }}</span></td>
                        <td><small>{{ Str::limit($m['description'], 50) }}</small></td>
                        <td>
                            <small>
                                {{ $m['start_date']
                                    ? \Carbon\Carbon::parse($m['start_date'])->format('d/m/Y')
                                    : '—' }}
                            </small>
                        </td>
                        <td>
                            <small>
                                {{ $m['end_date']
                                    ? \Carbon\Carbon::parse($m['end_date'])->format('d/m/Y')
                                    : '—' }}
                            </small>
                        </td>
                        <td>{{ $m['cost'] ? '₡' . number_format($m['cost'], 2) : '—' }}</td>
                        <td>
                            @if($m['status'] === 'open')
                                <span class="badge badge-warning">
                                    <i class="fas fa-lock-open mr-1"></i>Abierto
                                </span>
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-lock mr-1"></i>Cerrado
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($m['status'] === 'open')
                                <button type="button" class="btn btn-xs btn-success btn-cerrar"
                                        data-id="{{ $m['id'] }}"
                                        data-placa="{{ $m['vehicle']['plate'] ?? '' }}"
                                        data-inicio="{{ $m['start_date'] }}"
                                        title="Cerrar">
                                    <i class="fas fa-lock"></i> Cerrar
                                </button>
                            @else
                                <span class="text-muted small">
                                    <i class="fas fa-check-circle text-success"></i> Cerrado
                                </span>
                            @endif
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
                        <a class="page-link"
                           href="{{ route('admin.mantenimientos') }}?page={{ $paginado['current_page']-1 }}&vehicle_id={{ $vehiculoFiltro }}&status={{ $statusFiltro }}">«</a>
                    </li>
                @endif
                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link"
                           href="{{ route('admin.mantenimientos') }}?page={{ $p }}&vehicle_id={{ $vehiculoFiltro }}&status={{ $statusFiltro }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ route('admin.mantenimientos') }}?page={{ $paginado['current_page']+1 }}&vehicle_id={{ $vehiculoFiltro }}&status={{ $statusFiltro }}">»</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal: Abrir Mantenimiento --}}
<div class="modal fade" id="modalAbrir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-tools mr-2"></i>Abrir Mantenimiento</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('admin.mantenimientos.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Vehículo <span class="text-danger">*</span></label>
                        <select name="vehicle_id"
                                class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">— Seleccione vehículo disponible —</option>
                            @foreach($vehiculos as $v)
                                @if(isset($v['status']) && $v['status'] === 'available')
                                <option value="{{ $v['id'] ?? $v["id"] }}" {{ old('vehicle_id') == $v['id'] ? 'selected' : '' }}>
                                    {{ $v['plate'] }} — {{ $v['brand'] }} {{ $v['model'] }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                        @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select name="type" class="form-control @error('type') is-invalid @enderror">
                            <option value="">— Seleccione tipo —</option>
                            <option value="preventive" {{ old('type') === 'preventive' ? 'selected' : '' }}>Preventivo</option>
                            <option value="corrective"  {{ old('type') === 'corrective'  ? 'selected' : '' }}>Correctivo</option>
                            <option value="inspection"  {{ old('type') === 'inspection'  ? 'selected' : '' }}>Inspección</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Descripción <span class="text-danger">*</span></label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Describe el trabajo a realizar..."
                                  maxlength="500">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha inicio <span class="text-danger">*</span></label>
                                <input type="date" name="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', date('Y-m-d')) }}">
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kilometraje <small class="text-muted">(opcional)</small></label>
                                <div class="input-group">
                                    <input type="number" name="mileage_at_service" class="form-control"
                                           value="{{ old('mileage_at_service') }}" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Costo estimado <small class="text-muted">(opcional)</small></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">₡</span>
                            </div>
                            <input type="number" name="cost" class="form-control"
                                   value="{{ old('cost') }}" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-tools mr-1"></i>Abrir Mantenimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Cerrar Mantenimiento --}}
<div class="modal fade" id="modalCerrar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-lock mr-2"></i>Cerrar Mantenimiento</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formCerrar" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="callout callout-success py-2 px-3 mb-3 small">
                        <i class="fas fa-car mr-1"></i>
                        Al cerrar, el vehículo <strong id="placaCerrar"></strong>
                        volverá a estado <strong>Disponible</strong>.
                    </div>
                    <div class="form-group">
                        <label>Fecha de cierre</label>
                        <input type="date" name="end_date" id="end_date_cerrar"
                               class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group mb-0">
                        <label>Costo final <small class="text-muted">(opcional)</small></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">₡</span>
                            </div>
                            <input type="number" name="cost" id="cost_cerrar"
                                   class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-lock mr-1"></i>Cerrar Mantenimiento
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
    $(document).on('click', '.btn-cerrar', function () {
        const id    = $(this).data('id');
        const placa = $(this).data('placa');
        const inicio = $(this).data('inicio');
        $('#placaCerrar').text(placa);
        $('#end_date_cerrar').attr('min', inicio);
        $('#formCerrar').attr('action', '/admin/mantenimientos/' + id + '/cerrar');
        $('#cost_cerrar').val('');
        $('#modalCerrar').modal('show');
    });

    @if($errors->any())
        $('#modalAbrir').modal('show');
    @endif
});
</script>
@endpush