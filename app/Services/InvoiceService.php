<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function generateNumber(int $tenantId): string
    {
        $last = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        $next = $last ? ((int) ltrim(substr($last->invoice_number, 4), '0') + 1) : 1;

        return 'INV-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function convertFromQuote(Quote $quote, array $overrides = []): Invoice
    {
        $quote->load('lineItems.product');

        return DB::transaction(function () use ($quote, $overrides) {
            $invoice = Invoice::create(array_merge([
                'invoice_number'  => $this->generateNumber($quote->tenant_id),
                'quote_id'        => $quote->id,
                'opportunity_id'  => $quote->opportunity_id,
                'account_id'      => $quote->account_id,
                'contact_id'      => $quote->contact_id,
                'owner_id'        => $quote->prepared_by ?? auth()->id(),
                'status'          => InvoiceStatus::Draft->value,
                'issue_date'      => now()->toDateString(),
                'due_date'        => now()->addDays(30)->toDateString(),
                'subtotal'        => $quote->subtotal,
                'discount_type'   => $quote->discount_type,
                'discount_value'  => $quote->discount_value,
                'discount_amount' => $quote->discount_amount,
                'tax_rate'        => $quote->tax_rate,
                'tax_amount'      => $quote->tax_amount,
                'total'           => $quote->total,
                'notes'           => $quote->notes,
                'terms'           => $quote->terms,
                'currency'        => 'USD',
                'created_by'      => auth()->id(),
            ], $overrides));

            foreach ($quote->lineItems as $item) {
                $invoice->lineItems()->create([
                    'product_id'       => $item->product_id,
                    'description'      => $item->description,
                    'quantity'         => $item->quantity,
                    'unit_price'       => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'subtotal'         => $item->subtotal,
                    'sort_order'       => $item->sort_order,
                ]);
            }

            return $invoice;
        });
    }

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

    public function syncLineItems(Invoice $invoice, array $lineItems): void
    {
        $invoice->lineItems()->delete();

        foreach ($lineItems as $index => $item) {
            $subtotal = $this->calculateLineItemSubtotal($item);

            $invoice->lineItems()->create([
                'product_id'       => $item['product_id'] ?? null,
                'description'      => $item['description'],
                'quantity'         => $item['quantity'],
                'unit_price'       => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'subtotal'         => $subtotal,
                'sort_order'       => $index,
            ]);
        }

        $invoice->load('lineItems');
        $this->calculateTotals($invoice);
    }

    public function calculateTotals(Invoice $invoice): void
    {
        $subtotal = $invoice->lineItems->sum('subtotal');

        $discountAmount = 0;
        if ((float) $invoice->discount_value > 0) {
            if ($invoice->discount_type === 'percentage') {
                $discountAmount = round($subtotal * ((float) $invoice->discount_value / 100), 2);
            } else {
                $discountAmount = round((float) $invoice->discount_value, 2);
            }
        }

        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = round($afterDiscount * ((float) $invoice->tax_rate / 100), 2);
        $total = $afterDiscount + $taxAmount;

        $invoice->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount'      => $taxAmount,
            'total'           => $total,
        ]);
    }

    public function refreshPaymentStatus(Invoice $invoice): void
    {
        $invoice->refresh();
        $amountPaid = $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $invoice->amount_paid = $amountPaid;

        if ($amountPaid <= 0) {
            if ($invoice->status === InvoiceStatus::Partial) {
                $invoice->status = InvoiceStatus::Sent->value;
            }
        } elseif ($amountPaid >= (float) $invoice->total) {
            $invoice->status = InvoiceStatus::Paid->value;
            $invoice->paid_at = now();

            // Auto-deduct stock when invoice is fully paid
            app(StockService::class)->deductForInvoice($invoice);
        } else {
            $invoice->status = InvoiceStatus::Partial->value;
        }

        $invoice->save();
    }

    public function generateRecurring(Invoice $parent): Invoice
    {
        $parent->load('lineItems');

        return DB::transaction(function () use ($parent) {
            $nextDate = $this->nextRecurrenceDate($parent);

            $invoice = Invoice::create([
                'invoice_number'     => $this->generateNumber($parent->tenant_id),
                'parent_invoice_id'  => $parent->id,
                'opportunity_id'     => $parent->opportunity_id,
                'account_id'         => $parent->account_id,
                'contact_id'         => $parent->contact_id,
                'owner_id'           => $parent->owner_id,
                'status'             => InvoiceStatus::Draft->value,
                'issue_date'         => $nextDate->toDateString(),
                'due_date'           => $nextDate->copy()->addDays(30)->toDateString(),
                'subtotal'           => $parent->subtotal,
                'discount_type'      => $parent->discount_type,
                'discount_value'     => $parent->discount_value,
                'discount_amount'    => $parent->discount_amount,
                'tax_rate'           => $parent->tax_rate,
                'tax_amount'         => $parent->tax_amount,
                'total'              => $parent->total,
                'currency'           => $parent->currency,
                'notes'              => $parent->notes,
                'terms'              => $parent->terms,
                'is_recurring'       => $parent->is_recurring,
                'recurrence'         => $parent->recurrence,
                'recurrence_end_date'=> $parent->recurrence_end_date?->toDateString(),
                'next_invoice_date'  => $this->nextRecurrenceDate($parent, offset: 1)->toDateString(),
                'created_by'         => auth()->id(),
            ]);

            foreach ($parent->lineItems as $item) {
                $invoice->lineItems()->create([
                    'product_id'       => $item->product_id,
                    'description'      => $item->description,
                    'quantity'         => $item->quantity,
                    'unit_price'       => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'subtotal'         => $item->subtotal,
                    'sort_order'       => $item->sort_order,
                ]);
            }

            // Update parent's next_invoice_date
            $parent->next_invoice_date = $this->nextRecurrenceDate($parent, offset: 1)->toDateString();
            $parent->save();

            return $invoice;
        });
    }

    private function nextRecurrenceDate(Invoice $invoice, int $offset = 0): Carbon
    {
        $base = $invoice->next_invoice_date ?? now();
        $base = Carbon::parse($base);
        $periods = 1 + $offset;

        return match($invoice->recurrence) {
            'monthly'   => $base->copy()->addMonths($periods),
            'quarterly' => $base->copy()->addMonths($periods * 3),
            'yearly'    => $base->copy()->addYears($periods),
            default     => $base->copy()->addMonths($periods),
        };
    }
}
