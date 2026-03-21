@extends('layouts.app')

@section('title', 'Vehículos')
@section('page-title', 'Gestión de Vehículos')

@section('content')

@php
$statusLabels = [
    'available'      => ['label' => 'Disponible',     'badge' => 'success'],
    'in_use'         => ['label' => 'En uso',          'badge' => 'primary'],
    'maintenance'    => ['label' => 'Mantenimiento',   'badge' => 'warning'],
    'out_of_service' => ['label' => 'Fuera de servicio','badge' => 'danger'],
];
@endphp

{{-- Cabecera --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <small class="text-muted">
            Total: <strong>{{ $paginado['total'] }}</strong> vehículo(s)
            @if($statusFiltro)
                &nbsp;·&nbsp; Filtro:
                <span class="badge badge-{{ $statusLabels[$statusFiltro]['badge'] ?? 'secondary' }}">
                    {{ $statusLabels[$statusFiltro]['label'] ?? $statusFiltro }}
                </span>
                <a href="{{ route('admin.vehiculos') }}" class="ml-1 small">
                    <i class="fas fa-times-circle"></i> Quitar
                </a>
            @endif
        </small>
    </div>
    <a href="{{ route('admin.vehiculos.crear') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Nuevo Vehículo
    </a>
</div>

{{-- Filtro por estado --}}
<div class="mb-3">
    <div class="btn-group btn-group-sm flex-wrap">
        <a href="{{ route('admin.vehiculos') }}"
           class="btn {{ !$statusFiltro ? 'btn-secondary' : 'btn-outline-secondary' }}">
            Todos
        </a>
        @foreach($statusLabels as $key => $info)
            <a href="{{ route('admin.vehiculos') }}?status={{ $key }}"
               class="btn {{ $statusFiltro === $key ? 'btn-'.$info['badge'] : 'btn-outline-'.$info['badge'] }}">
                {{ $info['label'] }}
            </a>
        @endforeach
    </div>
</div>

{{-- Tabla --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-car mr-2"></i>Listado de Vehículos</h3>
        <div class="card-tools">
            <span class="badge badge-secondary">
                Página {{ $paginado['current_page'] }} de {{ $paginado['last_page'] }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(count($vehiculos) === 0)
            <div class="text-center py-5 text-muted">
                <i class="fas fa-car fa-3x mb-3 d-block"></i>
                No se encontraron vehículos{{ $statusFiltro ? ' con ese estado' : '' }}.
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
                        <th>Kilometraje</th>
                        <th>Estado</th>
                        <th class="text-center" width="110">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehiculos as $v)
                    @php
                        $info    = $statusLabels[$v['status']] ?? ['label'=>$v['status'],'badge'=>'secondary'];
                        $imgUrl  = $v['image_path']
                                    ? asset('storage/' . $v['image_path'])
                                    : null;
                        $eliminado = !empty($v['deleted_at']);
                    @endphp
                    <tr class="{{ $eliminado ? 'text-muted' : '' }}">
                        {{-- Imagen --}}
                        <td>
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $v['plate'] }}"
                                     class="img-thumbnail vehicle-thumb"
                                     data-src="{{ $imgUrl }}"
                                     style="width:55px;height:40px;object-fit:cover;cursor:pointer">
                            @else
                                <div class="vehicle-no-img">
                                    <i class="fas fa-car text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $v['plate'] }}</strong>
                        </td>
                        <td>
                            <div>{{ $v['brand'] }} {{ $v['model'] }}</div>
                            <small class="text-muted">{{ $v['year'] }}</small>
                        </td>
                        <td>{{ $v['vehicle_type'] }}</td>
                        <td>{{ $v['capacity'] }} <small class="text-muted">pers.</small></td>
                        <td>{{ $v['fuel_type'] }}</td>
                        <td>{{ number_format($v['mileage'] ?? 0) }} <small class="text-muted">km</small></td>
                        <td>
                            <span class="badge badge-{{ $info['badge'] }}">
                                {{ $info['label'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if(!$eliminado)
                                <a href="{{ route('admin.vehiculos.editar', $v['id']) }}"
                                   class="btn btn-xs btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-xs btn-danger btn-eliminar"
                                        data-id="{{ $v['id'] }}"
                                        data-placa="{{ $v['plate'] }}"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <span class="text-muted small"><i class="fas fa-lock"></i></span>
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
                           href="{{ route('admin.vehiculos') }}?page={{ $paginado['current_page']-1 }}&status={{ $statusFiltro }}">«</a>
                    </li>
                @endif
                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link"
                           href="{{ route('admin.vehiculos') }}?page={{ $p }}&status={{ $statusFiltro }}">{{ $p }}</a>
                    </li>
                @endfor
                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ route('admin.vehiculos') }}?page={{ $paginado['current_page']+1 }}&status={{ $statusFiltro }}">»</a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal: preview imagen --}}
<div class="modal fade" id="modalImagen" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imagen del vehículo</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="imgPreviewModal" src="" alt="Vehículo"
                     style="max-width:100%;max-height:400px;object-fit:contain">
            </div>
        </div>
    </div>
</div>

{{-- Modal: confirmar eliminación --}}
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar eliminación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar el vehículo con placa <strong id="placaEliminar"></strong>?</p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Borrado lógico — el registro se conserva en la base de datos.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .vehicle-thumb { border-radius: 4px; transition: transform .2s; }
    .vehicle-thumb:hover { transform: scale(1.1); }
    .vehicle-no-img {
        width:55px; height:40px; background:#f4f6f9;
        display:flex; align-items:center; justify-content:center;
        border-radius:4px; border:1px solid #dee2e6;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    // Preview imagen en modal
    $(document).on('click', '.vehicle-thumb', function () {
        $('#imgPreviewModal').attr('src', $(this).data('src'));
        $('#modalImagen').modal('show');
    });

    // Modal eliminar
    $(document).on('click', '.btn-eliminar', function () {
        $('#placaEliminar').text($(this).data('placa'));
        $('#formEliminar').attr('action', '/admin/vehiculos/' + $(this).data('id'));
        $('#modalEliminar').modal('show');
    });
});
</script>
@endpush