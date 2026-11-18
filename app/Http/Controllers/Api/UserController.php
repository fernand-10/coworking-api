<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roles:id,name')->get();
        return $this->successResponse($users, 'Listado de usuarios');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->assignRole($validated['role']);

        return $this->successResponse($user->load('roles'), 'Usuario creado correctamente', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->successResponse($user->load('roles', 'permissions'), 'Detalle del usuario');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:6'],
        ]);

        $user->update($validated);
        return $this->successResponse($user->load('roles'), 'Usuario actualizado correctamente');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return $this->successResponse(null, 'Usuario eliminado correctamente');
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->syncRoles([$validated['role']]);

        return $this->successResponse($user->load('roles'), 'Rol asignado correctamente al usuario');
    }
}
