@extends('layouts.app')

@section('title', 'Mi Historial')
@section('page-title', 'Mi Historial de Solicitudes')

@section('content')

@if($solicitudes->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        No tienes solicitudes registradas aún.
        <a href="{{ route('chofer.solicitudes') }}" class="alert-link ml-2">
            Crear una solicitud
        </a>
    </div>
@else
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-2"></i>Mis Solicitudes
            </h3>
            <div class="card-tools">
                <a href="{{ route('chofer.solicitudes') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>Nueva Solicitud
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Vehículo</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Motivo</th>
                            <th>Revisado por</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitudes as $solicitud)
                        <tr>
                            <td>{{ $solicitud->id }}</td>
                            <td>
                                @if($solicitud->vehicle)
                                    <i class="fas fa-car mr-1 text-primary"></i>
                                    {{ $solicitud->vehicle->brand }}
                                    {{ $solicitud->vehicle->model }}
                                    <br>
                                    <small class="text-muted">{{ $solicitud->vehicle->plate }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-calendar mr-1 text-success"></i>
                                {{ \Carbon\Carbon::parse($solicitud->departure_date)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <i class="fas fa-calendar-check mr-1 text-danger"></i>
                                {{ \Carbon\Carbon::parse($solicitud->return_date)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                {{ $solicitud->reason ?? '—' }}
                            </td>
                            <td>
                                {{ $solicitud->reviewer->name ?? '—' }}
                            </td>
                            <td>
                                @switch($solicitud->status)
                                    @case('pending')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock mr-1"></i>Pendiente
                                        </span>
                                        @break
                                    @case('approved')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>Aprobada
                                        </span>
                                        @break
                                    @case('rejected')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times mr-1"></i>Rechazada
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-ban mr-1"></i>Cancelada
                                        </span>
                                        @break
                                    @case('completed')
                                        <span class="badge badge-info">
                                            <i class="fas fa-flag-checkered mr-1"></i>Completada
                                        </span>
                                        @break
                                    @default
                                        <span class="badge badge-light">{{ $solicitud->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if(in_array($solicitud->status, ['pending', 'approved']))
                                    <form action="{{ route('chofer.historial.cancelar', $solicitud->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Estás seguro de cancelar esta solicitud?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-ban mr-1"></i>Cancelar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $solicitudes->links() }}
        </div>
    </div>
@endif

@endsection