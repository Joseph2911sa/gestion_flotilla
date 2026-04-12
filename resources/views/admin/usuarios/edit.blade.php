@extends('layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('content')
<div class="row">
    <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.usuarios') }}">Usuarios</a></li>
                <li class="breadcrumb-item active">Editar: {{ $usuario['name'] }}</li>
            </ol>
        </nav>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-2"></i>Editando usuario
                </h3>
                <div class="card-tools">
                    @php
                        $rolActual = $usuario['role']['name'] ?? '—';
                        $colores   = ['Admin' => 'primary', 'Operador' => 'warning', 'Chofer' => 'success'];
                        $color     = $colores[$rolActual] ?? 'secondary';
                    @endphp
                    <span class="badge badge-{{ $color }}">{{ $rolActual }}</span>
                </div>
            </div>

            <form action="{{ route('admin.usuarios.update', $usuario['id']) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">

                    {{-- Nombre --}}
                    <div class="form-group">
                        <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $usuario['name']) }}"
                               maxlength="150" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Correo --}}
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $usuario['email']) }}"
                               maxlength="255">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-group">
                        <label for="phone">Teléfono <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="phone" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $usuario['phone'] ?? '') }}"
                               maxlength="20">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Rol --}}
                    <div class="form-group">
                        <label for="role_id">Rol <span class="text-danger">*</span></label>
                        <select id="role_id" name="role_id"
                                class="form-control @error('role_id') is-invalid @enderror">
                            <option value="">— Seleccione un rol —</option>
                            @php $roleIdActual = old('role_id', $usuario['role']['id'] ?? ''); @endphp
                            <option value="1" {{ $roleIdActual == 1 ? 'selected' : '' }}>Administrador</option>
                            <option value="2" {{ $roleIdActual == 2 ? 'selected' : '' }}>Operador / Encargado</option>
                            <option value="3" {{ $roleIdActual == 3 ? 'selected' : '' }}>Chofer</option>
                        </select>
                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr>

                    {{-- Cambio de contraseña --}}
                    <div class="form-group mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">Contraseña</label>
                            <button type="button" id="btnTogglePass"
                                    class="btn btn-xs btn-outline-secondary">
                                <i class="fas fa-lock mr-1"></i>Cambiar contraseña
                            </button>
                        </div>

                        <div id="passSection" style="display:none">
                            <div class="callout callout-warning py-2 px-3 mb-3 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                Deja en blanco para conservar la contraseña actual.
                            </div>

                            <div class="form-group">
                                <label for="password">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Mínimo 6 caracteres">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                                data-target="password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-0">
                                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                           class="form-control"
                                           placeholder="Repite la contraseña">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                                data-target="password_confirmation">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="matchMsg" class="small mt-1"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel metadatos --}}
    <div class="col-lg-5">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información del registro</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">ID</td>
                        <td><code>#{{ $usuario['id'] }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Estado</td>
                        <td>
                            @if(!empty($usuario['deleted_at']))
                                <span class="badge badge-danger">Eliminado (borrado lógico)</span>
                            @else
                                <span class="badge badge-success">Activo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Creado</td>
                        <td>
                            <small>
                                {{ isset($usuario['created_at'])
                                    ? \Carbon\Carbon::parse($usuario['created_at'])->subHours(6)->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Actualizado</td>
                        <td>
                            <small>
                                {{ isset($usuario['updated_at'])
                                    ? \Carbon\Carbon::parse($usuario['updated_at'])->subHours(6)->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    // ── Toggle mostrar/ocultar contraseña ────────────────────────────────────
    $(document).on('click', '.toggle-pwd', function () {
        const inp = $('#' + $(this).data('target'));
        inp.attr('type', inp.attr('type') === 'password' ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ── Sección contraseña colapsable ────────────────────────────────────────
    let passOpen = false;

    // Si hubo error en contraseña tras submit, abrir automáticamente
    const tieneErrorPass = {{ $errors->has('password') || $errors->has('password_confirmation') ? 'true' : 'false' }};
    if (tieneErrorPass) {
        passOpen = true;
        $('#passSection').show();
        $('#btnTogglePass').html('<i class="fas fa-lock-open mr-1"></i>Cancelar cambio');
    }

    $('#btnTogglePass').on('click', function () {
        passOpen = !passOpen;
        $('#passSection').slideToggle(200);
        $(this).html(passOpen
            ? '<i class="fas fa-lock-open mr-1"></i>Cancelar cambio'
            : '<i class="fas fa-lock mr-1"></i>Cambiar contraseña');
        if (!passOpen) {
            $('#password, #password_confirmation').val('');
            $('#matchMsg').html('');
        }
    });

    // ── Coincidencia contraseñas ─────────────────────────────────────────────
    $('#password, #password_confirmation').on('input', function () {
        const p1 = $('#password').val(), p2 = $('#password_confirmation').val();
        if (!p2) { $('#matchMsg').html(''); return; }
        $('#matchMsg').html(p1 === p2
            ? '<span class="text-success"><i class="fas fa-check mr-1"></i>Coinciden</span>'
            : '<span class="text-danger"><i class="fas fa-times mr-1"></i>No coinciden</span>');
    });
});
</script>
@endpush