@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('page-title', $ticket->ticket_number)
@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Support</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('support.tickets.index') }}" class="ncv-breadcrumb-item">Tickets</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="h4 fw-semibold mb-0" style="color:var(--text-primary);">{{ $ticket->ticket_number }}</h1>
      <span class="badge bg-{{ $ticket->status->color() }}-subtle text-{{ $ticket->status->color() }} border border-{{ $ticket->status->color() }}-subtle">
        {{ $ticket->status->label() }}
      </span>
      <span class="badge bg-{{ $ticket->priority->color() }}-subtle text-{{ $ticket->priority->color() }} border border-{{ $ticket->priority->color() }}-subtle">
        {{ $ticket->priority->label() }}
      </span>
      @if($ticket->isSlaBreach())
        <span class="badge bg-danger text-white"><i class="bi bi-alarm-fill me-1"></i>SLA Breached</span>
      @elseif($ticket->isSlaWarning())
        <span class="badge bg-warning text-dark"><i class="bi bi-alarm me-1"></i>SLA Warning</span>
      @endif
    </div>
    <p class="mb-0" style="color:var(--text-secondary);font-size:.95rem;">{{ $ticket->subject }}</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('support.tickets.edit', $ticket) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('support.tickets.destroy', $ticket) }}"
          onsubmit="return confirm('Delete this ticket?')">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash me-1"></i> Delete
      </button>
    </form>
  </div>
</div>

