@extends('layouts.app')

@section('title', 'Nueva Solicitud')
@section('page-title', 'Nueva Solicitud de Vehículo')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle mr-2"></i>Crear Solicitud
                </h3>
            </div>
            <div class="card-body">

                {{-- Errores --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <form action="{{ route('chofer.solicitudes.store') }}" method="POST">
                    @csrf

                    {{-- Vehículo --}}
                    <div class="form-group">
                        <label for="vehicle_id">
                            <i class="fas fa-car mr-1"></i>Vehículo <span class="text-danger">*</span>
                        </label>
                        <select name="vehicle_id"
                                id="vehicle_id"
                                class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">-- Seleccione un vehículo --</option>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}"
                                    {{ old('vehicle_id', request('vehicle_id')) == $vehiculo->id ? 'selected' : '' }}>
                                    {{ $vehiculo->brand }} {{ $vehiculo->model }} — {{ $vehiculo->plate }}
                                    ({{ $vehiculo->vehicle_type }}, {{ $vehiculo->capacity }} personas)
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if($vehiculos->isEmpty())
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                No hay vehículos disponibles en este momento.
                            </small>
                        @endif
                    </div>

                    {{-- Ruta (opcional) --}}
                    <div class="form-group">
                        <label for="route_id">
                            <i class="fas fa-route mr-1"></i>Ruta <span class="text-muted">(opcional)</span>
                        </label>
                        <select name="route_id" id="route_id" class="form-control">
                            <option value="">-- Sin ruta específica --</option>
                            @foreach(\App\Models\Route::all() as $ruta)
                                <option value="{{ $ruta->id }}"
                                    {{ old('route_id') == $ruta->id ? 'selected' : '' }}>
                                    {{ $ruta->name }} ({{ $ruta->origin }} → {{ $ruta->destination }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fecha inicio --}}
                    <div class="form-group">
                        <label for="departure_date">
                            <i class="fas fa-calendar-alt mr-1"></i>Fecha y hora de inicio <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local"
                               name="departure_date"
                               id="departure_date"
                               class="form-control @error('departure_date') is-invalid @enderror"
                               value="{{ old('departure_date') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('departure_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Fecha fin --}}
                    <div class="form-group">
                        <label for="return_date">
                            <i class="fas fa-calendar-check mr-1"></i>Fecha y hora de fin <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local"
                               name="return_date"
                               id="return_date"
                               class="form-control @error('return_date') is-invalid @enderror"
                               value="{{ old('return_date') }}">
                        @error('return_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Motivo --}}
                    <div class="form-group">
                        <label for="reason">
                            <i class="fas fa-comment mr-1"></i>Motivo <span class="text-muted">(opcional)</span>
                        </label>
                        <textarea name="reason"
                                  id="reason"
                                  rows="3"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="Describa el motivo del viaje...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane mr-2"></i>Enviar Solicitud
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('chofer.vehiculos') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left mr-2"></i>Volver al catálogo
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        {{-- Info de estados --}}
        <div class="card card-outline card-info mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>Información
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-1">Tu solicitud quedará en estado <span class="badge badge-warning">Pendiente</span> hasta que un operador la revise.</p>
                <p class="mb-1">Puedes ver el estado de tus solicitudes en <strong>Mi Historial</strong>.</p>
                <p class="mb-0">Solo puedes cancelar solicitudes en estado <span class="badge badge-warning">Pendiente</span> o <span class="badge badge-success">Aprobada</span>.</p>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('departure_date').addEventListener('change', function() {
        const inicio = this.value;
        const finInput = document.getElementById('return_date');
        finInput.min = inicio;
        if (finInput.value && finInput.value <= inicio) {
            finInput.value = '';
        }
    });
</script>
@endpush