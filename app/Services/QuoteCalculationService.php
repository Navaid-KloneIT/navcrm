<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteLineItem;

class QuoteCalculationService
{
    public function calculateLineItemSubtotal(array $item): float
    {
        $quantity = (float) ($item['quantity'] ?? 0);
        $unitPrice = (float) ($item['unit_price'] ?? 0);
        $discountPercent = (float) ($item['discount_percent'] ?? 0);

        $lineTotal = $quantity * $unitPrice;
        if ($discountPercent > 0) {
            $lineTotal -= $lineTotal * ($discountPercent / 100);
        }

        return round($lineTotal, 2);
    }

    public function calculateQuoteTotals(Quote $quote): void
    {
        $subtotal = $quote->lineItems->sum('subtotal');

        $discountAmount = 0;
        if ($quote->discount_value > 0) {
            if ($quote->discount_type === 'percentage') {
                $discountAmount = round($subtotal * ($quote->discount_value / 100), 2);
            } else {
                $discountAmount = round((float) $quote->discount_value, 2);
            }
        }

        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = round($afterDiscount * ($quote->tax_rate / 100), 2);
        $total = $afterDiscount + $taxAmount;

        $quote->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function syncLineItems(Quote $quote, array $lineItems): void
    {
        $quote->lineItems()->delete();

        foreach ($lineItems as $index => $item) {
            $subtotal = $this->calculateLineItemSubtotal($item);

            $quote->lineItems()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'subtotal' => $subtotal,
                'sort_order' => $index,
            ]);
        }

        $quote->load('lineItems');
        $this->calculateQuoteTotals($quote);
    }
}
