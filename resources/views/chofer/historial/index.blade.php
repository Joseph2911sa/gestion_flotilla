@extends('layouts.app')

@section('title', 'Mi Historial')
@section('page-title', 'Mi Historial de Solicitudes')

@section('content')

@php $lista = collect($solicitudes); @endphp

@if($lista->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        No tienes solicitudes registradas aún.
        <a href="{{ route('chofer.solicitudes') }}" class="alert-link ml-2">Crear una solicitud</a>
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
                        @foreach($lista as $solicitud)
                        @php $s = is_array($solicitud) ? $solicitud : $solicitud->toArray(); @endphp
                        <tr>
                            <td>{{ $s['id'] }}</td>
                            <td>
                                @if(!empty($s['vehicle']))
                                    <i class="fas fa-car mr-1 text-primary"></i>
                                    {{ $s['vehicle']['brand'] }} {{ $s['vehicle']['model'] }}
                                    <br>
                                    <small class="text-muted">{{ $s['vehicle']['plate'] }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($s['departure_date'])->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($s['return_date'])->format('d/m/Y H:i') }}</small>
                            </td>
                            <td><small>{{ $s['reason'] ?? '—' }}</small></td>
                            <td>
                                {{ $s['reviewer']['name'] ?? '—' }}
                            </td>
                            <td>
                                @php
                                    $badges = [
                                        'pending'   => ['warning',   'clock',            'Pendiente'],
                                        'approved'  => ['success',   'check',            'Aprobada'],
                                        'rejected'  => ['danger',    'times',            'Rechazada'],
                                        'cancelled' => ['secondary', 'ban',              'Cancelada'],
                                        'completed' => ['info',      'flag-checkered',   'Completada'],
                                    ];
                                    $b = $badges[$s['status']] ?? ['light', 'question', $s['status']];
                                @endphp
                                <span class="badge badge-{{ $b[0] }}">
                                    <i class="fas fa-{{ $b[1] }} mr-1"></i>{{ $b[2] }}
                                </span>
                            </td>
                            <td>
                                @if(in_array($s['status'], ['pending', 'approved']))
                                    <form action="{{ route('chofer.historial.cancelar', $s['id']) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Cancelar esta solicitud?')">
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

        {{-- Paginación manual --}}
        @if(isset($paginado) && !empty($paginado['last_page']) && $paginado['last_page'] > 1)
        <div class="card-footer clearfix">
            <ul class="pagination pagination-sm m-0 float-right">
                @if($paginado['current_page'] > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('chofer.historial') }}?page={{ $paginado['current_page']-1 }}">«</a>
                    </li>
                @endif
                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="{{ route('chofer.historial') }}?page={{ $p }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link" href="{{ route('chofer.historial') }}?page={{ $paginado['current_page']+1 }}">»</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
    </div>
@endif

@endsection