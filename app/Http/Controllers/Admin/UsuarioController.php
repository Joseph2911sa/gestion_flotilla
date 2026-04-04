<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiConsumer;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    use ApiConsumer;

    public function index(Request $request)
    {
        $rolFiltro = $request->query('rol', '');
        $response  = $this->apiGet('users', ['page' => $request->query('page', 1)]);

        if ($response->failed()) {
            return back()->with('error', 'No se pudo cargar la lista de usuarios.');
        }

        $paginado = $response->json('data');
        $usuarios = collect($paginado['data'] ?? []);

        if ($rolFiltro) {
            $usuarios = $usuarios->filter(
                fn($u) => ($u['role']['name'] ?? '') === $rolFiltro
            )->values();
        }

        return view('admin.usuarios.index', [
            'usuarios'  => $usuarios,
            'paginado'  => [
                'current_page' => $paginado['current_page'],
                'last_page'    => $paginado['last_page'],
                'total'        => $paginado['total'],
            ],
            'rolFiltro' => $rolFiltro,
        ]);
    }

    public function create()
    {
        return view('admin.usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|max:255',
            'role_id'  => 'required|in:1,2,3',
            'password' => 'required|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.email'        => 'Formato de correo inválido.',
            'role_id.required'   => 'Seleccione un rol.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'Mínimo 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $response = $this->apiPost('users', [
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role_id'  => (int) $request->role_id,
            'password' => $request->password,
        ]);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al crear el usuario.');
        }

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(int $id)
    {
        $response = $this->apiGet("users/{$id}");

        if ($response->failed()) {
            return redirect()->route('admin.usuarios')->with('error', 'Usuario no encontrado.');
        }

        return view('admin.usuarios.edit', ['usuario' => $response->json('data')]);
    }

    public function update(Request $request, int $id)
    {
        $rules = [
            'name'    => 'required|string|max:150',
            'email'   => 'required|email|max:255',
            'role_id' => 'required|in:1,2,3',
            'phone'   => 'nullable|string|max:20',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'min:6|confirmed';
        }

        $request->validate($rules, [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.email'        => 'Formato de correo inválido.',
            'role_id.required'   => 'Seleccione un rol.',
            'password.min'       => 'Mínimo 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $payload = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'role_id' => (int) $request->role_id,
        ];
        if ($request->filled('password')) {
            $payload['password'] = $request->password;
        }

        $response = $this->apiPut("users/{$id}", $payload);

        if ($response->failed()) {
            return $this->handleError($response, 'Error al actualizar el usuario.');
        }

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(int $id)
    {
        // ── Bloquear auto-eliminación para evitar ERR_TOO_MANY_REDIRECTS ──────
        $usuarioActual = session('user');
        if ($usuarioActual && (int)($usuarioActual['id'] ?? 0) === $id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario mientras tienes la sesión activa.');
        }

        $response = $this->apiDelete("users/{$id}");

        if ($response->failed()) {
            return back()->with('error', 'No se pudo eliminar el usuario.');
        }

        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado correctamente.');
    }
}