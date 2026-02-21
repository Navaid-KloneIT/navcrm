<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function deductForInvoice(Invoice $invoice): void
    {
        $invoice->load('lineItems');

        DB::transaction(function () use ($invoice) {
            foreach ($invoice->lineItems as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $qty = (int) $item->quantity;
                if ($qty <= 0) {
                    continue;
                }

                Product::withoutGlobalScopes()
                    ->where('id', $item->product_id)
                    ->decrement('stock_quantity', $qty);

                StockMovement::create([
                    'tenant_id'      => $invoice->tenant_id,
                    'product_id'     => $item->product_id,
                    'type'           => StockMovementType::SaleOut->value,
                    'quantity'       => -$qty,
                    'reference_type' => Invoice::class,
                    'reference_id'   => $invoice->id,
                    'notes'          => "Auto-deduct for Invoice {$invoice->invoice_number}",
                    'created_by'     => null,
                ]);
            }
        });
    }

    public function addForPurchaseOrder(PurchaseOrder $po): void
    {
        $po->load('items');

        DB::transaction(function () use ($po) {
            foreach ($po->items as $item) {
                $qty = (int) $item->received_quantity;
                if ($qty <= 0) {
                    continue;
                }

                Product::withoutGlobalScopes()
                    ->where('id', $item->product_id)
                    ->increment('stock_quantity', $qty);

                StockMovement::create([
                    'tenant_id'      => $po->tenant_id,
                    'product_id'     => $item->product_id,
                    'type'           => StockMovementType::PurchaseIn->value,
                    'quantity'       => $qty,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id'   => $po->id,
                    'notes'          => "Stock received via PO {$po->po_number}",
                    'created_by'     => null,
                ]);
            }
        });
    }

    public function adjust(Product $product, int $quantity, string $notes, ?int $userId = null): void
    {
        DB::transaction(function () use ($product, $quantity, $notes, $userId) {
            $product->increment('stock_quantity', $quantity);

            StockMovement::create([
                'tenant_id'  => $product->tenant_id,
                'product_id' => $product->id,
                'type'       => StockMovementType::Adjustment->value,
                'quantity'   => $quantity,
                'notes'      => $notes,
                'created_by' => $userId,
            ]);
        });
    }
}
