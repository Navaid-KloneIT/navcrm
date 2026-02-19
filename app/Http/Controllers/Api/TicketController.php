<?php

namespace App\Http\Controllers\Api;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
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

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $tickets = $query->paginate($request->get('per_page', 25));

        return response()->json($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', new Enum(TicketPriority::class)],
            'channel'     => ['required', 'string'],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $priority = TicketPriority::from($validated['priority']);

        $ticket = Ticket::create([
            ...$validated,
            'ticket_number' => Ticket::generateTicketNumber(),
            'status'        => TicketStatus::Open->value,
            'created_by'    => auth()->id(),
            'sla_due_at'    => now()->addHours($priority->slaHours()),
        ]);

        return response()->json($ticket->load(['contact', 'assignee']), 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        return response()->json($ticket->load(['contact', 'account', 'assignee', 'creator', 'comments.user']));
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'subject'     => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', new Enum(TicketStatus::class)],
            'priority'    => ['sometimes', new Enum(TicketPriority::class)],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (isset($validated['status'])) {
            $status = TicketStatus::from($validated['status']);
            if ($status === TicketStatus::Resolved && ! $ticket->resolved_at) {
                $validated['resolved_at'] = now();
            }
            if ($status === TicketStatus::Closed && ! $ticket->closed_at) {
                $validated['closed_at'] = now();
            }
        }

        $ticket->update($validated);

        return response()->json($ticket->fresh(['contact', 'assignee']));
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(null, 204);
    }

    public function comments(Ticket $ticket): JsonResponse
    {
        $comments = $ticket->comments()->with(['user', 'contact'])->get();

        return response()->json($comments);
    }

    public function addComment(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'body'        => ['required', 'string'],
            'is_internal' => ['boolean'],
        ]);

        $comment = TicketComment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'body'        => $validated['body'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        if (! $ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        return response()->json($comment->load('user'), 201);
    }
}
