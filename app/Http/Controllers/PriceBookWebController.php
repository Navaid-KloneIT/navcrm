<?php

namespace App\Http\Controllers;

use App\Models\PriceBook;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceBookWebController extends Controller
{
    public function index(): View
    {
        $priceBooks = PriceBook::with(['entries.product'])->latest()->get();
        $products   = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'unit_price']);

        return view('price-books.index', compact('priceBooks', 'products'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'unit_price']);

        return view('price-books.index', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_default'  => ['boolean'],
            'is_active'   => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['is_active']  = $request->boolean('is_active', true);

        if ($validated['is_default']) {
            PriceBook::where('is_default', true)->update(['is_default' => false]);
        }

        PriceBook::create($validated);

        return redirect()->route('price-books.index')
            ->with('success', 'Price book created successfully.');
    }

    public function show(PriceBook $priceBook): View
    {
        $priceBook->load(['entries.product']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'unit_price']);

        return view('price-books.index', compact('priceBook', 'products'));
    }

    public function update(Request $request, PriceBook $priceBook): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_default'  => ['boolean'],
            'is_active'   => ['boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default', false);
        $validated['is_active']  = $request->boolean('is_active', true);

        if ($validated['is_default']) {
            PriceBook::where('id', '!=', $priceBook->id)->update(['is_default' => false]);
        }

        $priceBook->update($validated);

        return redirect()->route('price-books.index')
            ->with('success', 'Price book updated successfully.');
    }

    public function destroy(PriceBook $priceBook): RedirectResponse
    {
        $priceBook->delete();

        return redirect()->route('price-books.index')
            ->with('success', 'Price book deleted successfully.');
    }
}
