@extends('layouts.app')

@section('title', isset($call) ? 'Edit Call Log' : 'Log a Call')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
      {{ isset($call) ? 'Edit Call Log' : 'Log a Call' }}
    </h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ isset($call) ? 'Update call log details.' : 'Record an inbound or outbound call.' }}
    </p>
  </div>
  <a href="{{ route('activity.calls.index') }}" class="btn btn-ghost btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

@if($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST"
      action="{{ isset($call) ? route('activity.calls.update', $call) : route('activity.calls.store') }}">
  @csrf
  @if(isset($call)) @method('PUT') @endif

  <div class="row g-4">

    <div class="col-12 col-lg-8">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Call Details</h6></div>
        <div class="ncv-card-body">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Direction <span class="text-danger">*</span></label>
              <select name="direction" class="form-select @error('direction') is-invalid @enderror" required>
                @foreach(\App\Enums\CallDirection::cases() as $d)
                  <option value="{{ $d->value }}" {{ old('direction', $call->direction?->value ?? 'outbound') === $d->value ? 'selected' : '' }}>
                    {{ $d->label() }}
                  </option>
                @endforeach
              </select>
              @error('direction')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Outcome <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach(\App\Enums\CallStatus::cases() as $s)
                  <option value="{{ $s->value }}" {{ old('status', $call->status?->value ?? 'completed') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                  </option>
                @endforeach
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Phone Number</label>
              <input type="text" name="phone_number" class="form-control"
                     value="{{ old('phone_number', $call->phone_number ?? '') }}"
                     placeholder="+1 555 000 0000">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-medium">Duration (seconds)</label>
              <input type="number" name="duration" class="form-control" min="0"
                     value="{{ old('duration', $call->duration ?? '') }}"
                     placeholder="e.g. 120">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-medium">Called At <span class="text-danger">*</span></label>
              <input type="datetime-local" name="called_at" class="form-control @error('called_at') is-invalid @enderror"
                     value="{{ old('called_at', isset($call->called_at) ? $call->called_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
              @error('called_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Notes</label>
            <textarea name="notes" class="form-control" rows="4"
                      placeholder="Summary, action items, or follow-up notes…">{{ old('notes', $call->notes ?? '') }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Recording URL</label>
            <input type="url" name="recording_url" class="form-control @error('recording_url') is-invalid @enderror"
                   value="{{ old('recording_url', $call->recording_url ?? '') }}"
                   placeholder="https://voip-provider.com/recordings/…">
            @error('recording_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Related Record</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Relate To</label>
            <select id="loggableType" name="loggable_type" class="form-select"
                    onchange="updateLoggableSelect(this.value)">
              <option value="">— None —</option>
              <option value="App\Models\Contact" {{ old('loggable_type', $call->loggable_type ?? '') === 'App\Models\Contact' ? 'selected' : '' }}>Contact</option>
              <option value="App\Models\Lead"    {{ old('loggable_type', $call->loggable_type ?? '') === 'App\Models\Lead'    ? 'selected' : '' }}>Lead</option>
              <option value="App\Models\Account" {{ old('loggable_type', $call->loggable_type ?? '') === 'App\Models\Account' ? 'selected' : '' }}>Account</option>
            </select>
          </div>

          <div id="contactLoggable" class="loggable-select d-none">
            <label class="form-label fw-medium">Contact</label>
            <select name="loggable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($contacts as $c)
                <option value="{{ $c->id }}" {{ old('loggable_id', $call->loggable_id ?? '') == $c->id && old('loggable_type', $call->loggable_type ?? '') === 'App\Models\Contact' ? 'selected' : '' }}>
                  {{ $c->first_name }} {{ $c->last_name }}
                  @if($c->phone) — {{ $c->phone }} @endif
                </option>
              @endforeach
            </select>
          </div>
          <div id="leadLoggable" class="loggable-select d-none">
            <label class="form-label fw-medium">Lead</label>
            <select name="loggable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($leads as $l)
                <option value="{{ $l->id }}" {{ old('loggable_id', $call->loggable_id ?? '') == $l->id && old('loggable_type', $call->loggable_type ?? '') === 'App\Models\Lead' ? 'selected' : '' }}>
                  {{ $l->first_name }} {{ $l->last_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div id="accountLoggable" class="loggable-select d-none">
            <label class="form-label fw-medium">Account</label>
            <select name="loggable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($accounts as $a)
                <option value="{{ $a->id }}" {{ old('loggable_id', $call->loggable_id ?? '') == $a->id && old('loggable_type', $call->loggable_type ?? '') === 'App\Models\Account' ? 'selected' : '' }}>
                  {{ $a->name }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          {{ isset($call) ? 'Update Call Log' : 'Log Call' }}
        </button>
        <a href="{{ route('activity.calls.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </div>

  </div>
</form>

@push('scripts')
<script>
function updateLoggableSelect(type) {
  document.querySelectorAll('.loggable-select').forEach(el => el.classList.add('d-none'));
  if (type === 'App\\Models\\Contact') document.getElementById('contactLoggable').classList.remove('d-none');
  if (type === 'App\\Models\\Lead')    document.getElementById('leadLoggable').classList.remove('d-none');
  if (type === 'App\\Models\\Account') document.getElementById('accountLoggable').classList.remove('d-none');
}
updateLoggableSelect(document.getElementById('loggableType').value);
</script>
@endpush

@endsection
