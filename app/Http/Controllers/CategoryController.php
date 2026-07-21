<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')->get();
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([   
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'data' => $category
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');
        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados.'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }
}
