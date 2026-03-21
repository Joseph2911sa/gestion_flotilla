@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')

{{-- Cabecera --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <small class="text-muted">
            Total en esta página: <strong>{{ count($usuarios) }}</strong>
            @if($rolFiltro)
                &nbsp;·&nbsp; Rol: <span class="badge badge-info">{{ $rolFiltro }}</span>
                <a href="{{ route('admin.usuarios') }}" class="ml-1 small">
                    <i class="fas fa-times-circle"></i> Quitar filtro
                </a>
            @endif
        </small>
    </div>
    <a href="{{ route('admin.usuarios.crear') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus mr-1"></i> Nuevo Usuario
    </a>
</div>

{{-- Filtro por rol --}}
<div class="mb-3">
    <div class="btn-group btn-group-sm">
        <a href="{{ route('admin.usuarios') }}"
           class="btn {{ !$rolFiltro ? 'btn-secondary' : 'btn-outline-secondary' }}">
            Todos
        </a>
        @foreach(['Admin' => 'primary', 'Operador' => 'warning', 'Chofer' => 'success'] as $rol => $color)
            <a href="{{ route('admin.usuarios') }}?rol={{ $rol }}"
               class="btn {{ $rolFiltro === $rol ? "btn-$color" : "btn-outline-$color" }}">
                {{ $rol }}
            </a>
        @endforeach
    </div>
</div>

{{-- Tabla --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Listado de Usuarios</h3>
        <div class="card-tools">
            <span class="badge badge-secondary">
                Página {{ $paginado['current_page'] }} de {{ $paginado['last_page'] }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        @if(count($usuarios) === 0)
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 d-block opacity-50"></i>
                No se encontraron usuarios{{ $rolFiltro ? " con rol \"$rolFiltro\"" : '' }}.
            </div>
        @else
        <div class="table-responsive">
            <table id="tablaUsuarios" class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="60">#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-center" width="120">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $u)
                    @php
                        $rolNombre  = $u['role']['name'] ?? '—';
                        $eliminado  = !empty($u['deleted_at']);
                        $badgeColor = match($rolNombre) {
                            'Admin'    => 'primary',
                            'Operador' => 'warning',
                            'Chofer'   => 'success',
                            default    => 'secondary',
                        };
                    @endphp
                    <tr class="{{ $eliminado ? 'text-muted' : '' }}">
                        <td><small class="text-muted">#{{ $u['id'] }}</small></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-badge bg-{{ $badgeColor }} mr-2">
                                    {{ strtoupper(mb_substr($u['name'], 0, 1)) }}
                                </div>
                                <span>{{ $u['name'] }}</span>
                            </div>
                        </td>
                        <td>{{ $u['email'] }}</td>
                        <td>{{ $u['phone'] ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $badgeColor }}">{{ $rolNombre }}</span>
                        </td>
                        <td>
                            @if($eliminado)
                                <span class="badge badge-danger">
                                    <i class="fas fa-ban mr-1"></i>Eliminado
                                </span>
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-check mr-1"></i>Activo
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!$eliminado)
                                <a href="{{ route('admin.usuarios.editar', $u['id']) }}"
                                   class="btn btn-xs btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-xs btn-danger btn-eliminar"
                                        data-id="{{ $u['id'] }}"
                                        data-nombre="{{ $u['name'] }}"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <span class="text-muted small">
                                    <i class="fas fa-lock"></i> Borrado
                                </span>
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
                           href="{{ route('admin.usuarios') }}?page={{ $paginado['current_page'] - 1 }}&rol={{ $rolFiltro }}">
                            «
                        </a>
                    </li>
                @endif

                @for($p = 1; $p <= $paginado['last_page']; $p++)
                    <li class="page-item {{ $p === $paginado['current_page'] ? 'active' : '' }}">
                        <a class="page-link"
                           href="{{ route('admin.usuarios') }}?page={{ $p }}&rol={{ $rolFiltro }}">
                            {{ $p }}
                        </a>
                    </li>
                @endfor

                @if($paginado['current_page'] < $paginado['last_page'])
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ route('admin.usuarios') }}?page={{ $paginado['current_page'] + 1 }}&rol={{ $rolFiltro }}">
                            »
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        @endif
        @endif
    </div>
</div>

{{-- Modal confirmación eliminación --}}
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
                <p>¿Eliminar al usuario <strong id="nombreEliminar"></strong>?</p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Aplica borrado lógico. El registro se conserva en la base de datos.
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
    .user-badge {
        width: 30px; height: 30px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    // Abrir modal de eliminación
    $(document).on('click', '.btn-eliminar', function () {
        $('#nombreEliminar').text($(this).data('nombre'));
        $('#formEliminar').attr('action', '/admin/usuarios/' + $(this).data('id'));
        $('#modalEliminar').modal('show');
    });
});
</script>
@endpush