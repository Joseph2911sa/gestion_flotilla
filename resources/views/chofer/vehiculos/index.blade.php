@extends('layouts.app')

@section('title', 'Vehículos Disponibles')
@section('page-title', 'Vehículos Disponibles')

@section('content')

{{-- Filtro por rango de fecha --}}
<div class="card card-outline card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search mr-2"></i>Filtrar por disponibilidad
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('chofer.vehiculos') }}">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label>Fecha y hora de inicio</label>
                    <input type="datetime-local" name="fecha_inicio"
                           class="form-control" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="col-md-4">
                    <label>Fecha y hora de fin</label>
                    <input type="datetime-local" name="fecha_fin"
                           class="form-control" value="{{ request('fecha_fin') }}">
                </div>
                <div class="col-md-4 d-flex">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i>Buscar
                    </button>
                    <a href="{{ route('chofer.vehiculos') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Resultados --}}
@php $lista = collect($vehiculos); @endphp

@if($lista->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No hay vehículos disponibles
        @if(request('fecha_inicio') && request('fecha_fin'))
            para el rango de fechas seleccionado.
        @else
            en este momento.
        @endif
    </div>
@else
    <div class="row">
        @foreach($lista as $vehiculo)
        @php $v = is_array($vehiculo) ? $vehiculo : $vehiculo->toArray(); @endphp
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                {{-- Imagen --}}
                <div style="height:200px;overflow:hidden;background:#f4f6f9;">
                    @if(!empty($v['image_path']))
                        <img src="{{ asset('storage/' . $v['image_path']) }}"
                             alt="{{ $v['brand'] }} {{ $v['model'] }}"
                             class="card-img-top"
                             style="width:100%;height:200px;object-fit:cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <i class="fas fa-car fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>

                {{-- Especificaciones --}}
                <div class="card-body">
                    <h5 class="card-title font-weight-bold">
                        {{ $v['brand'] }} {{ $v['model'] }}
                        <span class="badge badge-success float-right">Disponible</span>
                    </h5>
                    <p class="text-muted mb-1">
                        <i class="fas fa-id-card mr-1"></i>
                        <strong>Placa:</strong> {{ $v['plate'] }}
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-calendar mr-1"></i>
                        <strong>Año:</strong> {{ $v['year'] }}
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-car-side mr-1"></i>
                        <strong>Tipo:</strong> {{ $v['vehicle_type'] }}
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-users mr-1"></i>
                        <strong>Capacidad:</strong> {{ $v['capacity'] }} personas
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-gas-pump mr-1"></i>
                        <strong>Combustible:</strong> {{ $v['fuel_type'] }}
                    </p>
                </div>

                {{-- Botón solicitar --}}
                <div class="card-footer bg-white border-top-0">
                    <a href="{{ route('chofer.solicitudes') }}?vehicle_id={{ $v['id'] }}"
                       class="btn btn-primary btn-block">
                        <i class="fas fa-plus-circle mr-2"></i>Solicitar este vehículo
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Paginación manual --}}
    @if(isset($paginado) && $paginado && $paginado['last_page'] > 1)
    <div class="d-flex justify-content-center">
        <ul class="pagination">
            @if($paginado['current_page'] > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ route('chofer.vehiculos') }}?page={{ $paginado['current_page']-1 }}">«</a>
                </li>
            @endif
            @for($p = 1; $p <= $paginado['last_page']; $p++)
                <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('chofer.vehiculos') }}?page={{ $p }}">{{ $p }}</a>
                </li>
            @endfor
            @if($paginado['current_page'] < $paginado['last_page'])
                <li class="page-item">
                    <a class="page-link" href="{{ route('chofer.vehiculos') }}?page={{ $paginado['current_page']+1 }}">»</a>
                </li>
            @endif
        </ul>
    </div>
    @endif
@endif

@endsection