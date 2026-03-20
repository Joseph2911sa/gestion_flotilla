@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <i class="fas fa-truck-moving fa-2x text-primary"></i>
        <br>
        <b>Sistema</b> de Flotilla
    </div>

    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Inicia sesión para continuar</p>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="input-group mb-3">
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="Correo electrónico"
                           value="{{ old('email') }}"
                           autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Contraseña">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection