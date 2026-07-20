<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'supplier', 'status']);

        // Filtrar por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        // Filtrar por proveedor
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        // Filtrar por estado
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->integer('status_id'));
        }

        // Buscar por nombre o SKU
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filtrar por bajo stock
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->paginate($request->integer('per_page', 15));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => 'required|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $stock = $validated['stock'] ?? 0;
        $minStock = $validated['min_stock'] ?? 0;

        // Determinar automáticamente el estado según el stock inicial
        $validated['status_id'] = $this->determineStatusId($stock, $minStock);

        $product = Product::create($validated);

        // Si se especificó stock inicial > 0, registramos un movimiento de inventario inicial
        if ($stock > 0) {
            $product->movements()->create([
                'type' => 'in',
                'quantity' => $stock,
                'reason' => 'Stock inicial al registrar producto'
            ]);
        }

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data' => $product->load(['category', 'supplier', 'status'])
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'supplier', 'status', 'movements' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|integer|min:0', // Nota: es mejor modificar stock mediante movimientos, pero permitimos ajuste manual aquí
            'min_stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'status_id' => 'nullable|exists:statuses,id', // Permitir asignación manual opcional
        ]);

        // Si el usuario actualiza stock o min_stock, recalculamos el estado de manera automática,
        // a menos que se esté especificando un status_id manual en el request.
        if (!isset($validated['status_id']) && (isset($validated['stock']) || isset($validated['min_stock']))) {
            $newStock = isset($validated['stock']) ? (int)$validated['stock'] : $product->stock;
            $newMinStock = isset($validated['min_stock']) ? (int)$validated['min_stock'] : $product->min_stock;
            $validated['status_id'] = $this->determineStatusId($newStock, $newMinStock);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'data' => $product->load(['category', 'supplier', 'status'])
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Producto y sus movimientos de inventario asociados eliminados exitosamente'
        ]);
    }

    public function lowStock(): JsonResponse
    {
        $products = Product::whereColumn('stock', '<=', 'min_stock')
                           ->with(['category', 'supplier', 'status'])
                           ->get();

        return response()->json([
            'total_low_stock' => $products->count(),
            'data' => $products
        ]);
    }

    private function determineStatusId(int $stock, int $minStock): int
    {
        if ($stock == 0) {
            return Status::where('name_status', 'Agotado')->first()?->id ?? 2;
        }
        if ($stock <= $minStock) {
            return Status::where('name_status', 'Stock Bajo')->first()?->id ?? 3;
        }
        return Status::where('name_status', 'Disponible')->first()?->id ?? 1;
    }
}
