@extends('layouts.app')

@section('title', isset($email) ? 'Edit Email Log' : 'Log an Email')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
      {{ isset($email) ? 'Edit Email Log' : 'Log an Email' }}
    </h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ isset($email) ? 'Update email log details.' : 'Record an email interaction.' }}
    </p>
  </div>
  <a href="{{ route('activity.emails.index') }}" class="btn btn-ghost btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

@if($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST"
      action="{{ isset($email) ? route('activity.emails.update', $email) : route('activity.emails.store') }}">
  @csrf
  @if(isset($email)) @method('PUT') @endif

  <div class="row g-4">

    <div class="col-12 col-lg-8">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Email Details</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                   value="{{ old('subject', $email->subject ?? '') }}" required placeholder="Email subject…">
            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">From</label>
              <input type="email" name="from_email" class="form-control"
                     value="{{ old('from_email', $email->from_email ?? '') }}" placeholder="sender@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">To</label>
              <input type="email" name="to_email" class="form-control"
                     value="{{ old('to_email', $email->to_email ?? '') }}" placeholder="recipient@example.com">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Body</label>
            <textarea name="body" class="form-control" rows="6"
                      placeholder="Email body or summary…">{{ old('body', $email->body ?? '') }}</textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Sent At</label>
              <input type="datetime-local" name="sent_at" class="form-control"
                     value="{{ old('sent_at', isset($email->sent_at) ? $email->sent_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">External Message ID</label>
              <input type="text" name="message_id" class="form-control"
                     value="{{ old('message_id', $email->message_id ?? '') }}"
                     placeholder="For deduplication with email clients">
            </div>
          </div>

        </div>
      </div>

      {{-- Tracking --}}
      <div class="ncv-card mt-4">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Tracking</h6></div>
        <div class="ncv-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Opened At</label>
              <input type="datetime-local" name="opened_at" class="form-control"
                     value="{{ old('opened_at', isset($email->opened_at) ? $email->opened_at->format('Y-m-d\TH:i') : '') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Clicked At</label>
              <input type="datetime-local" name="clicked_at" class="form-control"
                     value="{{ old('clicked_at', isset($email->clicked_at) ? $email->clicked_at->format('Y-m-d\TH:i') : '') }}">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Properties</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Direction <span class="text-danger">*</span></label>
            <select name="direction" class="form-select @error('direction') is-invalid @enderror" required>
              @foreach(\App\Enums\EmailDirection::cases() as $d)
                <option value="{{ $d->value }}" {{ old('direction', $email->direction?->value ?? 'outbound') === $d->value ? 'selected' : '' }}>
                  {{ $d->label() }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Source <span class="text-danger">*</span></label>
            <select name="source" class="form-select @error('source') is-invalid @enderror" required>
              @foreach(\App\Enums\EmailSource::cases() as $s)
                <option value="{{ $s->value }}" {{ old('source', $email->source?->value ?? 'manual') === $s->value ? 'selected' : '' }}>
                  {{ $s->label() }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <div class="ncv-card mt-4">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Related Record</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Relate To</label>
            <select id="emailableType" name="emailable_type" class="form-select"
                    onchange="updateEmailableSelect(this.value)">
              <option value="">— None —</option>
              <option value="App\Models\Contact" {{ old('emailable_type', $email->emailable_type ?? '') === 'App\Models\Contact' ? 'selected' : '' }}>Contact</option>
              <option value="App\Models\Lead"    {{ old('emailable_type', $email->emailable_type ?? '') === 'App\Models\Lead'    ? 'selected' : '' }}>Lead</option>
              <option value="App\Models\Account" {{ old('emailable_type', $email->emailable_type ?? '') === 'App\Models\Account' ? 'selected' : '' }}>Account</option>
            </select>
          </div>

          <div id="contactEmailable" class="emailable-select d-none">
            <label class="form-label fw-medium">Contact</label>
            <select name="emailable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($contacts as $c)
                <option value="{{ $c->id }}" {{ old('emailable_id', $email->emailable_id ?? '') == $c->id && old('emailable_type', $email->emailable_type ?? '') === 'App\Models\Contact' ? 'selected' : '' }}>
                  {{ $c->first_name }} {{ $c->last_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div id="leadEmailable" class="emailable-select d-none">
            <label class="form-label fw-medium">Lead</label>
            <select name="emailable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($leads as $l)
                <option value="{{ $l->id }}" {{ old('emailable_id', $email->emailable_id ?? '') == $l->id && old('emailable_type', $email->emailable_type ?? '') === 'App\Models\Lead' ? 'selected' : '' }}>
                  {{ $l->first_name }} {{ $l->last_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div id="accountEmailable" class="emailable-select d-none">
            <label class="form-label fw-medium">Account</label>
            <select name="emailable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($accounts as $a)
                <option value="{{ $a->id }}" {{ old('emailable_id', $email->emailable_id ?? '') == $a->id && old('emailable_type', $email->emailable_type ?? '') === 'App\Models\Account' ? 'selected' : '' }}>
                  {{ $a->name }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          {{ isset($email) ? 'Update Email Log' : 'Log Email' }}
        </button>
        <a href="{{ route('activity.emails.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </div>

  </div>
</form>

@push('scripts')
<script>
function updateEmailableSelect(type) {
  document.querySelectorAll('.emailable-select').forEach(el => el.classList.add('d-none'));
  if (type === 'App\\Models\\Contact') document.getElementById('contactEmailable').classList.remove('d-none');
  if (type === 'App\\Models\\Lead')    document.getElementById('leadEmailable').classList.remove('d-none');
  if (type === 'App\\Models\\Account') document.getElementById('accountEmailable').classList.remove('d-none');
}
updateEmailableSelect(document.getElementById('emailableType').value);
</script>
@endpush

@endsection
