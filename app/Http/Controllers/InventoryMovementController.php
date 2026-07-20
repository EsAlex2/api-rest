<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InventoryMovement::with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate($request->integer('per_page', 15));

        return response()->json($movements);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:0', // Ajuste a 0 es válido, pero in/out requiere > 0 en lógica
            'reason' => 'nullable|string|max:255'
        ]);

        $productId = $validated['product_id'];
        $type = $validated['type'];
        $quantity = (int)$validated['quantity'];
        $reason = $validated['reason'] ?? null;

        if ($type !== 'adjustment' && $quantity <= 0) {
            return response()->json([
                'message' => 'La cantidad para entradas o salidas de inventario debe ser mayor a 0.'
            ], 422);
        }

        try {
            $result = DB::transaction(function() use ($productId, $type, $quantity, $reason) {
                // Obtener el producto bloqueándolo para escritura para evitar condiciones de carrera (race conditions)
                $product = Product::lockForUpdate()->findOrFail($productId);

                $oldStock = $product->stock;
                $newStock = $oldStock;

                if ($type === 'in') {
                    $newStock = $oldStock + $quantity;
                } elseif ($type === 'out') {
                    if ($oldStock < $quantity) {
                        throw new \Exception("Stock insuficiente para realizar esta salida. Stock actual: {$oldStock}, cantidad solicitada: {$quantity}.");
                    }
                    $newStock = $oldStock - $quantity;
                } elseif ($type === 'adjustment') {
                    $newStock = $quantity;
                }

                // Determinar el nuevo estado
                $statusId = $this->determineStatusId($newStock, $product->min_stock);

                // Actualizar el producto
                $product->update([
                    'stock' => $newStock,
                    'status_id' => $statusId
                ]);

                // Registrar el movimiento
                $movement = InventoryMovement::create([
                    'product_id' => $productId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'reason' => $reason ?? ($type === 'adjustment' ? 'Ajuste de inventario' : ($type === 'in' ? 'Entrada manual' : 'Salida manual'))
                ]);

                return [
                    'movement' => $movement,
                    'product' => $product->load(['category', 'supplier', 'status'])
                ];
            });

            return response()->json([
                'message' => 'Movimiento de inventario registrado con éxito',
                'data' => $result['movement'],
                'product' => $result['product']
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(InventoryMovement $movement): JsonResponse
    {
        return response()->json($movement->load('product'));
    }

    public function productMovements(Product $product): JsonResponse
    {
        $movements = $product->movements()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'product' => $product->load(['category', 'supplier', 'status']),
            'movements' => $movements
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
