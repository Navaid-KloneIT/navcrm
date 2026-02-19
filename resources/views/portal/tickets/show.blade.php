@extends('portal.layout')

@section('title', $ticket->ticket_number)

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="h5 fw-semibold mb-0" style="color:var(--text-primary);">{{ $ticket->ticket_number }}</h1>
      <span class="badge bg-{{ $ticket->status->color() }}-subtle text-{{ $ticket->status->color() }} border border-{{ $ticket->status->color() }}-subtle">
        {{ $ticket->status->label() }}
      </span>
      <span class="badge bg-{{ $ticket->priority->color() }}-subtle text-{{ $ticket->priority->color() }} border border-{{ $ticket->priority->color() }}-subtle">
        {{ $ticket->priority->label() }}
      </span>
    </div>
    <p class="mb-1" style="color:var(--text-secondary);">{{ $ticket->subject }}</p>
    <p class="mb-0" style="color:var(--text-muted);font-size:.8rem;">
      Submitted {{ $ticket->created_at->format('M j, Y \a\t g:i A') }}
    </p>
  </div>
  <a href="{{ route('portal.tickets.index') }}" class="btn btn-outline-secondary btn-sm flex-shrink-0">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<div class="row g-4">
  <div class="col-12 col-md-8">

    {{-- Original Description --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle d-flex align-items-center justify-content-center"
               style="width:32px;height:32px;background:#10b981;color:white;font-size:.75rem;font-weight:700;">
            {{ strtoupper(substr($contact->first_name, 0, 2)) }}
          </div>
          <div>
            <div class="fw-semibold" style="font-size:.875rem;">{{ $contact->full_name }}</div>
            <div style="color:var(--text-muted);font-size:.75rem;">{{ $ticket->created_at->diffForHumans() }}</div>
          </div>
        </div>
      </div>
      <div class="ncv-card-body">
        @if($ticket->description)
          <p class="mb-0" style="white-space:pre-wrap;color:var(--text-secondary);">{{ $ticket->description }}</p>
        @else
          <p class="mb-0" style="color:var(--text-muted);">No description provided.</p>
        @endif
      </div>
    </div>

    {{-- Replies --}}
    @foreach($ticket->comments as $comment)
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle d-flex align-items-center justify-content-center"
               style="width:32px;height:32px;background:{{ $comment->user_id ? '#3b82f6' : '#10b981' }};color:white;font-size:.75rem;font-weight:700;">
            {{ strtoupper(substr($comment->author_name, 0, 2)) }}
          </div>
          <div>
            <div class="fw-semibold d-flex align-items-center gap-2" style="font-size:.875rem;">
              {{ $comment->author_name }}
              @if($comment->user_id)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.65rem;">Support Agent</span>
              @endif
            </div>
            <div style="color:var(--text-muted);font-size:.75rem;">{{ $comment->created_at->diffForHumans() }}</div>
          </div>
        </div>
      </div>
      <div class="ncv-card-body">
        <p class="mb-0" style="white-space:pre-wrap;color:var(--text-secondary);">{{ $comment->body }}</p>
      </div>
    </div>
    @endforeach

    {{-- Reply Form (only if ticket is not closed) --}}
    @if(! in_array($ticket->status, [\App\Enums\TicketStatus::Closed]))
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Add a Reply</h6>
      </div>
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('portal.tickets.comment', $ticket) }}">
          @csrf
          <div class="mb-3">
            <textarea name="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                      placeholder="Add more details or ask a follow-up question…">{{ old('body') }}</textarea>
            @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-send me-1"></i> Send Reply
          </button>
        </form>
      </div>
    </div>
    @else
    <div class="ncv-card">
      <div class="ncv-card-body text-center py-4" style="color:var(--text-muted);">
        <i class="bi bi-check-circle" style="font-size:1.5rem;color:#10b981;"></i>
        <p class="mt-2 mb-0">This ticket is closed. If you need further assistance, please submit a new ticket.</p>
      </div>
    </div>
    @endif

  </div>

  {{-- Info Sidebar --}}
  <div class="col-12 col-md-4">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Ticket Details</h6>
      </div>
      <div class="ncv-card-body">
        <dl class="mb-0" style="font-size:.875rem;">
          <dt class="text-muted fw-normal mb-1">Ticket Number</dt>
          <dd class="mb-3">{{ $ticket->ticket_number }}</dd>

          <dt class="text-muted fw-normal mb-1">Status</dt>
          <dd class="mb-3">
            <span class="badge bg-{{ $ticket->status->color() }}-subtle text-{{ $ticket->status->color() }} border border-{{ $ticket->status->color() }}-subtle">
              {{ $ticket->status->label() }}
            </span>
          </dd>

          <dt class="text-muted fw-normal mb-1">Priority</dt>
          <dd class="mb-3">
            <span class="badge bg-{{ $ticket->priority->color() }}-subtle text-{{ $ticket->priority->color() }} border border-{{ $ticket->priority->color() }}-subtle">
              {{ $ticket->priority->label() }}
            </span>
          </dd>

          <dt class="text-muted fw-normal mb-1">Submitted</dt>
          <dd class="mb-3">{{ $ticket->created_at->format('M j, Y g:i A') }}</dd>

          @if($ticket->resolved_at)
          <dt class="text-muted fw-normal mb-1">Resolved</dt>
          <dd class="mb-0">{{ $ticket->resolved_at->format('M j, Y g:i A') }}</dd>
          @endif
        </dl>
      </div>
    </div>
  </div>
</div>

@endsection
