<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = Status::all();
        return response()->json($statuses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_status' => 'required|string|max:255|unique:statuses,name_status',
            'description' => 'required|string|max:1000',
        ]);

        $status = Status::create($validated);

        return response()->json([
            'message' => 'Estado creado exitosamente',
            'data' => $status
        ], 201);
    }

    public function show(Status $status): JsonResponse
    {
        return response()->json($status);
    }

    public function update(Request $request, Status $status): JsonResponse
    {
        $validated = $request->validate([
            'name_status' => 'sometimes|required|string|max:255|unique:statuses,name_status,' . $status->id,
            'description' => 'sometimes|required|string|max:1000',
        ]);

        $status->update($validated);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data' => $status
        ]);
    }

    public function destroy(Status $status): JsonResponse
    {
        // Verificar si hay productos que usan este estado
        if ($status->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el estado porque está asociado a uno o más productos.'
            ], 400);
        }

        $status->delete();

        return response()->json([
            'message' => 'Estado eliminado exitosamente'
        ]);
    }
}
