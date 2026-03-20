<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthWeb
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Si no hay sesión activa, redirigir al login
        if (!session('api_token') || !session('user')) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para continuar.');
        }

        // Si se especifican roles, verificar que el usuario tenga uno de ellos
        if (!empty($roles)) {
            $userRole = session('user')['role']['name'] ?? '';
            if (!in_array($userRole, $roles)) {
                abort(403, 'No tienes permiso para acceder a esta sección.');
            }
        }

        return $next($request);
    }
}