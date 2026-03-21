<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;

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
        Route::get('/usuarios',       fn() => view('admin.usuarios.index'))->name('usuarios');
        Route::get('/vehiculos',      fn() => view('admin.vehiculos.index'))->name('vehiculos');
        Route::get('/mantenimientos', fn() => view('admin.mantenimientos.index'))->name('mantenimientos');
        Route::get('/reportes',       fn() => view('admin.reportes.index'))->name('reportes');
        Route::get('/rutas',          fn() => view('admin.rutas.index'))->name('rutas');
    });

    // ── Operador ──────────────────────────────────────────────────────────────
    Route::middleware('auth.web:Admin,Operador')->prefix('operador')->name('operador.')->group(function () {
        Route::get('/solicitudes',    fn() => view('operador.solicitudes.index'))->name('solicitudes');
        Route::get('/viajes',         fn() => view('operador.viajes.index'))->name('viajes');
        Route::get('/rutas',          fn() => view('operador.rutas.index'))->name('rutas');
        Route::get('/mantenimientos', fn() => view('operador.mantenimientos.index'))->name('mantenimientos');
    });

// ── Chofer ────────────────────────────────────────────────────────────────
    Route::middleware('auth.web:Chofer')->prefix('chofer')->name('chofer.')->group(function () {
    Route::get('/vehiculos',    [\App\Http\Controllers\Chofer\VehiculoController::class,  'index'])->name('vehiculos');
    Route::get('/solicitudes',  [\App\Http\Controllers\Chofer\SolicitudController::class, 'index'])->name('solicitudes');
    Route::post('/solicitudes', [\App\Http\Controllers\Chofer\SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::get('/historial',    [\App\Http\Controllers\Chofer\HistorialController::class, 'index'])->name('historial');
    Route::post('/historial/{id}/cancelar', [\App\Http\Controllers\Chofer\HistorialController::class, 'cancelar'])->name('historial.cancelar');
    });
});