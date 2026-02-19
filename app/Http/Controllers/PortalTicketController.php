<?php

namespace App\Http\Controllers;

use App\Enums\TicketChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class PortalTicketController extends Controller
{
    private function portalContact(): Contact
    {
        return Contact::withoutGlobalScopes()->findOrFail(session('portal_contact_id'));
    }

    public function index(): View
    {
        $contact = $this->portalContact();

        $tickets = Ticket::withoutGlobalScopes()
            ->where('contact_id', $contact->id)
            ->latest()
            ->paginate(15);

        return view('portal.tickets.index', compact('contact', 'tickets'));
    }

    public function create(): View
    {
        $contact = $this->portalContact();

        return view('portal.tickets.create', compact('contact'));
    }

    public function store(Request $request): RedirectResponse
    {
        $contact = $this->portalContact();

        $validated = $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', new Enum(TicketPriority::class)],
        ]);

        $priority = TicketPriority::from($validated['priority']);

        $ticket = Ticket::withoutGlobalScopes()->create([
            'tenant_id'     => $contact->tenant_id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject'       => $validated['subject'],
            'description'   => $validated['description'] ?? null,
            'priority'      => $priority->value,
            'channel'       => TicketChannel::Portal->value,
            'status'        => TicketStatus::Open->value,
            'contact_id'    => $contact->id,
            'sla_due_at'    => now()->addHours($priority->slaHours()),
        ]);

        return redirect()->route('portal.tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} submitted successfully. We'll get back to you soon.");
    }

    public function show(Ticket $ticket): View
    {
        $contact = $this->portalContact();

        // Ensure the ticket belongs to this portal contact
        if ($ticket->contact_id !== $contact->id) {
            abort(403);
        }

        $ticket->loadMissing(['comments' => function ($q) {
            $q->where('is_internal', false)->orderBy('created_at');
        }, 'comments.user', 'comments.contact']);

        return view('portal.tickets.show', compact('contact', 'ticket'));
    }

    public function addComment(Request $request, Ticket $ticket): RedirectResponse
    {
        $contact = $this->portalContact();

        if ($ticket->contact_id !== $contact->id) {
            abort(403);
        }

        $request->validate(['body' => ['required', 'string']]);

        TicketComment::create([
            'ticket_id'   => $ticket->id,
            'contact_id'  => $contact->id,
            'body'        => $request->body,
            'is_internal' => false,
        ]);

        return back()->with('success', 'Reply submitted.');
    }
}
