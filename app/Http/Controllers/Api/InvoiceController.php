<?php

namespace App\Http\Controllers\Api;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json($query->latest()->paginate(25)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'owner_id'       => ['nullable', 'integer', 'exists:users,id'],
            'status'         => ['required', 'string'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['nullable', 'date'],
            'currency'       => ['nullable', 'string', 'size:3'],
            'tax_rate'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'          => ['nullable', 'string'],
            'terms'          => ['nullable', 'string'],
            'line_items'     => ['nullable', 'array'],
        ]);

        $service = app(InvoiceService::class);
        $tenantId = auth()->user()->tenant_id;

        $validated['invoice_number'] = $service->generateNumber($tenantId);
        $validated['created_by']     = auth()->id();
        $validated['owner_id']       = $validated['owner_id'] ?? auth()->id();

        $lineItems = $request->input('line_items', []);
        unset($validated['line_items']);

        $invoice = Invoice::create($validated);
        if ($lineItems) {
            $service->syncLineItems($invoice, $lineItems);
        }

        return response()->json($invoice->load('lineItems'), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['lineItems.product', 'account', 'contact', 'owner', 'payments']));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'owner_id'       => ['nullable', 'integer', 'exists:users,id'],
            'status'         => ['sometimes', 'string'],
            'issue_date'     => ['sometimes', 'date'],
            'due_date'       => ['nullable', 'date'],
            'currency'       => ['nullable', 'string', 'size:3'],
            'tax_rate'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'          => ['nullable', 'string'],
            'terms'          => ['nullable', 'string'],
            'line_items'     => ['nullable', 'array'],
        ]);

        $lineItems = $request->input('line_items');
        unset($validated['line_items']);

        $invoice->update($validated);
        if ($lineItems !== null) {
            app(InvoiceService::class)->syncLineItems($invoice, $lineItems);
        }

        return response()->json($invoice->load('lineItems'));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $invoice->update(['status' => $request->status]);

        return response()->json($invoice);
    }
}
