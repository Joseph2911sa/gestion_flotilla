<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('role:id,name')->latest();

        // Filtro por role_id (útil para cargar solo choferes: ?role_id=3)
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Respetar per_page para cargar todos cuando se necesite
        $perPage = min((int) $request->query('per_page', 10), 999);
        $users   = $query->paginate($perPage);

        return response()->json([
            'message' => 'Listado de usuarios',
            'data'    => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role_id'  => $request->role_id,
            'phone'    => $request->phone,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('role:id,name');

        return response()->json([
            'message' => 'Usuario obtenido correctamente',
            'data'    => $user,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|email|unique:users,email,' . $user->id,
            'password'=> 'sometimes|min:6',
            'role_id' => 'sometimes|exists:roles,id',
            'phone'   => 'nullable|string|max:20',
        ]);

        $data = $request->only(['name', 'email', 'role_id', 'phone']);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $user,
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
            'data'    => null,
        ]);
    }
}