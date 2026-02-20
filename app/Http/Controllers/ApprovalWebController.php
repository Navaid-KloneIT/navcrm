<?php

namespace App\Http\Controllers;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalWebController extends Controller
{
    public function index(): View
    {
        $quotes = Quote::with(['account', 'opportunity', 'preparedBy'])
            ->where('status', QuoteStatus::PendingApproval)
            ->latest()
            ->paginate(25);

        return view('approvals.index', compact('quotes'));
    }

    public function approve(Request $request, Quote $quote): RedirectResponse
    {
        if ($quote->status !== QuoteStatus::PendingApproval) {
            return back()->with('error', 'Quote is not pending approval.');
        }

        $quote->update([
            'status'      => QuoteStatus::Approved,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Quote {$quote->quote_number} approved.");
    }

    public function reject(Request $request, Quote $quote): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($quote->status !== QuoteStatus::PendingApproval) {
            return back()->with('error', 'Quote is not pending approval.');
        }

        $quote->update([
            'status'           => QuoteStatus::Rejected,
            'rejection_reason' => $request->rejection_reason,
            'rejected_at'      => now(),
        ]);

        return back()->with('success', "Quote {$quote->quote_number} rejected.");
    }
}
