<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    // ── GET /admin/usuarios ───────────────────────────────────────────────────
    public function index(Request $request)
    {
        $rolFiltro = $request->query('rol', '');
        $page      = (int) $request->query('page', 1);
        $perPage   = 10;

        $query = User::with('role:id,name')->latest();

        if ($rolFiltro) {
            $query->whereHas('role', fn($q) => $q->where('name', $rolFiltro));
        }

        $paginado = $query->paginate($perPage, ['*'], 'page', $page);

        return view('admin.usuarios.index', [
            'usuarios'  => $paginado->items(),
            'paginado'  => [
                'current_page' => $paginado->currentPage(),
                'last_page'    => $paginado->lastPage(),
                'total'        => $paginado->total(),
            ],
            'rolFiltro' => $rolFiltro,
        ]);
    }

    // ── GET /admin/usuarios/crear ─────────────────────────────────────────────
    public function create()
    {
        return view('admin.usuarios.create');
    }

    // ── POST /admin/usuarios ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:150',
            'email'                 => 'required|email|max:255|unique:users,email',
            'role_id'               => 'required|in:1,2,3',
            'password'              => 'required|min:6|confirmed',
            'phone'                 => 'nullable|string|max:20',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.email'        => 'Formato de correo inválido.',
            'email.unique'       => 'Este correo ya está registrado.',
            'role_id.required'   => 'Seleccione un rol.',
            'role_id.in'         => 'Rol no válido.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'Mínimo 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role_id'  => (int) $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.usuarios')
            ->with('success', 'Usuario creado exitosamente.');
    }

    // ── GET /admin/usuarios/{id}/editar ───────────────────────────────────────
    public function edit(int $id)
    {
        $usuario = User::with('role:id,name')->find($id);

        if (!$usuario) {
            return redirect()->route('admin.usuarios')
                ->with('error', 'Usuario no encontrado.');
        }

        // Convertir a array para que las vistas funcionen igual que antes
        $usuario = $usuario->toArray();

        return view('admin.usuarios.edit', compact('usuario'));
    }

    // ── PUT /admin/usuarios/{id} ──────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return redirect()->route('admin.usuarios')
                ->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'name'    => 'required|string|max:150',
            'email'   => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
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
            'email.unique'       => 'Este correo ya está en uso por otro usuario.',
            'role_id.required'   => 'Seleccione un rol.',
            'password.min'       => 'Mínimo 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'role_id' => (int) $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    // ── DELETE /admin/usuarios/{id} ── Borrado lógico ─────────────────────────
    public function destroy(int $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $usuario->delete(); // SoftDelete

        return redirect()->route('admin.usuarios')
            ->with('success', 'Usuario eliminado correctamente (borrado lógico).');
    }
}