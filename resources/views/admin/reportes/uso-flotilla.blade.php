@extends('layouts.app')

@section('title', 'Uso de Flotilla')
@section('page-title', 'Reporte - Uso de Flotilla por Periodo')

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.reportes') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Uso de Flotilla</li>
    </ol>
</nav>

<div class="card card-outline card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
    </div>
    <form action="{{ route('admin.reportes.uso-flotilla') }}" method="GET">
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
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@if($startDate && $endDate)

@php
    $lista       = collect($reporte);
    $totalViajes = 0;
    $totalKm     = 0;
    foreach ($lista as $item) {
        $r = is_array($item) ? $item : (array) $item;
        $totalViajes += (int)   ($r['total_trips']      ?? 0);
        $totalKm     += (float) ($r['total_kilometers'] ?? 0);
    }
@endphp

@if($lista->isNotEmpty())
<div class="row mb-4">
    <div class="col-md-4">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-car"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Vehiculos activos</span>
                <span class="info-box-number">{{ $lista->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-road"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total viajes</span>
                <span class="info-box-number">{{ $totalViajes }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-tachometer-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total kilometros</span>
                <span class="info-box-number">{{ number_format($totalKm) }} km</span>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-bar mr-2"></i>
            Uso del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </h3>
        <div class="card-tools">
            <span class="badge badge-secondary">{{ $lista->count() }} vehiculo(s)</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($lista->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-road fa-3x mb-3 d-block"></i>
                No se registraron viajes en ese periodo.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Placa</th>
                        <th>Vehiculo</th>
                        <th class="text-center">Total Viajes</th>
                        <th class="text-center">Kilometros Recorridos</th>
                        <th>Participacion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lista as $item)
                    @php
                        $r          = is_array($item) ? $item : (array) $item;
                        $kmItem     = (float) ($r['total_kilometers'] ?? 0);
                        $viajesItem = (int)   ($r['total_trips']      ?? 0);
                        $porcentaje = $totalKm > 0 ? round(($kmItem / $totalKm) * 100) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $r['plate'] ?? '-' }}</strong></td>
                        <td>{{ ($r['brand'] ?? '') }} {{ ($r['model'] ?? '') }}</td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ $viajesItem }}</span>
                        </td>
                        <td class="text-center">
                            <strong>{{ number_format($kmItem) }}</strong>
                            <small class="text-muted"> km</small>
                        </td>
                        <td style="min-width:150px">
                            <div class="progress" style="height:16px">
                                <div class="progress-bar bg-primary" style="width:{{ $porcentaje }}%">
                                    {{ $porcentaje }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="thead-light">
                    <tr>
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td class="text-center"><strong>{{ $totalViajes }}</strong></td>
                        <td class="text-center"><strong>{{ number_format($totalKm) }} km</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>
@endif

@endsection