@extends('layouts.app')

@section('title', 'Crear Vehículo')
@section('page-title', 'Crear Vehículo')

@section('content')
<div class="row">
    <div class="col-lg-8">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.vehiculos') }}">Vehículos</a></li>
                <li class="breadcrumb-item active">Crear</li>
            </ol>
        </nav>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-car mr-2"></i>Nuevo Vehículo</h3>
            </div>

            <form action="{{ route('admin.vehiculos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">

                    {{-- ── IDENTIFICACIÓN ──────────────────────────────── --}}
                    <h6 class="text-muted text-uppercase font-weight-bold mb-3">
                        <i class="fas fa-id-card mr-1"></i> Identificación
                    </h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="plate">Placa <span class="text-danger">*</span></label>
                                <input type="text" id="plate" name="plate"
                                       class="form-control text-uppercase @error('plate') is-invalid @enderror"
                                       value="{{ old('plate') }}"
                                       placeholder="Ej: ABC-123" maxlength="20" autofocus>
                                @error('plate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="brand">Marca <span class="text-danger">*</span></label>
                                <input type="text" id="brand" name="brand"
                                       class="form-control @error('brand') is-invalid @enderror"
                                       value="{{ old('brand') }}"
                                       placeholder="Ej: Toyota" maxlength="100">
                                @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="model">Modelo <span class="text-danger">*</span></label>
                                <input type="text" id="model" name="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model') }}"
                                       placeholder="Ej: Hilux" maxlength="100">
                                @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── ESPECIFICACIONES ─────────────────────────────── --}}
                    <h6 class="text-muted text-uppercase font-weight-bold mb-3 mt-2">
                        <i class="fas fa-cogs mr-1"></i> Especificaciones
                    </h6>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="year">Año <span class="text-danger">*</span></label>
                                <input type="number" id="year" name="year"
                                       class="form-control @error('year') is-invalid @enderror"
                                       value="{{ old('year', date('Y')) }}"
                                       min="1900" max="{{ date('Y') + 1 }}">
                                @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="vehicle_type">Tipo <span class="text-danger">*</span></label>
                                <select id="vehicle_type" name="vehicle_type"
                                        class="form-control @error('vehicle_type') is-invalid @enderror">
                                    <option value="">— Seleccione —</option>
                                    @foreach($tiposVehiculo as $tipo)
                                        <option value="{{ $tipo }}" {{ old('vehicle_type') === $tipo ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="capacity">Capacidad <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="capacity" name="capacity"
                                           class="form-control @error('capacity') is-invalid @enderror"
                                           value="{{ old('capacity', 5) }}" min="1" max="100">
                                    <div class="input-group-append">
                                        <span class="input-group-text">pers.</span>
                                    </div>
                                </div>
                                @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fuel_type">Combustible <span class="text-danger">*</span></label>
                                <select id="fuel_type" name="fuel_type"
                                        class="form-control @error('fuel_type') is-invalid @enderror">
                                    <option value="">— Seleccione —</option>
                                    @foreach($tiposCombustible as $comb)
                                        <option value="{{ $comb }}" {{ old('fuel_type') === $comb ? 'selected' : '' }}>
                                            {{ $comb }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fuel_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="mileage">Kilometraje inicial</label>
                                <div class="input-group">
                                    <input type="number" id="mileage" name="mileage"
                                           class="form-control @error('mileage') is-invalid @enderror"
                                           value="{{ old('mileage', 0) }}" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">km</span>
                                    </div>
                                </div>
                                @error('mileage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Estado inicial</label>
                                <select id="status" name="status"
                                        class="form-control @error('status') is-invalid @enderror">
                                    @php
                                    $statusLabels = [
                                        'available'      => 'Disponible',
                                        'in_use'         => 'En uso',
                                        'maintenance'    => 'En mantenimiento',
                                        'out_of_service' => 'Fuera de servicio',
                                    ];
                                    @endphp
                                    @foreach($statuses as $s)
                                        <option value="{{ $s }}" {{ old('status', 'available') === $s ? 'selected' : '' }}>
                                            {{ $statusLabels[$s] ?? $s }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── IMAGEN ───────────────────────────────────────── --}}
                    <h6 class="text-muted text-uppercase font-weight-bold mb-3 mt-2">
                        <i class="fas fa-image mr-1"></i> Imagen del vehículo
                    </h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image">Subir imagen <small class="text-muted">(opcional, máx. 2MB)</small></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
                                           id="image" name="image" accept="image/jpg,image/jpeg,image/png,image/webp">
                                    <label class="custom-file-label" for="image">Seleccionar imagen...</label>
                                </div>
                                @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <small class="text-muted">Formatos: jpg, jpeg, png, webp</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="previewWrap" style="display:none">
                                <label>Vista previa:</label>
                                <img id="imgPreview" src="" alt="Preview"
                                     style="max-width:100%;max-height:150px;object-fit:contain;
                                            border:1px solid #dee2e6;border-radius:4px;padding:4px">
                            </div>
                            <div id="noPreview" class="text-center text-muted py-3"
                                 style="border:2px dashed #dee2e6;border-radius:4px">
                                <i class="fas fa-image fa-2x mb-1 d-block"></i>
                                <small>Sin imagen seleccionada</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.vehiculos') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Registrar Vehículo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Placa a mayúsculas automático
    $('#plate').on('input', function () {
        this.value = this.value.toUpperCase();
    });

    // Preview de imagen
    $('#image').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        $('.custom-file-label').text(file.name);
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#imgPreview').attr('src', e.target.result);
            $('#previewWrap').show();
            $('#noPreview').hide();
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush