@extends('layouts.app')

@section('title', 'Tickets')
@section('page-title', 'Tickets')
@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Support</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Tickets</h1>
    <p class="mb-0" style="color:var(--text-muted); font-size:.875rem;">Manage customer support cases and requests.</p>
  </div>
  <a href="{{ route('support.tickets.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
    <i class="bi bi-plus-lg"></i> New Ticket
  </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @php
    $statuses = [
      ['key' => 'open',        'label' => 'Open',        'icon' => 'bi-ticket',        'color' => '#3b82f6'],
      ['key' => 'in_progress', 'label' => 'In Progress', 'icon' => 'bi-arrow-repeat',  'color' => '#0ea5e9'],
      ['key' => 'escalated',   'label' => 'Escalated',   'icon' => 'bi-exclamation-triangle', 'color' => '#f59e0b'],
      ['key' => 'resolved',    'label' => 'Resolved',    'icon' => 'bi-check-circle',  'color' => '#10b981'],
    ];
  @endphp
  @foreach($statuses as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card p-3 h-100">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width:40px;height:40px;background:{{ $stat['color'] }}18;">
          <i class="bi {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:1.1rem;"></i>
        </div>
        <div>
          <div class="fw-bold fs-5" style="color:var(--text-primary);">{{ $statusCounts[$stat['key']] ?? 0 }}</div>
          <div style="color:var(--text-muted);font-size:.8rem;">{{ $stat['label'] }}</div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-body">
    <form method="GET" action="{{ route('support.tickets.index') }}" class="row g-3 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label form-label-sm">Search</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Subject or ticket number…" value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          @foreach(\App\Enums\TicketStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Priority</label>
        <select name="priority" class="form-select form-select-sm">
          <option value="">All</option>
          @foreach(\App\Enums\TicketPriority::cases() as $p)
            <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Assigned To</label>
        <select name="assigned_to" class="form-select form-select-sm">
          <option value="">All Agents</option>
          @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Channel</label>
        <select name="channel" class="form-select form-select-sm">
          <option value="">All Channels</option>
          @foreach(\App\Enums\TicketChannel::cases() as $c)
            <option value="{{ $c->value }}" {{ request('channel') === $c->value ? 'selected' : '' }}>{{ $c->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Account</label>
        <select name="account_id" class="form-select form-select-sm">
          <option value="">All Accounts</option>
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
      </div>
      <div class="col-6 col-md-1 d-flex gap-2 align-self-end">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
        <a href="{{ route('support.tickets.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">✕</a>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($tickets->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-ticket-perforated" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No tickets found</p>
        <p class="small mb-3">No tickets match your filters.</p>
        <a href="{{ route('support.tickets.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-1"></i> Create Ticket
        </a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle); border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Ticket</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Priority</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Status</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Contact</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Assigned To</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">SLA</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Created</th>
              <th class="py-3 pe-4" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($tickets as $ticket)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <a href="{{ route('support.tickets.show', $ticket) }}"
                   class="fw-medium text-decoration-none" style="color:var(--accent-blue);">
                  {{ $ticket->ticket_number }}
                </a>
                <div style="color:var(--text-muted);font-size:.8rem;max-width:260px;" class="text-truncate">
                  {{ $ticket->subject }}
                </div>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $ticket->priority->color() }}-subtle text-{{ $ticket->priority->color() }} border border-{{ $ticket->priority->color() }}-subtle"
                      style="font-size:.72rem;font-weight:600;">
                  {{ $ticket->priority->label() }}
                </span>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $ticket->status->color() }}-subtle text-{{ $ticket->status->color() }} border border-{{ $ticket->status->color() }}-subtle"
                      style="font-size:.72rem;font-weight:600;">
                  {{ $ticket->status->label() }}
                </span>
              </td>
              <td class="py-3" style="color:var(--text-secondary);">
                {{ $ticket->contact?->full_name ?? '—' }}
              </td>
              <td class="py-3" style="color:var(--text-secondary);">
                {{ $ticket->assignee?->name ?? '<span style="color:var(--text-muted);">Unassigned</span>' }}
              </td>
              <td class="py-3">
                @if($ticket->sla_due_at)
                  @if($ticket->isSlaBreach())
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.72rem;">
                      <i class="bi bi-alarm-fill me-1"></i>SLA Breached
                    </span>
                  @elseif($ticket->isSlaWarning())
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.72rem;">
                      <i class="bi bi-alarm me-1"></i>Due {{ $ticket->sla_due_at->diffForHumans() }}
                    </span>
                  @else
                    <span style="color:var(--text-muted);font-size:.8rem;">{{ $ticket->sla_due_at->diffForHumans() }}</span>
                  @endif
                @else
                  <span style="color:var(--text-muted);">—</span>
                @endif
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $ticket->created_at->format('M j, Y') }}
              </td>
              <td class="py-3 pe-4">
                <div class="d-flex gap-1">
                  <a href="{{ route('support.tickets.show', $ticket) }}"
                     class="btn btn-ghost btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="{{ route('support.tickets.edit', $ticket) }}"
                     class="btn btn-ghost btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form method="POST" action="{{ route('support.tickets.destroy', $ticket) }}"
                        onsubmit="return confirm('Delete ticket {{ $ticket->ticket_number }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm text-danger" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($tickets->hasPages())
      <div class="d-flex align-items-center justify-content-between px-4 py-3"
           style="border-top:1px solid var(--border-color);">
        <span style="color:var(--text-muted);font-size:.875rem;">
          Showing {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }} of {{ $tickets->total() }}
        </span>
        {{ $tickets->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
