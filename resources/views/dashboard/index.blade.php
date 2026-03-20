@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php $role = session('user')['role']['name'] ?? ''; @endphp

<div class="row">
    @if($role === 'Admin')
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>Usuarios</h3><p>Gestionar usuarios del sistema</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.usuarios') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>Vehículos</h3><p>Gestionar flotilla</p></div>
                <div class="icon"><i class="fas fa-car"></i></div>
                <a href="{{ route('admin.vehiculos') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>Mantenimientos</h3><p>Control de mantenimientos</p></div>
                <div class="icon"><i class="fas fa-tools"></i></div>
                <a href="{{ route('admin.mantenimientos') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>Reportes</h3><p>Reportes del sistema</p></div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
                <a href="{{ route('admin.reportes') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

    @elseif($role === 'Operador')
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>Solicitudes</h3><p>Pendientes de revisión</p></div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <a href="{{ route('operador.solicitudes') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>Viajes</h3><p>Gestionar viajes activos</p></div>
                <div class="icon"><i class="fas fa-road"></i></div>
                <a href="{{ route('operador.viajes') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>Mantenimientos</h3><p>Registrar mantenimientos</p></div>
                <div class="icon"><i class="fas fa-tools"></i></div>
                <a href="{{ route('operador.mantenimientos') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

    @elseif($role === 'Chofer')
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>Vehículos</h3><p>Ver vehículos disponibles</p></div>
                <div class="icon"><i class="fas fa-car-side"></i></div>
                <a href="{{ route('chofer.vehiculos') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>Nueva Solicitud</h3><p>Solicitar un vehículo</p></div>
                <div class="icon"><i class="fas fa-plus-circle"></i></div>
                <a href="{{ route('chofer.solicitudes') }}" class="small-box-footer">Solicitar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>Mi Historial</h3><p>Ver mis solicitudes y viajes</p></div>
                <div class="icon"><i class="fas fa-history"></i></div>
                <a href="{{ route('chofer.historial') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    @endif
</div>
@endsection