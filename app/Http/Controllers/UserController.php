<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        }

        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        // El usuario autenticado debe ser admin. Esto lo validaremos en el middleware o ruta, 
        // pero añadimos una capa de seguridad extra aquí.
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para registrar usuarios.'
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cedula' => 'required|string|max:50|unique:users,cedula',
            'gender' => 'required|string|in:M,F,Otro',
            'mobile_phone' => 'nullable|string|max:50',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'nullable|string|in:admin,user',
        ]);

        // 1. Generar nombre de usuario (username) automáticamente
        // Normalizamos los nombres para que contengan solo caracteres alfanuméricos
        $normalizedFirstName = Str::slug($validated['first_name'], '');
        $normalizedLastName = Str::slug($validated['last_name'], '');
        $baseUsername = strtolower($normalizedFirstName . '.' . $normalizedLastName);

        $username = $baseUsername;
        $counter = 1;

        // Bucle para evitar colisiones de nombre de usuario en la base de datos
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // 2. Establecer la contraseña por defecto (su propia cédula de identidad)
        $defaultPassword = $validated['cedula'];

        // 3. Crear el usuario
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'cedula' => $validated['cedula'],
            'gender' => $validated['gender'],
            'mobile_phone' => $validated['mobile_phone'] ?? null,
            'email' => $validated['email'],
            'role' => $validated['role'] ?? 'user',
            'username' => $username,
            'password' => Hash::make($defaultPassword), // Almacenamos la contraseña cifrada
        ]);

        // Retornamos el usuario creado, informando la clave temporal generada
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'data' => $user,
            'credenciales_generadas' => [
                'username' => $username,
                'password_temporal' => $defaultPassword
            ]
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        }

        return response()->json($user);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'No autorizado. Se requiere rol de administrador.'
            ], 403);
        }

        // Prevenir que un admin se elimine a sí mismo
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario de la sesión activa.'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente.'
        ]);
    }
}
