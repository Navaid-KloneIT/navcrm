<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['lineItems.product', 'account', 'contact', 'owner']);

        return Pdf::loadView('finance.invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4');
    }
}
