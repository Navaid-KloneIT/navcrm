<?php

namespace App\Http\Controllers\Api;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['vendor', 'creator']);

        $query->search($request->get('search'), ['po_number']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($vendorId = $request->get('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        $orders = $query->latest('order_date')->paginate($request->get('per_page', 25));

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
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

        $po = PurchaseOrder::create([
            'po_number'     => $this->generatePoNumber(),
            'vendor_id'     => $validated['vendor_id'],
            'status'        => $validated['status'] ?? 'draft',
            'order_date'    => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        $subtotal = 0;
        $taxTotal = 0;
        foreach ($validated['items'] as $item) {
            $qty       = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
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

        return response()->json($po->load(['vendor', 'items.product', 'creator']), 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json(
            $purchaseOrder->load(['vendor', 'items.product', 'creator', 'approver'])
        );
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id'     => ['sometimes', 'required', 'integer', 'exists:vendors,id'],
            'status'        => ['sometimes', 'string', 'in:draft,submitted'],
            'order_date'    => ['sometimes', 'required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'notes'         => ['nullable', 'string'],
        ]);

        $purchaseOrder->update($validated);

        return response()->json($purchaseOrder->fresh(['vendor', 'items.product', 'creator']));
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->delete();

        return response()->json(null, 204);
    }

    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->update([
            'status'      => PurchaseOrderStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($purchaseOrder->fresh(['vendor', 'creator', 'approver']));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $request->validate([
            'received_quantities'   => ['required', 'array'],
            'received_quantities.*' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($request->received_quantities as $itemId => $qty) {
            $purchaseOrder->items()->where('id', $itemId)->update([
                'received_quantity' => $qty,
            ]);
        }

        $purchaseOrder->update([
            'status'        => PurchaseOrderStatus::Received->value,
            'received_date' => now(),
        ]);

        app(StockService::class)->addForPurchaseOrder($purchaseOrder);

        return response()->json($purchaseOrder->fresh(['vendor', 'items.product']));
    }

    private function generatePoNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = PurchaseOrder::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        $next = $last ? ((int) ltrim(substr($last->po_number, 3), '0') + 1) : 1;

        return 'PO-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
