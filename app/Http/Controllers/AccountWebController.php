<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Account::with(['owner', 'parent']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%");
            });
        }

        if ($industry = $request->get('industry')) {
            $query->where('industry', $industry);
        }

        $accounts = $query->latest()->paginate(25)->withQueryString();

        return view('accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $parents = Account::orderBy('name')->get(['id', 'name']);
        $owners  = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('accounts.create', compact('parents', 'owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'industry'       => ['nullable', 'string', 'max:100'],
            'website'        => ['nullable', 'url', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'employee_count' => ['nullable', 'integer', 'min:0'],
            'tax_id'         => ['nullable', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'parent_id'      => ['nullable', 'integer', 'exists:accounts,id'],
            'owner_id'       => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $account = Account::create($validated);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account created successfully.');
    }

    public function show(Account $account): View
    {
        $account->load(['owner', 'parent', 'children', 'contacts', 'addresses', 'activities.user', 'notes.user']);

        return view('accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        $parents = Account::where('id', '!=', $account->id)->orderBy('name')->get(['id', 'name']);
        $owners  = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('accounts.create', compact('account', 'parents', 'owners'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'industry'       => ['nullable', 'string', 'max:100'],
            'website'        => ['nullable', 'url', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'employee_count' => ['nullable', 'integer', 'min:0'],
            'tax_id'         => ['nullable', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'parent_id'      => ['nullable', 'integer', 'exists:accounts,id'],
            'owner_id'       => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $account->update($validated);

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
