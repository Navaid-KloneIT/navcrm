@extends('layouts.app')

@section('title', isset($ticket) ? 'Edit Ticket' : 'New Ticket')
@section('page-title', isset($ticket) ? 'Edit Ticket' : 'New Ticket')
@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Support</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('support.tickets.index') }}" class="ncv-breadcrumb-item">Tickets</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
      {{ isset($ticket) ? 'Edit ' . $ticket->ticket_number : 'New Ticket' }}
    </h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ isset($ticket) ? 'Update ticket details.' : 'Log a new support ticket.' }}
    </p>
  </div>
  <a href="{{ route('support.tickets.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<form method="POST"
      action="{{ isset($ticket) ? route('support.tickets.update', $ticket) : route('support.tickets.store') }}">
  @csrf
  @if(isset($ticket)) @method('PUT') @endif

  <div class="row g-4">

    {{-- Main Details --}}
    <div class="col-12 col-lg-8">
      <div class="ncv-card mb-4">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Ticket Details</h6>
        </div>
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                   value="{{ old('subject', $ticket->subject ?? '') }}" placeholder="Brief description of the issue">
            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Description</label>
            <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror"
                      placeholder="Detailed description of the issue…">{{ old('description', $ticket->description ?? '') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      {{-- Status (edit only) --}}
      @if(isset($ticket))
      <div class="ncv-card mb-4">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Status</h6>
        </div>
        <div class="ncv-card-body">
          <div class="row g-3">
            @foreach(\App\Enums\TicketStatus::cases() as $status)
            <div class="col-6 col-md-4">
              <div class="status-option border rounded-3 p-3 text-center" id="status-{{ $status->value }}"
                   onclick="selectStatus('{{ $status->value }}')" style="cursor:pointer;">
                <div class="fw-medium" style="font-size:.85rem;">{{ $status->label() }}</div>
              </div>
            </div>
            @endforeach
          </div>
          <input type="hidden" name="status" id="statusInput"
                 value="{{ old('status', $ticket->status->value) }}">
        </div>
      </div>
      @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-12 col-lg-4">

      {{-- Priority --}}
      <div class="ncv-card mb-4">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Priority <span class="text-danger">*</span></h6>
        </div>
        <div class="ncv-card-body">
          @foreach(\App\Enums\TicketPriority::cases() as $priority)
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="priority"
                   id="priority-{{ $priority->value }}" value="{{ $priority->value }}"
                   {{ old('priority', $ticket->priority->value ?? 'medium') === $priority->value ? 'checked' : '' }}
                   onchange="updateSlaInfo()">
            <label class="form-check-label d-flex align-items-center gap-2" for="priority-{{ $priority->value }}">
              <span class="badge bg-{{ $priority->color() }}-subtle text-{{ $priority->color() }} border border-{{ $priority->color() }}-subtle"
                    style="font-size:.72rem;">{{ $priority->label() }}</span>
              <span style="color:var(--text-muted);font-size:.8rem;">
                @if($priority->value === 'low') 72h SLA
                @elseif($priority->value === 'medium') 24h SLA
                @elseif($priority->value === 'high') 8h SLA
                @elseif($priority->value === 'critical') 4h SLA
                @endif
              </span>
            </label>
          </div>
          @endforeach
          @error('priority') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Channel --}}
      <div class="ncv-card mb-4">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Channel <span class="text-danger">*</span></h6>
        </div>
        <div class="ncv-card-body">
          <select name="channel" class="form-select @error('channel') is-invalid @enderror">
            @foreach(\App\Enums\TicketChannel::cases() as $channel)
              <option value="{{ $channel->value }}"
                {{ old('channel', $ticket->channel->value ?? 'manual') === $channel->value ? 'selected' : '' }}>
                {{ $channel->label() }}
              </option>
            @endforeach
          </select>
          @error('channel') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Assignment --}}
      <div class="ncv-card mb-4">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Assignment</h6>
        </div>
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Contact</label>
            <select name="contact_id" class="form-select">
              <option value="">— None —</option>
              @foreach($contacts as $contact)
                <option value="{{ $contact->id }}"
                  {{ old('contact_id', $ticket->contact_id ?? '') == $contact->id ? 'selected' : '' }}>
                  {{ $contact->first_name }} {{ $contact->last_name }} ({{ $contact->email }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Account</label>
            <select name="account_id" class="form-select">
              <option value="">— None —</option>
              @foreach($accounts as $account)
                <option value="{{ $account->id }}"
                  {{ old('account_id', $ticket->account_id ?? '') == $account->id ? 'selected' : '' }}>
                  {{ $account->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-0">
            <label class="form-label fw-medium">Assign To</label>
            <select name="assigned_to" class="form-select">
              <option value="">— Unassigned —</option>
              @foreach($agents as $agent)
                <option value="{{ $agent->id }}"
                  {{ old('assigned_to', $ticket->assigned_to ?? '') == $agent->id ? 'selected' : '' }}>
                  {{ $agent->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-{{ isset($ticket) ? 'check-lg' : 'ticket' }} me-1"></i>
        {{ isset($ticket) ? 'Save Changes' : 'Create Ticket' }}
      </button>
    </div>

  </div>
</form>

@endsection

@push('scripts')
<script>
function selectStatus(value) {
  document.getElementById('statusInput').value = value;
  document.querySelectorAll('.status-option').forEach(el => {
    el.style.borderColor = '';
    el.style.background  = '';
  });
  const el = document.getElementById('status-' + value);
  if (el) {
    el.style.borderColor = 'var(--accent-blue)';
    el.style.background  = 'rgba(59,130,246,.08)';
  }
}

// Initialise status selection on page load
const currentStatus = document.getElementById('statusInput')?.value;
if (currentStatus) selectStatus(currentStatus);
</script>
@endpush
