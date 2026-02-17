<?php

namespace App\Services;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotePdfService
{
    public function generate(Quote $quote): \Barryvdh\DomPDF\PDF
    {
        $quote->load(['lineItems.product', 'account', 'contact', 'preparedBy', 'opportunity']);

        return Pdf::loadView('quotes.pdf', [
            'quote' => $quote,
        ])->setPaper('a4');
    }
}
