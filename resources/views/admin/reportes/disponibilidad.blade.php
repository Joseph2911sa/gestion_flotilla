@extends('layouts.app')

@section('title', 'Disponibilidad de Vehículos')
@section('page-title', 'Reporte — Disponibilidad de Vehículos')

@section('content')

@php
$statusLabels = [
    'available'      => ['label' => 'Disponible',        'badge' => 'success'],
    'in_use'         => ['label' => 'En uso',             'badge' => 'primary'],
    'maintenance'    => ['label' => 'Mantenimiento',      'badge' => 'warning'],
    'out_of_service' => ['label' => 'Fuera de servicio',  'badge' => 'danger'],
];
@endphp

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.reportes') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Disponibilidad</li>
    </ol>
</nav>

{{-- Filtros --}}
<div class="card card-outline card-success mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
    </div>
    <form action="{{ route('admin.reportes.disponibilidad') }}" method="GET">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label for="start_date">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $startDate) }}">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label for="end_date">Fecha Fin <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $endDate) }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-search mr-1"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Resultados --}}
@if($startDate && $endDate)
<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-car mr-2"></i>
            Vehículos — del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </h3>
        <div class="card-tools">
            <span class="badge badge-secondary">{{ count($vehiculos) }} registro(s)</span>
        </div>
    </div>
    <div class="card-body p-0">
        @php $lista = collect($vehiculos); @endphp

        @if($lista->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 d-block"></i>
                No se encontraron vehículos para ese rango de fechas.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="70">Imagen</th>
                        <th>Placa</th>
                        <th>Vehículo</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Combustible</th>
                        <th>Estado actual</th>
                        <th>Disponibilidad en rango</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lista as $vehiculo)
                    @php
                        // El API devuelve arrays, no objetos
                        $v      = is_array($vehiculo) ? $vehiculo : $vehiculo->toArray();
                        $info   = $statusLabels[$v['status'] ?? ''] ?? ['label' => $v['status'] ?? '—', 'badge' => 'secondary'];
                        $ocupado = !empty($v['trip_request_id']);
                        $imgUrl  = !empty($v['image_path']) ? asset('storage/' . $v['image_path']) : null;
                    @endphp
                    <tr>
                        <td>
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $v['plate'] }}"
                                     style="width:55px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6">
                            @else
                                <div style="width:55px;height:40px;background:#f4f6f9;border-radius:4px;
                                            border:1px solid #dee2e6;display:flex;align-items:center;justify-content:center">
                                    <i class="fas fa-car text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td><strong>{{ $v['plate'] }}</strong></td>
                        <td>
                            {{ $v['brand'] }} {{ $v['model'] }}
                            <small class="d-block text-muted">{{ $v['year'] }}</small>
                        </td>
                        <td>{{ $v['vehicle_type'] }}</td>
                        <td>{{ $v['capacity'] }} <small class="text-muted">pers.</small></td>
                        <td>{{ $v['fuel_type'] }}</td>
                        <td>
                            <span class="badge badge-{{ $info['badge'] }}">{{ $info['label'] }}</span>
                        </td>
                        <td>
                            @if($ocupado)
                                <span class="badge badge-danger">
                                    <i class="fas fa-times mr-1"></i>Ocupado en rango
                                </span>
                                @if(!empty($v['departure_date']) && !empty($v['return_date']))
                                <small class="d-block text-muted mt-1">
                                    {{ \Carbon\Carbon::parse($v['departure_date'])->format('d/m/Y H:i') }}
                                    →
                                    {{ \Carbon\Carbon::parse($v['return_date'])->format('d/m/Y H:i') }}
                                </small>
                                @endif
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-check mr-1"></i>Disponible en rango
                                </span>
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