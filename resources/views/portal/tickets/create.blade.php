@extends('portal.layout')

@section('title', 'Submit a Ticket')

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-md-8">

    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Submit a Support Ticket</h1>
        <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
          Describe your issue and we'll get back to you as soon as possible.
        </p>
      </div>
      <a href="{{ route('portal.tickets.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> My Tickets
      </a>
    </div>

    <div class="ncv-card">
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('portal.tickets.store') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                   value="{{ old('subject') }}" placeholder="Brief summary of your issue" required>
            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Description</label>
            <textarea name="description" rows="6" class="form-control @error('description') is-invalid @enderror"
                      placeholder="Please describe your issue in detail. Include any steps to reproduce, error messages, or relevant details…">{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-4">
            <label class="form-label fw-medium">Urgency <span class="text-danger">*</span></label>
            <div class="row g-3">
              @foreach(\App\Enums\TicketPriority::cases() as $priority)
              <div class="col-6">
                <div class="form-check border rounded-3 p-3 priority-opt"
                     id="popt-{{ $priority->value }}"
                     onclick="selectPriority('{{ $priority->value }}')"
                     style="cursor:pointer;margin:0;">
                  <input class="form-check-input" type="radio" name="priority"
                         id="priority-{{ $priority->value }}" value="{{ $priority->value }}"
                         {{ old('priority', 'medium') === $priority->value ? 'checked' : '' }}>
                  <label class="form-check-label ms-2" for="priority-{{ $priority->value }}" style="cursor:pointer;">
                    <span class="badge bg-{{ $priority->color() }}-subtle text-{{ $priority->color() }} border border-{{ $priority->color() }}-subtle me-1">{{ $priority->label() }}</span>
                    <span style="color:var(--text-muted);font-size:.78rem;">
                      @if($priority->value === 'low') Non-urgent, general question
                      @elseif($priority->value === 'medium') Normal issue, some impact
                      @elseif($priority->value === 'high') Significant impact on work
                      @elseif($priority->value === 'critical') System down, urgent help needed
                      @endif
                    </span>
                  </label>
                </div>
              </div>
              @endforeach
            </div>
            @error('priority') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>

          <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-send me-1"></i> Submit Ticket
            </button>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
function selectPriority(value) {
  document.querySelectorAll('.priority-opt').forEach(el => {
    el.style.borderColor = '';
    el.style.background  = '';
  });
  const el = document.getElementById('popt-' + value);
  if (el) {
    el.style.borderColor = 'var(--accent-blue)';
    el.style.background  = 'rgba(59,130,246,.06)';
    el.querySelector('input[type=radio]').checked = true;
  }
}
// Init
const checked = document.querySelector('input[name=priority]:checked');
if (checked) selectPriority(checked.value);
</script>
@endpush
