<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VehiculoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Operador\SolicitudController as OperadorSolicitudController;

// ── Rutas públicas ────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthWebController::class, 'logout'])->name('logout');

// ── Rutas protegidas (requieren sesión) ───────────────────────────────────────
Route::middleware('auth.web')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('auth.web:Admin')->prefix('admin')->name('admin.')->group(function () {

        // Tarjeta 10: CRUD Usuarios
        Route::get(   '/usuarios',             [UsuarioController::class, 'index']  )->name('usuarios');
        Route::get(   '/usuarios/crear',       [UsuarioController::class, 'create'] )->name('usuarios.crear');
        Route::post(  '/usuarios',             [UsuarioController::class, 'store']  )->name('usuarios.store');
        Route::get(   '/usuarios/{id}/editar', [UsuarioController::class, 'edit']   )->name('usuarios.editar');
        Route::put(   '/usuarios/{id}',        [UsuarioController::class, 'update'] )->name('usuarios.update');
        Route::delete('/usuarios/{id}',        [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // Tarjeta 11: CRUD Vehículos
        Route::get(   '/vehiculos',             [VehiculoController::class, 'index']  )->name('vehiculos');
        Route::get(   '/vehiculos/crear',       [VehiculoController::class, 'create'] )->name('vehiculos.crear');
        Route::post(  '/vehiculos',             [VehiculoController::class, 'store']  )->name('vehiculos.store');
        Route::get(   '/vehiculos/{id}/editar', [VehiculoController::class, 'edit']   )->name('vehiculos.editar');
        Route::put(   '/vehiculos/{id}',        [VehiculoController::class, 'update'] )->name('vehiculos.update');
        Route::delete('/vehiculos/{id}',        [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');

        // Tarjeta 28: Reportes
        Route::get('/reportes',                        [ReporteController::class, 'index']          )->name('reportes');
        Route::get('/reportes/disponibilidad',         [ReporteController::class, 'disponibilidad'] )->name('reportes.disponibilidad');
        Route::get('/reportes/uso-flotilla',           [ReporteController::class, 'usoFlotilla']    )->name('reportes.uso-flotilla');
        Route::get('/reportes/historial-chofer',       [ReporteController::class, 'historialChofer'])->name('reportes.historial-chofer');

        // Pendientes
        Route::get('/mantenimientos', fn() => view('admin.mantenimientos.index'))->name('mantenimientos');
        Route::get('/rutas',          fn() => view('admin.rutas.index')         )->name('rutas');
    });

    // ── Operador ──────────────────────────────────────────────────────────────
    Route::middleware('auth.web:Admin,Operador')->prefix('operador')->name('operador.')->group(function () {

        // Tarjeta 20: Panel Operador
        Route::get(   '/solicitudes',                    [OperadorSolicitudController::class, 'index']           )->name('solicitudes');
        Route::patch( '/solicitudes/{id}/aprobar',       [OperadorSolicitudController::class, 'aprobar']         )->name('solicitudes.aprobar');
        Route::patch( '/solicitudes/{id}/rechazar',      [OperadorSolicitudController::class, 'rechazar']        )->name('solicitudes.rechazar');
        Route::post(  '/solicitudes/asignacion-directa', [OperadorSolicitudController::class, 'asignacionDirecta'])->name('solicitudes.directa');

        // Pendientes
        Route::get('/viajes',         fn() => view('operador.viajes.index')        )->name('viajes');
        Route::get('/rutas',          fn() => view('operador.rutas.index')         )->name('rutas');
        Route::get('/mantenimientos', fn() => view('operador.mantenimientos.index'))->name('mantenimientos');
    });

    // ── Chofer ────────────────────────────────────────────────────────────────
    Route::middleware('auth.web:Chofer')->prefix('chofer')->name('chofer.')->group(function () {
        Route::get(   '/vehiculos',               [\App\Http\Controllers\Chofer\VehiculoController::class,  'index']  )->name('vehiculos');
        Route::get(   '/solicitudes',             [\App\Http\Controllers\Chofer\SolicitudController::class, 'index']  )->name('solicitudes');
        Route::post(  '/solicitudes',             [\App\Http\Controllers\Chofer\SolicitudController::class, 'store']  )->name('solicitudes.store');
        Route::get(   '/historial',               [\App\Http\Controllers\Chofer\HistorialController::class, 'index']  )->name('historial');
        Route::post(  '/historial/{id}/cancelar', [\App\Http\Controllers\Chofer\HistorialController::class, 'cancelar'])->name('historial.cancelar');
    });

});