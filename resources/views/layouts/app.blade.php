<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Flotilla')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- Navbar top --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    <i class="fas fa-user-circle mr-1"></i>
                    {{ session('user')['name'] ?? 'Usuario' }}
                    <span class="badge badge-secondary ml-1">
                        {{ session('user')['role']['name'] ?? '' }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- Sidebar --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <i class="fas fa-truck-moving ml-3 mr-2"></i>
            <span class="brand-text font-weight-bold">Flotilla</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-white ml-1"></i>
                </div>
                <div class="info">
                    <span class="d-block text-white">{{ session('user')['name'] ?? '' }}</span>
                    <small class="text-muted">{{ session('user')['role']['name'] ?? '' }}</small>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">

                    {{-- Dashboard (todos) --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @php $role = session('user')['role']['name'] ?? ''; @endphp

                    {{-- ── ADMIN ─────────────────────────────────── --}}
                    @if($role === 'Admin')
                        <li class="nav-header">ADMINISTRACIÓN</li>

                        <li class="nav-item">
                            <a href="{{ route('admin.usuarios') }}" class="nav-link {{ request()->routeIs('admin.usuarios*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.vehiculos') }}" class="nav-link {{ request()->routeIs('admin.vehiculos*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-car"></i>
                                <p>Vehículos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.rutas') }}" class="nav-link {{ request()->routeIs('admin.rutas*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-route"></i>
                                <p>Rutas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.mantenimientos') }}" class="nav-link {{ request()->routeIs('admin.mantenimientos*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>Mantenimientos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reportes') }}" class="nav-link {{ request()->routeIs('admin.reportes*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reportes</p>
                            </a>
                        </li>
                    @endif

                    {{-- ── OPERADOR ──────────────────────────────── --}}
                    @if($role === 'Operador')
                        <li class="nav-header">OPERACIONES</li>

                        <li class="nav-item">
                            <a href="{{ route('operador.solicitudes') }}" class="nav-link {{ request()->routeIs('operador.solicitudes*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Solicitudes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('operador.viajes') }}" class="nav-link {{ request()->routeIs('operador.viajes*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-road"></i>
                                <p>Viajes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('operador.rutas') }}" class="nav-link {{ request()->routeIs('operador.rutas*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-route"></i>
                                <p>Rutas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('operador.mantenimientos') }}" class="nav-link {{ request()->routeIs('operador.mantenimientos*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>Mantenimientos</p>
                            </a>
                        </li>
                    @endif

                    {{-- ── CHOFER ────────────────────────────────── --}}
                    @if($role === 'Chofer')
                        <li class="nav-header">MI PANEL</li>

                        <li class="nav-item">
                            <a href="{{ route('chofer.vehiculos') }}" class="nav-link {{ request()->routeIs('chofer.vehiculos*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-car-side"></i>
                                <p>Vehículos Disponibles</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('chofer.solicitudes') }}" class="nav-link {{ request()->routeIs('chofer.solicitudes*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Nueva Solicitud</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('chofer.historial') }}" class="nav-link {{ request()->routeIs('chofer.historial*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Mi Historial</p>
                            </a>
                        </li>
                    @endif

                </ul>
            </nav>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                {{-- Alertas globales --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <footer class="main-footer text-center text-muted">
        <strong>Sistema de Gestión de Flotilla</strong> &copy; {{ date('Y') }}
    </footer>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
@stack('scripts')
</body>
</html>