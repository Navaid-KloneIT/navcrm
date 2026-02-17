<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\StoreQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\QuoteCalculationService;
use App\Services\QuotePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteCalculationService $calculationService,
        protected QuotePdfService $pdfService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Quote::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($opportunityId = $request->input('opportunity_id')) {
            $query->where('opportunity_id', $opportunityId);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $quotes = $query->with(['opportunity', 'account', 'contact', 'preparedBy'])
            ->paginate($request->input('per_page', 15));

        return response()->json(
            QuoteResource::collection($quotes)->response()->getData(true)
        );
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $lineItems = $validated['line_items'];
        unset($validated['line_items']);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['prepared_by'] = $request->user()->id;
        $validated['quote_number'] = $this->generateQuoteNumber($request->user()->tenant_id);

        $quote = DB::transaction(function () use ($validated, $lineItems) {
            $quote = Quote::create($validated);
            $this->calculationService->syncLineItems($quote, $lineItems);
            return $quote;
        });

        return response()->json([
            'data' => new QuoteResource(
                $quote->fresh()->load(['lineItems.product', 'opportunity', 'account', 'contact', 'preparedBy'])
            ),
        ], 201);
    }

    public function show(Quote $quote): JsonResponse
    {
        $quote->load(['lineItems.product', 'opportunity', 'account', 'contact', 'preparedBy']);

        return response()->json([
            'data' => new QuoteResource($quote),
        ]);
    }

    public function update(UpdateQuoteRequest $request, Quote $quote): JsonResponse
    {
        $validated = $request->validated();

        $quote = DB::transaction(function () use ($quote, $validated) {
            if (isset($validated['line_items'])) {
                $lineItems = $validated['line_items'];
                unset($validated['line_items']);
                $quote->update($validated);
                $this->calculationService->syncLineItems($quote, $lineItems);
            } else {
                $quote->update($validated);
                $this->calculationService->calculateQuoteTotals($quote);
            }
            return $quote;
        });

        return response()->json([
            'data' => new QuoteResource(
                $quote->fresh()->load(['lineItems.product', 'opportunity', 'account', 'contact', 'preparedBy'])
            ),
        ]);
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $quote->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Quote $quote): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,sent,accepted,rejected,expired'],
        ]);

        $quote->update($validated);

        return response()->json([
            'data' => new QuoteResource(
                $quote->fresh()->load(['lineItems.product', 'opportunity', 'account', 'contact', 'preparedBy'])
            ),
        ]);
    }

    public function generatePdf(Quote $quote)
    {
        $pdf = $this->pdfService->generate($quote);

        return $pdf->download("quote-{$quote->quote_number}.pdf");
    }

    private function generateQuoteNumber(int $tenantId): string
    {
        $lastQuote = Quote::where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->first();

        if ($lastQuote && preg_match('/Q-(\d+)/', $lastQuote->quote_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1001;
        }

        return 'Q-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
