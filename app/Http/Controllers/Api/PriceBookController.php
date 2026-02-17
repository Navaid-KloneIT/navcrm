<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceBook\StorePriceBookRequest;
use App\Http\Resources\PriceBookResource;
use App\Http\Resources\PriceBookEntryResource;
use App\Models\PriceBook;
use App\Models\PriceBookEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceBookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PriceBook::query()->withCount('entries');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $priceBooks = $query->orderBy('name')->get();

        return response()->json([
            'data' => PriceBookResource::collection($priceBooks),
        ]);
    }

    public function store(StorePriceBookRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        if (!empty($validated['is_default'])) {
            PriceBook::where('is_default', true)->update(['is_default' => false]);
        }

        $priceBook = PriceBook::create($validated);

        return response()->json([
            'data' => new PriceBookResource($priceBook),
        ], 201);
    }

    public function show(PriceBook $priceBook): JsonResponse
    {
        $priceBook->load(['entries.product']);

        return response()->json([
            'data' => new PriceBookResource($priceBook),
        ]);
    }

    public function update(Request $request, PriceBook $priceBook): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            PriceBook::where('id', '!=', $priceBook->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $priceBook->update($validated);

        return response()->json([
            'data' => new PriceBookResource($priceBook->fresh()),
        ]);
    }

    public function destroy(PriceBook $priceBook): JsonResponse
    {
        $priceBook->delete();

        return response()->json(null, 204);
    }

    public function addEntry(Request $request, PriceBook $priceBook): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($priceBook->entries()->where('product_id', $validated['product_id'])->exists()) {
            return response()->json(['message' => 'Product already exists in this price book.'], 422);
        }

        $entry = $priceBook->entries()->create($validated);
        $entry->load('product');

        return response()->json([
            'data' => new PriceBookEntryResource($entry),
        ], 201);
    }

    public function updateEntry(Request $request, PriceBookEntry $entry): JsonResponse
    {
        $validated = $request->validate([
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
            'min_quantity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $entry->update($validated);
        $entry->load('product');

        return response()->json([
            'data' => new PriceBookEntryResource($entry),
        ]);
    }

    public function removeEntry(PriceBookEntry $entry): JsonResponse
    {
        $entry->delete();

        return response()->json(null, 204);
    }
}
