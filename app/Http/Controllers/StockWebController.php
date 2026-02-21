<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();

        $query->search($request->get('search'), ['name', 'sku']);

        if ($request->get('low_stock')) {
            $query->where('reorder_level', '>', 0)
                  ->whereColumn('stock_quantity', '<=', 'reorder_level');
        }

        if ($request->get('out_of_stock')) {
            $query->where('stock_quantity', '<=', 0);
        }

        $products = $query->where('is_active', true)->orderBy('name')->paginate(25)->withQueryString();

        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock'      => Product::where('is_active', true)
                ->where('reorder_level', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->count(),
            'out_of_stock'   => Product::where('is_active', true)
                ->where('stock_quantity', '<=', 0)
                ->count(),
        ];

        return view('inventory.stock.index', compact('products', 'stats'));
    }

    public function show(Product $product): View
    {
        $movements = StockMovement::where('product_id', $product->id)
            ->with('creator')
            ->latest()
            ->paginate(25);

        return view('inventory.stock.show', compact('product', 'movements'));
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer'],
            'notes'    => ['required', 'string', 'max:500'],
        ]);

        app(StockService::class)->adjust(
            $product,
            $validated['quantity'],
            $validated['notes'],
            auth()->id()
        );

        return redirect()->route('inventory.stock.show', $product)
            ->with('success', 'Stock adjusted successfully.');
    }
}
