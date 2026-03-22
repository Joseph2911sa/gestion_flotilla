@extends('layouts.app')

@section('title', 'Historial del Chofer')
@section('page-title', 'Reporte — Historial del Chofer')

@section('content')

@php
$statusConfig = [
    'pending'   => ['label' => 'Pendiente',  'badge' => 'warning'],
    'approved'  => ['label' => 'Aprobada',   'badge' => 'success'],
    'rejected'  => ['label' => 'Rechazada',  'badge' => 'danger'],
    'cancelled' => ['label' => 'Cancelada',  'badge' => 'secondary'],
    'completed' => ['label' => 'Completada', 'badge' => 'primary'],
];
@endphp

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.reportes') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Historial del Chofer</li>
    </ol>
</nav>

{{-- Filtros --}}
<div class="card card-outline card-warning mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
    </div>
    <form action="{{ route('admin.reportes.historial-chofer') }}" method="GET">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label for="driver_id">Chofer <span class="text-danger">*</span></label>
                        <select name="driver_id" id="driver_id"
                                class="form-control @error('driver_id') is-invalid @enderror">
                            <option value="">— Seleccione un chofer —</option>
                            @foreach($choferes as $c)
                                <option value="{{ $c->id }}"
                                    {{ $driverId == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label for="start_date">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $startDate) }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label for="end_date">Fecha Fin <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $endDate) }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Resultados --}}
@if($driverId && $startDate && $endDate)

{{-- Info del chofer --}}
@if($chofer)
<div class="callout callout-warning mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-user-tie fa-2x text-warning mr-3"></i>
        <div>
            <h5 class="mb-0">{{ $chofer->name }}</h5>
            <small class="text-muted">
                {{ $chofer->email }}
                &nbsp;·&nbsp;
                Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </small>
        </div>
        <div class="ml-auto text-right">
            <div class="h4 mb-0">{{ $solicitudes->count() }}</div>
            <small class="text-muted">solicitud(es)</small>
        </div>
    </div>
</div>
@endif

{{-- Resumen por estado --}}
@if($solicitudes->isNotEmpty())
<div class="row mb-4">
    @foreach($statusConfig as $st => $cfg)
    @php $cant = $solicitudes->where('status', $st)->count(); @endphp
    @if($cant > 0)
    <div class="col-md-2 col-4 mb-2">
        <div class="small-box bg-{{ $cfg['badge'] === 'secondary' ? 'secondary' : $cfg['badge'] }}">
            <div class="inner">
                <h4>{{ $cant }}</h4>
                <p>{{ $cfg['label'] }}</p>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-2"></i>
            Solicitudes de {{ $chofer->name ?? 'Chofer' }}
        </h3>
        <div class="card-tools">
            <span class="badge badge-secondary">{{ $solicitudes->count() }} registro(s)</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($solicitudes->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                No se encontraron solicitudes para este chofer en el período seleccionado.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Vehículo</th>
                        <th>Tipo</th>
                        <th>Salida</th>
                        <th>Retorno</th>
                        <th>Estado</th>
                        <th>Motivo / Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudes as $s)
                    @php $cfg = $statusConfig[$s->status] ?? ['label' => $s->status, 'badge' => 'secondary']; @endphp
                    <tr>
                        <td><small class="text-muted">#{{ $s->id }}</small></td>
                        <td>
                            <strong>{{ $s->plate }}</strong>
                            <small class="d-block text-muted">{{ $s->brand }} {{ $s->model }}</small>
                        </td>
                        <td>{{ $s->vehicle_type }}</td>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($s->departure_date)->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($s->return_date)->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <span class="badge badge-{{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                        </td>
                        <td>
                            @if($s->rejection_reason)
                                <span class="text-danger small">
                                    <i class="fas fa-times-circle mr-1"></i>{{ $s->rejection_reason }}
                                </span>
                            @elseif($s->reason)
                                <small class="text-muted">{{ Str::limit($s->reason, 50) }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif

@endsection