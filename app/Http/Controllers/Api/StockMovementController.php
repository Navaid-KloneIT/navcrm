<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with(['product', 'creator']);

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $movements = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($movements);
    }

    public function show(Product $product, Request $request): JsonResponse
    {
        $movements = StockMovement::where('product_id', $product->id)
            ->with('creator')
            ->latest()
            ->paginate($request->get('per_page', 25));

        return response()->json([
            'product'   => $product,
            'movements' => $movements,
        ]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer'],
            'notes'      => ['required', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        app(StockService::class)->adjust(
            $product,
            $validated['quantity'],
            $validated['notes'],
            auth()->id()
        );

        return response()->json([
            'message'        => 'Stock adjusted successfully.',
            'stock_quantity' => $product->fresh()->stock_quantity,
        ]);
    }
}
