<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with(['user', 'opportunity', 'account', 'approvedBy']);

        $query->search($request->get('search'), ['description']);
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        $query->filterDateRange($request->get('date_from'), $request->get('date_to'), 'expense_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($opportunityId = $request->get('opportunity_id')) {
            $query->where('opportunity_id', $opportunityId);
        }

        $expenses      = $query->latest('expense_date')->paginate(25)->withQueryString();
        $owners        = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total_pending'  => Expense::where('status', 'pending')->sum('amount'),
            'total_approved' => Expense::where('status', 'approved')->sum('amount'),
        ];

        return view('finance.expenses.index', compact('expenses', 'owners', 'opportunities', 'stats'));
    }

    public function create(): View
    {
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $accounts      = Account::orderBy('name')->get(['id', 'name']);
        $expense       = null;

        return view('finance.expenses.create', compact('expense', 'opportunities', 'accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateExpense($request);
        $validated['user_id']    = $validated['user_id'] ?? auth()->id();
        $validated['created_by'] = auth()->id();

        $expense = Expense::create($validated);

        return redirect()->route('finance.expenses.show', $expense)
            ->with('success', 'Expense submitted successfully.');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['opportunity', 'account', 'user', 'approvedBy', 'createdBy']);

        return view('finance.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $accounts      = Account::orderBy('name')->get(['id', 'name']);

        return view('finance.expenses.create', compact('expense', 'opportunities', 'accounts'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $this->validateExpense($request);
        $expense->update($validated);

        return redirect()->route('finance.expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Expense deleted.');
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $expense->update([
            'status'      => ExpenseStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Expense approved.');
    }

    public function reject(Expense $expense): RedirectResponse
    {
        $expense->update([
            'status'      => ExpenseStatus::Rejected->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Expense rejected.');
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'category'       => ['required', 'string'],
            'description'    => ['required', 'string', 'max:500'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'currency'       => ['required', 'string', 'size:3'],
            'expense_date'   => ['required', 'date'],
            'receipt_url'    => ['nullable', 'url', 'max:500'],
            'notes'          => ['nullable', 'string'],
        ]);
    }
}
