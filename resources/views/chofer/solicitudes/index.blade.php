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

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('chofer.solicitudes.store') }}" method="POST">
                    @csrf

                    {{-- Vehículo --}}
                    <div class="form-group">
                        <label for="vehicle_id">
                            <i class="fas fa-car mr-1"></i>Vehículo <span class="text-danger">*</span>
                        </label>
                        <select name="vehicle_id" id="vehicle_id"
                                class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">-- Seleccione un vehículo --</option>
                            @foreach($vehiculos as $vehiculo)
                            @php $v = is_array($vehiculo) ? $vehiculo : $vehiculo->toArray(); @endphp
                                <option value="{{ $v['id'] }}"
                                    {{ old('vehicle_id', request('vehicle_id')) == $v['id'] ? 'selected' : '' }}>
                                    {{ $v['brand'] }} {{ $v['model'] }} — {{ $v['plate'] }}
                                    ({{ $v['vehicle_type'] }}, {{ $v['capacity'] }} personas)
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if(collect($vehiculos)->isEmpty())
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                No hay vehículos disponibles en este momento.
                            </small>
                        @endif
                    </div>

                    {{-- Fecha inicio --}}
                    <div class="form-group">
                        <label for="departure_date">
                            <i class="fas fa-calendar-alt mr-1"></i>Fecha y hora de inicio <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="departure_date" id="departure_date"
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
                        <input type="datetime-local" name="return_date" id="return_date"
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
                        <textarea name="reason" id="reason" rows="3"
                                  class="form-control"
                                  placeholder="Describa el motivo del viaje...">{{ old('reason') }}</textarea>
                    </div>

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
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('departure_date').addEventListener('change', function() {
    const finInput = document.getElementById('return_date');
    finInput.min = this.value;
    if (finInput.value && finInput.value <= this.value) finInput.value = '';
});
</script>
@endpush