<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['vendor', 'creator']);

        $query->search($request->get('search'), ['po_number']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($vendorId = $request->get('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        $query->filterDateRange($request->get('date_from'), $request->get('date_to'), 'order_date');

        $purchaseOrders = $query->latest('order_date')->paginate(25)->withQueryString();
        $vendors        = Vendor::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);

        $stats = [
            'total'     => PurchaseOrder::count(),
            'draft'     => PurchaseOrder::where('status', 'draft')->count(),
            'approved'  => PurchaseOrder::where('status', 'approved')->count(),
            'received'  => PurchaseOrder::where('status', 'received')->count(),
            'total_value' => PurchaseOrder::whereIn('status', ['approved', 'received'])->sum('total_amount'),
        ];

        return view('inventory.purchase-orders.index', compact('purchaseOrders', 'vendors', 'stats'));
    }

    public function create(): View
    {
        $purchaseOrder = null;
        $vendors       = Vendor::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);
        $products      = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price', 'unit']);

        return view('inventory.purchase-orders.create', compact('purchaseOrder', 'vendors', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePurchaseOrder($request);

        $tenantId = auth()->user()->tenant_id;
        $last = PurchaseOrder::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();
        $next = $last ? ((int) ltrim(substr($last->po_number, 3), '0') + 1) : 1;

        $po = PurchaseOrder::create([
            'po_number'    => 'PO-' . str_pad($next, 5, '0', STR_PAD_LEFT),
            'vendor_id'    => $validated['vendor_id'],
            'status'       => $validated['status'] ?? 'draft',
            'order_date'   => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'notes'        => $validated['notes'] ?? null,
            'created_by'   => auth()->id(),
        ]);

        $this->syncItems($po, $validated['items'] ?? []);

        return redirect()->route('inventory.purchase-orders.show', $po)
            ->with('success', 'Purchase Order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['vendor', 'items.product', 'creator', 'approver']);

        return view('inventory.purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('items');
        $vendors  = Vendor::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price', 'unit']);

        return view('inventory.purchase-orders.create', compact('purchaseOrder', 'vendors', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $this->validatePurchaseOrder($request);

        $purchaseOrder->update([
            'vendor_id'     => $validated['vendor_id'],
            'status'        => $validated['status'] ?? $purchaseOrder->status,
            'order_date'    => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'notes'         => $validated['notes'] ?? null,
        ]);

        $this->syncItems($purchaseOrder, $validated['items'] ?? []);

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()->route('inventory.purchase-orders.index')
            ->with('success', 'Purchase Order deleted.');
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->update([
            'status'      => PurchaseOrderStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Purchase Order approved.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate([
            'received_quantities'   => ['required', 'array'],
            'received_quantities.*' => ['required', 'numeric', 'min:0'],
        ]);

        // Update received quantities on each item
        foreach ($request->received_quantities as $itemId => $qty) {
            $purchaseOrder->items()->where('id', $itemId)->update([
                'received_quantity' => $qty,
            ]);
        }

        $purchaseOrder->update([
            'status'        => PurchaseOrderStatus::Received->value,
            'received_date' => now(),
        ]);

        // Add stock
        app(StockService::class)->addForPurchaseOrder($purchaseOrder);

        return redirect()->back()->with('success', 'Purchase Order received and stock updated.');
    }

    private function syncItems(PurchaseOrder $po, array $items): void
    {
        $po->items()->delete();

        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $qty       = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $taxRate   = (float) ($item['tax_rate'] ?? 0);
            $lineTotal = round($qty * $unitPrice, 2);
            $lineTax   = round($lineTotal * ($taxRate / 100), 2);

            $po->items()->create([
                'product_id'  => $item['product_id'],
                'description' => $item['description'] ?? null,
                'quantity'    => $qty,
                'unit_price'  => $unitPrice,
                'tax_rate'    => $taxRate,
                'total'       => $lineTotal + $lineTax,
            ]);

            $subtotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        $po->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxTotal,
            'total_amount' => $subtotal + $taxTotal,
        ]);
    }

    private function validatePurchaseOrder(Request $request): array
    {
        return $request->validate([
            'vendor_id'              => ['required', 'integer', 'exists:vendors,id'],
            'status'                 => ['nullable', 'string', 'in:draft,submitted'],
            'order_date'             => ['required', 'date'],
            'expected_date'          => ['nullable', 'date'],
            'notes'                  => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.description'    => ['nullable', 'string', 'max:255'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'       => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
