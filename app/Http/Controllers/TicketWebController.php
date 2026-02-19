<?php

namespace App\Http\Controllers;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::with(['contact', 'assignee']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($channel = $request->get('channel')) {
            $query->where('channel', $channel);
        }

        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $tickets = $query->latest()->paginate(25)->withQueryString();

        $statusCounts = Ticket::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $agents   = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);

        return view('support.tickets.index', compact('tickets', 'statusCounts', 'agents', 'accounts'));
    }

    public function create(): View
    {
        $ticket   = null;
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $agents   = User::orderBy('name')->get(['id', 'name']);

        return view('support.tickets.create', compact('ticket', 'contacts', 'accounts', 'agents'));
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $priority = TicketPriority::from($validated['priority']);

        $ticket = Ticket::create([
            ...$validated,
            'tenant_id'     => auth()->user()->tenant_id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'status'        => TicketStatus::Open->value,
            'created_by'    => auth()->id(),
            'sla_due_at'    => now()->addHours($priority->slaHours()),
        ]);

        return redirect()->route('support.tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} created successfully.");
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['contact', 'account', 'assignee', 'creator', 'comments.user', 'comments.contact']);
        $agents = User::orderBy('name')->get(['id', 'name']);

        return view('support.tickets.show', compact('ticket', 'agents'));
    }

    public function edit(Ticket $ticket): View
    {
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $agents   = User::orderBy('name')->get(['id', 'name']);

        return view('support.tickets.create', compact('ticket', 'contacts', 'accounts', 'agents'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        // If status changes to resolved/closed, record timestamp
        if (isset($validated['status'])) {
            $newStatus = TicketStatus::from($validated['status']);

            if ($newStatus === TicketStatus::Resolved && ! $ticket->resolved_at) {
                $validated['resolved_at'] = now();
            }

            if ($newStatus === TicketStatus::Closed && ! $ticket->closed_at) {
                $validated['closed_at'] = now();
            }
        }

        // If priority changed, recalculate SLA
        if (isset($validated['priority']) && $validated['priority'] !== $ticket->priority->value) {
            $priority = TicketPriority::from($validated['priority']);
            $validated['sla_due_at'] = now()->addHours($priority->slaHours());
        }

        $ticket->update($validated);

        return redirect()->route('support.tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()->route('support.tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    public function addComment(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'body'        => ['required', 'string'],
            'is_internal' => ['boolean'],
        ]);

        TicketComment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'body'        => $request->body,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        // Record first agent response time
        if (! $ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        return back()->with('success', 'Reply added.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate(['status' => ['required', 'string']]);

        $status = TicketStatus::from($request->status);
        $update = ['status' => $status->value];

        if ($status === TicketStatus::Resolved && ! $ticket->resolved_at) {
            $update['resolved_at'] = now();
        }

        if ($status === TicketStatus::Closed && ! $ticket->closed_at) {
            $update['closed_at'] = now();
        }

        $ticket->update($update);

        return back()->with('success', "Ticket status changed to {$status->label()}.");
    }
}
