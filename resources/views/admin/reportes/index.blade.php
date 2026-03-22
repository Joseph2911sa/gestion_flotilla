@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Reportes del Sistema')

@section('content')

<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card card-outline card-success h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-car fa-3x text-success mb-3 d-block"></i>
                <h5 class="font-weight-bold">Disponibilidad de Vehículos</h5>
                <p class="text-muted small mb-4">
                    Lista vehículos disponibles con especificaciones e imagen para un rango de fecha/hora.
                </p>
                <a href="{{ route('admin.reportes.disponibilidad') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-search mr-1"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card card-outline card-primary h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-primary mb-3 d-block"></i>
                <h5 class="font-weight-bold">Uso de Flotilla por Período</h5>
                <p class="text-muted small mb-4">
                    Viajes por vehículo y kilómetros recorridos en un rango de fechas.
                </p>
                <a href="{{ route('admin.reportes.uso-flotilla') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-search mr-1"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card card-outline card-warning h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-user-tie fa-3x text-warning mb-3 d-block"></i>
                <h5 class="font-weight-bold">Historial del Chofer</h5>
                <p class="text-muted small mb-4">
                    Solicitudes y viajes del chofer con estados y vehículo asociado, filtrable por período.
                </p>
                <a href="{{ route('admin.reportes.historial-chofer') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-search mr-1"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

</div>

@endsection