<div class="row g-4">

  {{-- Left: Description + Comments --}}
  <div class="col-12 col-lg-8">

    {{-- Description --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Description</h6>
      </div>
      <div class="ncv-card-body">
        @if($ticket->description)
          <p class="mb-0" style="color:var(--text-secondary);white-space:pre-wrap;">{{ $ticket->description }}</p>
        @else
          <p class="mb-0" style="color:var(--text-muted);">No description provided.</p>
        @endif
      </div>
    </div>

    {{-- Comments Thread --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">Activity &amp; Replies</h6>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
          {{ $ticket->comments->count() }} comment{{ $ticket->comments->count() !== 1 ? 's' : '' }}
        </span>
      </div>
      <div class="ncv-card-body">
        @forelse($ticket->comments as $comment)
        <div class="d-flex gap-3 mb-4 {{ $comment->is_internal ? 'p-3 rounded-3' : '' }}"
             style="{{ $comment->is_internal ? 'background:rgba(245,158,11,.08);border:1px dashed rgba(245,158,11,.3);' : '' }}">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
               style="width:36px;height:36px;background:{{ $comment->user_id ? 'var(--accent-blue)' : '#10b981' }};color:white;font-size:.8rem;font-weight:700;">
            {{ strtoupper(substr($comment->author_name, 0, 2)) }}
          </div>
          <div class="flex-fill">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="fw-semibold" style="font-size:.875rem;color:var(--text-primary);">{{ $comment->author_name }}</span>
              @if($comment->is_internal)
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.68rem;">Internal Note</span>
              @endif
              <span style="color:var(--text-muted);font-size:.78rem;">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <div style="color:var(--text-secondary);font-size:.875rem;white-space:pre-wrap;">{{ $comment->body }}</div>
          </div>
        </div>
        @empty
          <p class="text-center mb-0" style="color:var(--text-muted);">No replies yet. Be the first to respond.</p>
        @endforelse
      </div>
    </div>

    {{-- Add Reply --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Add Reply</h6>
      </div>
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('support.tickets.comment', $ticket) }}">
          @csrf
          <div class="mb-3">
            <textarea name="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                      placeholder="Write your reply…">{{ old('body') }}</textarea>
            @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_internal" id="isInternal" value="1">
              <label class="form-check-label" for="isInternal" style="font-size:.875rem;">
                Internal note (only visible to agents)
              </label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-send me-1"></i> Send Reply
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  {{-- Right: Details + Quick Actions --}}
  <div class="col-12 col-lg-4">

    {{-- Quick Status Change --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Change Status</h6>
      </div>
      <div class="ncv-card-body d-flex flex-column gap-2">
        @foreach(\App\Enums\TicketStatus::cases() as $status)
          @if($status !== $ticket->status)
          <form method="POST" action="{{ route('support.tickets.status', $ticket) }}">
            @csrf
            <input type="hidden" name="status" value="{{ $status->value }}">
            <button type="submit" class="btn btn-outline-secondary btn-sm w-100 text-start">
              <span class="badge bg-{{ $status->color() }}-subtle text-{{ $status->color() }} me-2">{{ $status->label() }}</span>
              Set as {{ $status->label() }}
            </button>
          </form>
          @endif
        @endforeach
      </div>
    </div>

    {{-- Ticket Info --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Ticket Info</h6>
      </div>
      <div class="ncv-card-body">
        <dl class="mb-0" style="font-size:.875rem;">
          <dt class="text-muted fw-normal mb-1">Channel</dt>
          <dd class="mb-3">
            <i class="bi {{ $ticket->channel->icon() }} me-1"></i>
            {{ $ticket->channel->label() }}
          </dd>

          <dt class="text-muted fw-normal mb-1">Contact</dt>
          <dd class="mb-3">
            @if($ticket->contact)
              <a href="{{ route('contacts.show', $ticket->contact) }}" class="text-decoration-none">
                {{ $ticket->contact->full_name }}
              </a>
            @else
              <span style="color:var(--text-muted);">—</span>
            @endif
          </dd>

          <dt class="text-muted fw-normal mb-1">Account</dt>
          <dd class="mb-3">
            @if($ticket->account)
              <a href="{{ route('accounts.show', $ticket->account) }}" class="text-decoration-none">
                {{ $ticket->account->name }}
              </a>
            @else
              <span style="color:var(--text-muted);">—</span>
            @endif
          </dd>

          <dt class="text-muted fw-normal mb-1">Assigned To</dt>
          <dd class="mb-3">{{ $ticket->assignee?->name ?? 'Unassigned' }}</dd>

          <dt class="text-muted fw-normal mb-1">Created By</dt>
          <dd class="mb-3">{{ $ticket->creator?->name ?? '—' }}</dd>

          <dt class="text-muted fw-normal mb-1">Created</dt>
          <dd class="mb-3">{{ $ticket->created_at->format('M j, Y g:i A') }}</dd>

          @if($ticket->sla_due_at)
          <dt class="text-muted fw-normal mb-1">SLA Due</dt>
          <dd class="mb-3 {{ $ticket->isSlaBreach() ? 'text-danger fw-semibold' : '' }}">
            {{ $ticket->sla_due_at->format('M j, Y g:i A') }}
            @if($ticket->isSlaBreach())
              <span class="d-block small text-danger">Breached {{ $ticket->sla_due_at->diffForHumans() }}</span>
            @endif
          </dd>
          @endif

          @if($ticket->resolved_at)
          <dt class="text-muted fw-normal mb-1">Resolved</dt>
          <dd class="mb-0">{{ $ticket->resolved_at->format('M j, Y g:i A') }}</dd>
          @endif
        </dl>
      </div>
    </div>

    {{-- Quick Assign --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Re-assign</h6>
      </div>
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('support.tickets.update', $ticket) }}">
          @csrf @method('PUT')
          <input type="hidden" name="subject" value="{{ $ticket->subject }}">
          <input type="hidden" name="priority" value="{{ $ticket->priority->value }}">
          <input type="hidden" name="channel" value="{{ $ticket->channel->value }}">
          <select name="assigned_to" class="form-select form-select-sm mb-2">
            <option value="">— Unassigned —</option>
            @foreach($agents as $agent)
              <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                {{ $agent->name }}
              </option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Update Assignment</button>
        </form>
      </div>
    </div>

  </div>
</div>

@endsection
