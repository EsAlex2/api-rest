<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_role' => 'required|string|max:255|unique:roles,name_role',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name_role' => $validated['name_role'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'message' => 'Rol creado exitosamente.',
            'data' => $role->load('permissions')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name_role' => 'sometimes|required|string|max:255|unique:roles,name_role,' . $role->id,
            'description' => 'nullable|string|max:1000',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(array_filter([
            'name_role' => $validated['name_role'] ?? null,
            'description' => $validated['description'] ?? null,
        ]));

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente.',
            'data' => $role->load('permissions')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevenir la eliminación de roles del sistema base (admin, user)
        if (in_array($role->name_role, ['admin', 'user'])) {
            return response()->json([
                'message' => 'No se pueden eliminar los roles base del sistema (admin o user).'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente.'
        ]);
    }

    /**
     * List all available permissions.
     */
    public function listPermissions(): JsonResponse
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }
}
