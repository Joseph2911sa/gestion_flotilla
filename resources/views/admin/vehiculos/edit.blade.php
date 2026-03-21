@extends('layouts.app')

@section('title', 'Editar Vehículo')
@section('page-title', 'Editar Vehículo')

@section('content')
<div class="row">
    <div class="col-lg-8">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.vehiculos') }}">Vehículos</a></li>
                <li class="breadcrumb-item active">Editar: {{ $vehiculo['plate'] }}</li>
            </ol>
        </nav>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-car mr-2"></i>Editando: {{ $vehiculo['plate'] }}
                </h3>
                <div class="card-tools">
                    @php
                    $statusLabels = [
                        'available'      => ['label'=>'Disponible',       'badge'=>'success'],
                        'in_use'         => ['label'=>'En uso',            'badge'=>'primary'],
                        'maintenance'    => ['label'=>'Mantenimiento',     'badge'=>'warning'],
                        'out_of_service' => ['label'=>'Fuera de servicio', 'badge'=>'danger'],
                    ];
                    $info = $statusLabels[$vehiculo['status']] ?? ['label'=>$vehiculo['status'],'badge'=>'secondary'];
                    @endphp
                    <span class="badge badge-{{ $info['badge'] }}">{{ $info['label'] }}</span>
                </div>
            </div>

            <form action="{{ route('admin.vehiculos.update', $vehiculo['id']) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                                       value="{{ old('plate', $vehiculo['plate']) }}"
                                       maxlength="20">
                                @error('plate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="brand">Marca <span class="text-danger">*</span></label>
                                <input type="text" id="brand" name="brand"
                                       class="form-control @error('brand') is-invalid @enderror"
                                       value="{{ old('brand', $vehiculo['brand']) }}"
                                       maxlength="100">
                                @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="model">Modelo <span class="text-danger">*</span></label>
                                <input type="text" id="model" name="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model', $vehiculo['model']) }}"
                                       maxlength="100">
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
                                       value="{{ old('year', $vehiculo['year']) }}"
                                       min="1900" max="{{ date('Y') + 1 }}">
                                @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="vehicle_type">Tipo <span class="text-danger">*</span></label>
                                <select id="vehicle_type" name="vehicle_type"
                                        class="form-control @error('vehicle_type') is-invalid @enderror">
                                    @foreach($tiposVehiculo as $tipo)
                                        <option value="{{ $tipo }}"
                                            {{ old('vehicle_type', $vehiculo['vehicle_type']) === $tipo ? 'selected' : '' }}>
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
                                           value="{{ old('capacity', $vehiculo['capacity']) }}"
                                           min="1" max="100">
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
                                    @foreach($tiposCombustible as $comb)
                                        <option value="{{ $comb }}"
                                            {{ old('fuel_type', $vehiculo['fuel_type']) === $comb ? 'selected' : '' }}>
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
                                <label for="status">Estado <span class="text-danger">*</span></label>
                                <select id="status" name="status"
                                        class="form-control @error('status') is-invalid @enderror">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s }}"
                                            {{ old('status', $vehiculo['status']) === $s ? 'selected' : '' }}>
                                            {{ $statusLabels[$s]['label'] ?? $s }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kilometraje actual</label>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control"
                                           value="{{ number_format($vehiculo['mileage'] ?? 0) }} km"
                                           readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Se actualiza automáticamente al registrar devoluciones.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- ── IMAGEN ───────────────────────────────────────── --}}
                    <h6 class="text-muted text-uppercase font-weight-bold mb-3 mt-2">
                        <i class="fas fa-image mr-1"></i> Imagen del vehículo
                    </h6>

                    <div class="row">
                        <div class="col-md-4">
                            <label>Imagen actual:</label>
                            @if($vehiculo['image_path'])
                                <div>
                                    <img src="{{ asset('storage/' . $vehiculo['image_path']) }}"
                                         alt="{{ $vehiculo['plate'] }}"
                                         style="max-width:100%;max-height:150px;object-fit:contain;
                                                border:1px solid #dee2e6;border-radius:4px;padding:4px">
                                </div>
                            @else
                                <div class="text-center text-muted py-3"
                                     style="border:2px dashed #dee2e6;border-radius:4px">
                                    <i class="fas fa-image fa-2x mb-1 d-block"></i>
                                    <small>Sin imagen</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="image">
                                    {{ $vehiculo['image_path'] ? 'Reemplazar imagen' : 'Subir imagen' }}
                                    <small class="text-muted">(opcional, máx. 2MB)</small>
                                </label>
                                <div class="custom-file">
                                    <input type="file"
                                           class="custom-file-input @error('image') is-invalid @enderror"
                                           id="image" name="image"
                                           accept="image/jpg,image/jpeg,image/png,image/webp">
                                    <label class="custom-file-label" for="image">Seleccionar imagen...</label>
                                </div>
                                @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div id="previewWrap" style="display:none">
                                <label>Nueva imagen (vista previa):</label>
                                <img id="imgPreview" src="" alt="Preview"
                                     style="max-width:100%;max-height:120px;object-fit:contain;
                                            border:1px solid #dee2e6;border-radius:4px;padding:4px">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.vehiculos') }}" class="btn btn-secondary">
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
    <div class="col-lg-4">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" style="width:45%">ID</td>
                        <td><code>#{{ $vehiculo['id'] }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Placa</td>
                        <td><strong>{{ $vehiculo['plate'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Estado</td>
                        <td><span class="badge badge-{{ $info['badge'] }}">{{ $info['label'] }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kilometraje</td>
                        <td>{{ number_format($vehiculo['mileage'] ?? 0) }} km</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Creado</td>
                        <td>
                            <small>
                                {{ isset($vehiculo['created_at'])
                                    ? \Carbon\Carbon::parse($vehiculo['created_at'])->format('d/m/Y H:i')
                                    : '—' }}
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Actualizado</td>
                        <td>
                            <small>
                                {{ isset($vehiculo['updated_at'])
                                    ? \Carbon\Carbon::parse($vehiculo['updated_at'])->format('d/m/Y H:i')
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
    // Placa a mayúsculas
    $('#plate').on('input', function () {
        this.value = this.value.toUpperCase();
    });

    // Preview nueva imagen
    $('#image').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        $('.custom-file-label').text(file.name);
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#imgPreview').attr('src', e.target.result);
            $('#previewWrap').show();
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush