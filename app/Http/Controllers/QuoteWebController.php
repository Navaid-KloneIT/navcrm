<?php

namespace App\Http\Controllers;

use App\Enums\QuoteStatus;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PriceBook;
use App\Models\Product;
use App\Models\Quote;
use App\Services\QuoteCalculationService;
use App\Services\QuotePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class QuoteWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Quote::with(['account', 'contact', 'opportunity', 'preparedBy']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%");
            });
        }

        $quotes  = $query->latest()->paginate(25)->withQueryString();
        $statuses = QuoteStatus::cases();

        return view('quotes.index', compact('quotes', 'statuses'));
    }

    public function create(): View
    {
        $accounts      = Account::orderBy('name')->get(['id', 'name']);
        $contacts      = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $products      = Product::where('is_active', true)->orderBy('name')->get();
        $priceBooks    = PriceBook::where('is_active', true)->orderBy('name')->get(['id', 'name', 'is_default']);

        return view('quotes.create', compact('accounts', 'contacts', 'opportunities', 'products', 'priceBooks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opportunity_id'  => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'      => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'      => ['nullable', 'integer', 'exists:contacts,id'],
            'valid_until'     => ['nullable', 'date'],
            'discount_type'   => ['nullable', 'in:percentage,fixed'],
            'discount_value'  => ['nullable', 'numeric', 'min:0'],
            'tax_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'           => ['nullable', 'string'],
            'terms'           => ['nullable', 'string'],
            'line_items'                      => ['required', 'array', 'min:1'],
            'line_items.*.product_id'          => ['nullable', 'integer', 'exists:products,id'],
            'line_items.*.description'         => ['required', 'string', 'max:500'],
            'line_items.*.quantity'            => ['required', 'numeric', 'min:0.01'],
            'line_items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'line_items.*.discount_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Auto-generate quote number
        $lastQuote = Quote::withoutGlobalScopes()->where('tenant_id', $tenantId)->latest('id')->first();
        $sequence  = $lastQuote ? ((int) ltrim(substr($lastQuote->quote_number, 3), '0') + 1) : 1;
        $quoteNumber = 'QT-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        $quote = Quote::create([
            'quote_number'   => $quoteNumber,
            'opportunity_id' => $validated['opportunity_id'] ?? null,
            'account_id'     => $validated['account_id'] ?? null,
            'contact_id'     => $validated['contact_id'] ?? null,
            'status'         => QuoteStatus::Draft,
            'valid_until'    => $validated['valid_until'] ?? null,
            'discount_type'  => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? 0,
            'tax_rate'       => $validated['tax_rate'] ?? 0,
            'notes'          => $validated['notes'] ?? null,
            'terms'          => $validated['terms'] ?? null,
            'prepared_by'    => auth()->id(),
            'subtotal'       => 0,
            'discount_amount'=> 0,
            'tax_amount'     => 0,
            'total'          => 0,
        ]);

        foreach ($validated['line_items'] as $idx => $item) {
            $discountPct = (float) ($item['discount_percent'] ?? 0);
            $lineTotal   = (float) $item['quantity'] * (float) $item['unit_price'] * (1 - $discountPct / 100);

            $quote->lineItems()->create([
                'product_id'      => $item['product_id'] ?? null,
                'description'     => $item['description'],
                'quantity'        => $item['quantity'],
                'unit_price'      => $item['unit_price'],
                'discount_percent'=> $discountPct,
                'subtotal'        => round($lineTotal, 2),
                'sort_order'      => $idx,
            ]);
        }

        $quote->load('lineItems');
        app(QuoteCalculationService::class)->calculateQuoteTotals($quote);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote created successfully.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(['lineItems.product', 'account', 'contact', 'opportunity', 'preparedBy']);

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        $quote->load(['lineItems.product']);

        $accounts      = Account::orderBy('name')->get(['id', 'name']);
        $contacts      = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $products      = Product::where('is_active', true)->orderBy('name')->get();
        $priceBooks    = PriceBook::where('is_active', true)->orderBy('name')->get(['id', 'name', 'is_default']);

        return view('quotes.create', compact('quote', 'accounts', 'contacts', 'opportunities', 'products', 'priceBooks'));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'opportunity_id'  => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'      => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'      => ['nullable', 'integer', 'exists:contacts,id'],
            'valid_until'     => ['nullable', 'date'],
            'discount_type'   => ['nullable', 'in:percentage,fixed'],
            'discount_value'  => ['nullable', 'numeric', 'min:0'],
            'tax_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'           => ['nullable', 'string'],
            'terms'           => ['nullable', 'string'],
            'line_items'                      => ['required', 'array', 'min:1'],
            'line_items.*.product_id'          => ['nullable', 'integer', 'exists:products,id'],
            'line_items.*.description'         => ['required', 'string', 'max:500'],
            'line_items.*.quantity'            => ['required', 'numeric', 'min:0.01'],
            'line_items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'line_items.*.discount_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $quote->update([
            'opportunity_id' => $validated['opportunity_id'] ?? null,
            'account_id'     => $validated['account_id'] ?? null,
            'contact_id'     => $validated['contact_id'] ?? null,
            'valid_until'    => $validated['valid_until'] ?? null,
            'discount_type'  => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? 0,
            'tax_rate'       => $validated['tax_rate'] ?? 0,
            'notes'          => $validated['notes'] ?? null,
            'terms'          => $validated['terms'] ?? null,
        ]);

        $quote->lineItems()->delete();
        foreach ($validated['line_items'] as $idx => $item) {
            $discountPct = (float) ($item['discount_percent'] ?? 0);
            $lineTotal   = (float) $item['quantity'] * (float) $item['unit_price'] * (1 - $discountPct / 100);

            $quote->lineItems()->create([
                'product_id'      => $item['product_id'] ?? null,
                'description'     => $item['description'],
                'quantity'        => $item['quantity'],
                'unit_price'      => $item['unit_price'],
                'discount_percent'=> $discountPct,
                'subtotal'        => round($lineTotal, 2),
                'sort_order'      => $idx,
            ]);
        }

        $quote->load('lineItems');
        app(QuoteCalculationService::class)->calculateQuoteTotals($quote);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Quote updated successfully.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Quote deleted successfully.');
    }

    public function pdf(Quote $quote): Response
    {
        $pdf = app(QuotePdfService::class)->generate($quote);

        return $pdf->download("quote-{$quote->quote_number}.pdf");
    }
}
