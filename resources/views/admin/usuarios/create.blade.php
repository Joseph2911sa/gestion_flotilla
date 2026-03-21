    @extends('layouts.app')

@section('title', 'Crear Usuario')
@section('page-title', 'Crear Usuario')

@section('content')
<div class="row">
    <div class="col-lg-7">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.usuarios') }}">Usuarios</a></li>
                <li class="breadcrumb-item active">Crear</li>
            </ol>
        </nav>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Nuevo Usuario</h3>
            </div>

            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                <div class="card-body">

                    {{-- Nombre --}}
                    <div class="form-group">
                        <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Ej: Juan Pérez Rodríguez"
                               maxlength="150" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Correo --}}
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="usuario@empresa.com"
                               maxlength="255">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-group">
                        <label for="phone">Teléfono <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="phone" name="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}"
                               placeholder="Ej: 8888-0000" maxlength="20">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Rol --}}
                    <div class="form-group">
                        <label for="role_id">Rol <span class="text-danger">*</span></label>
                        <select id="role_id" name="role_id"
                                class="form-control @error('role_id') is-invalid @enderror">
                            <option value="">— Seleccione un rol —</option>
                            <option value="1" {{ old('role_id') == '1' ? 'selected' : '' }}>Administrador</option>
                            <option value="2" {{ old('role_id') == '2' ? 'selected' : '' }}>Operador / Encargado</option>
                            <option value="3" {{ old('role_id') == '3' ? 'selected' : '' }}>Chofer</option>
                        </select>
                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Info accesos del rol --}}
                    <div id="rolInfo" class="callout callout-info py-2 px-3 d-none mb-3 small">
                        <div id="rolInfoTexto"></div>
                    </div>

                    <hr>

                    {{-- Contraseña --}}
                    <div class="form-group">
                        <label for="password">Contraseña <span class="text-danger">*</span></label>
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
                        {{-- Barra de seguridad --}}
                        <div id="strengthWrap" class="mt-2" style="display:none">
                            <div class="progress" style="height:4px">
                                <div id="strengthBar" class="progress-bar" style="width:0%"></div>
                            </div>
                            <small id="strengthLabel" class="text-muted"></small>
                        </div>
                    </div>

                    {{-- Confirmar contraseña --}}
                    <div class="form-group mb-0">
                        <label for="password_confirmation">
                            Confirmar Contraseña <span class="text-danger">*</span>
                        </label>
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
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel lateral: accesos del rol --}}
    <div class="col-lg-5">
        <div class="card card-outline card-secondary" id="cardAccesos" style="display:none">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key mr-2"></i>Accesos del rol</h3>
            </div>
            <div class="card-body p-0">
                <ul id="listaAccesos" class="list-group list-group-flush"></ul>
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

    // ── Fortaleza de contraseña ──────────────────────────────────────────────
    $('#password').on('input', function () {
        const v = $(this).val();
        $('#strengthWrap').toggle(v.length > 0);
        let s = 0;
        if (v.length >= 6)          s++;
        if (v.length >= 10)         s++;
        if (/[A-Z]/.test(v))        s++;
        if (/[0-9]/.test(v))        s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        const lvl = [
            {l:'Muy débil', c:'bg-danger',  p:20},
            {l:'Débil',     c:'bg-warning', p:40},
            {l:'Regular',   c:'bg-info',    p:60},
            {l:'Fuerte',    c:'bg-primary', p:80},
            {l:'Muy fuerte',c:'bg-success', p:100},
        ][Math.min(s, 4)];
        $('#strengthBar').attr('class', 'progress-bar ' + lvl.c).css('width', lvl.p + '%');
        $('#strengthLabel').text(lvl.l);
        checkMatch();
    });

    // ── Coincidencia contraseñas ─────────────────────────────────────────────
    $('#password_confirmation').on('input', checkMatch);
    function checkMatch() {
        const p1 = $('#password').val(), p2 = $('#password_confirmation').val();
        if (!p2) { $('#matchMsg').html(''); return; }
        $('#matchMsg').html(p1 === p2
            ? '<span class="text-success"><i class="fas fa-check mr-1"></i>Coinciden</span>'
            : '<span class="text-danger"><i class="fas fa-times mr-1"></i>No coinciden</span>');
    }

    // ── Info de accesos por rol ──────────────────────────────────────────────
    const ACCESOS = {
        1: ['Gestión completa de usuarios', 'Gestión de vehículos', 'Mantenimientos', 'Reportes', 'Rutas', 'Acceso total'],
        2: ['Ver y gestionar solicitudes', 'Aprobar / rechazar solicitudes', 'Asignación directa', 'Registro de viajes', 'Rutas y mantenimientos'],
        3: ['Ver vehículos disponibles', 'Crear solicitud de vehículo', 'Cancelar solicitud propia', 'Ver historial personal'],
    };

    $('#role_id').on('change', function () {
        const id = $(this).val();
        if (!id) { $('#cardAccesos').hide(); return; }
        $('#listaAccesos').html((ACCESOS[id] || []).map(a =>
            `<li class="list-group-item py-2 small">
                <i class="fas fa-check-circle text-success mr-2"></i>${a}
            </li>`
        ).join(''));
        $('#cardAccesos').show();
    });

    if ($('#role_id').val()) $('#role_id').trigger('change');
});
</script>
@endpush