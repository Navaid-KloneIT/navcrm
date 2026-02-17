<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate($request->input('per_page', 15));

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $product = Product::create($validated);

        return response()->json([
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
