<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quote;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\InvoicePdfService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['account', 'contact', 'owner']);

        $query->search($request->get('search'), ['invoice_number']);
        $query->filterOwner($request->get('owner_id'));
        $query->filterDateRange($request->get('date_from'), $request->get('date_to'), 'issue_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }

        $invoices = $query->latest()->paginate(25)->withQueryString();
        $owners   = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total_outstanding' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('total') - Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('amount_paid'),
            'overdue_count'     => Invoice::where('status', 'overdue')->count(),
        ];

        return view('finance.invoices.index', compact('invoices', 'owners', 'accounts', 'stats'));
    }

    public function create(Request $request): View
    {
        $quote    = $request->get('quote_id') ? Quote::with('lineItems.product')->findOrFail($request->get('quote_id')) : null;
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit_price', 'unit']);
        $taxRates = TaxRate::where('is_active', true)->orderBy('name')->get();
        $invoice  = null;

        return view('finance.invoices.create', compact('invoice', 'quote', 'accounts', 'contacts', 'opportunities', 'products', 'taxRates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInvoice($request);

        $tenantId = auth()->user()->tenant_id;
        $service  = app(InvoiceService::class);

        $validated['invoice_number'] = $service->generateNumber($tenantId);
        $validated['created_by']     = auth()->id();
        $validated['owner_id']       = $validated['owner_id'] ?? auth()->id();

        $lineItems = $request->input('line_items', []);
        unset($validated['line_items']);

        $invoice = Invoice::create($validated);
        $service->syncLineItems($invoice, $lineItems);

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['lineItems.product', 'account', 'contact', 'opportunity', 'owner', 'createdBy', 'quote', 'payments.createdBy', 'parentInvoice']);

        return view('finance.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('lineItems.product');
        $accounts      = Account::orderBy('name')->get(['id', 'name']);
        $contacts      = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $products      = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit_price', 'unit']);
        $taxRates      = TaxRate::where('is_active', true)->orderBy('name')->get();
        $quote         = null;

        return view('finance.invoices.create', compact('invoice', 'quote', 'accounts', 'contacts', 'opportunities', 'products', 'taxRates'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $this->validateInvoice($request);
        $lineItems = $request->input('line_items', []);
        unset($validated['line_items']);

        $invoice->update($validated);
        app(InvoiceService::class)->syncLineItems($invoice, $lineItems);

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('finance.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function pdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        return app(InvoicePdfService::class)
            ->generate($invoice)
            ->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_date'     => ['required', 'date'],
            'method'           => ['required', 'string'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string'],
            'status'           => ['required', 'string'],
        ]);

        $validated['invoice_id'] = $invoice->id;
        $validated['currency']   = $invoice->currency;
        $validated['created_by'] = auth()->id();

        Payment::create($validated);
        app(InvoiceService::class)->refreshPaymentStatus($invoice);

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully.');
    }

    public function destroyPayment(Invoice $invoice, Payment $payment): RedirectResponse
    {
        $payment->delete();
        app(InvoiceService::class)->refreshPaymentStatus($invoice);

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', 'Payment removed.');
    }

    public function generateRecurring(Invoice $invoice): RedirectResponse
    {
        $newInvoice = app(InvoiceService::class)->generateRecurring($invoice);

        return redirect()->route('finance.invoices.show', $newInvoice)
            ->with('success', 'Recurring invoice ' . $newInvoice->invoice_number . ' generated.');
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'account_id'          => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'          => ['nullable', 'integer', 'exists:contacts,id'],
            'opportunity_id'      => ['nullable', 'integer', 'exists:opportunities,id'],
            'quote_id'            => ['nullable', 'integer', 'exists:quotes,id'],
            'owner_id'            => ['nullable', 'integer', 'exists:users,id'],
            'status'              => ['required', 'string'],
            'issue_date'          => ['required', 'date'],
            'due_date'            => ['nullable', 'date', 'after_or_equal:issue_date'],
            'currency'            => ['required', 'string', 'size:3'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_type'       => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value'      => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string'],
            'terms'               => ['nullable', 'string'],
            'is_recurring'        => ['nullable', 'boolean'],
            'recurrence'          => ['nullable', 'string', 'in:monthly,quarterly,yearly'],
            'recurrence_end_date' => ['nullable', 'date'],
            'next_invoice_date'   => ['nullable', 'date'],
        ]);
    }
}
