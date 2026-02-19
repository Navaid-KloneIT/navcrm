<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with(['user', 'opportunity', 'account', 'approvedBy']);

        $query->search($request->get('search'), ['description']);
        $query->filterDateRange($request->get('date_from'), $request->get('date_to'), 'expense_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        return response()->json($query->latest('expense_date')->paginate(25)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'category'       => ['required', 'string'],
            'description'    => ['required', 'string', 'max:500'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'currency'       => ['nullable', 'string', 'size:3'],
            'expense_date'   => ['required', 'date'],
            'receipt_url'    => ['nullable', 'url', 'max:500'],
            'notes'          => ['nullable', 'string'],
        ]);

        $validated['user_id']    = $validated['user_id'] ?? auth()->id();
        $validated['created_by'] = auth()->id();

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense->load(['opportunity', 'account', 'user', 'approvedBy']));
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'category'       => ['sometimes', 'string'],
            'description'    => ['sometimes', 'string', 'max:500'],
            'amount'         => ['sometimes', 'numeric', 'min:0.01'],
            'currency'       => ['nullable', 'string', 'size:3'],
            'expense_date'   => ['sometimes', 'date'],
            'receipt_url'    => ['nullable', 'url', 'max:500'],
            'notes'          => ['nullable', 'string'],
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(null, 204);
    }

    public function approve(Expense $expense): JsonResponse
    {
        $expense->update([
            'status'      => ExpenseStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($expense);
    }

    public function reject(Expense $expense): JsonResponse
    {
        $expense->update([
            'status'      => ExpenseStatus::Rejected->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json($expense);
    }
}
