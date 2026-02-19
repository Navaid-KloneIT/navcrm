@extends('portal.layout')

@section('title', 'My Tickets')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">My Tickets</h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      All support requests submitted by {{ $contact->first_name }}.
    </p>
  </div>
  <a href="{{ route('portal.tickets.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> New Ticket
  </a>
</div>

<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($tickets->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-ticket-perforated" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No tickets yet</p>
        <p class="small mb-3">Submit a ticket and we'll get back to you.</p>
        <a href="{{ route('portal.tickets.create') }}" class="btn btn-primary btn-sm">Submit a Ticket</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle);border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Ticket</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Priority</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Status</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Submitted</th>
              <th class="py-3 pe-4"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($tickets as $ticket)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <div class="fw-medium" style="color:var(--text-primary);">{{ $ticket->ticket_number }}</div>
                <div style="color:var(--text-muted);font-size:.8rem;">{{ Str::limit($ticket->subject, 55) }}</div>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $ticket->priority->color() }}-subtle text-{{ $ticket->priority->color() }} border border-{{ $ticket->priority->color() }}-subtle"
                      style="font-size:.72rem;">{{ $ticket->priority->label() }}</span>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $ticket->status->color() }}-subtle text-{{ $ticket->status->color() }} border border-{{ $ticket->status->color() }}-subtle"
                      style="font-size:.72rem;">{{ $ticket->status->label() }}</span>
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $ticket->created_at->format('M j, Y') }}
              </td>
              <td class="py-3 pe-4">
                <a href="{{ route('portal.tickets.show', $ticket) }}"
                   class="btn btn-ghost btn-sm" title="View">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($tickets->hasPages())
      <div class="d-flex justify-content-center px-4 py-3" style="border-top:1px solid var(--border-color);">
        {{ $tickets->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
