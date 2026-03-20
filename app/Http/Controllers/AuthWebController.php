<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthWebController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = config('app.url') . '/api/v1';
    }

    public function showLogin()
    {
        if (session('api_token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciales incorrectas.');
        }

        $user = Auth::user();
        $user->load('role');

        $token = $user->createToken('web-token')->plainTextToken;

        session([
            'api_token' => $token,
            'user'      => $user->toArray(),
        ]);

        $role = $user->role->name ?? '';

        return match ($role) {
            'Admin'    => redirect()->route('dashboard'),
            'Operador' => redirect()->route('dashboard'),
            'Chofer'   => redirect()->route('dashboard'),
            default    => redirect()->route('login')->with('error', 'Rol no reconocido.'),
        };
    }

    public function logout(Request $request)
    {
        Http::withToken(session('api_token'))
            ->post("{$this->apiBase}/logout");

        session()->flush();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}