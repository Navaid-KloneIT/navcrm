@extends('layouts.app')

@section('title', isset($survey) && $survey->exists ? 'Edit Survey' : 'New Survey')
@section('page-title', isset($survey) && $survey->exists ? 'Edit Survey' : 'New Survey')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Customer Success</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('success.surveys.index') }}" class="ncv-breadcrumb-item" style="color:inherit;text-decoration:none;">Surveys</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($survey) && $survey->exists ? 'Edit Survey' : 'New Survey' }}</h1>
        <p class="ncv-page-subtitle">{{ isset($survey) && $survey->exists ? 'Update survey details and settings.' : 'Create a new NPS or CSAT survey.' }}</p>
      </div>
      <a href="{{ route('success.surveys.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($survey) && $survey->exists ? route('success.surveys.update', $survey) : route('success.surveys.store') }}">
      @csrf
      @if(isset($survey) && $survey->exists) @method('PUT') @endif

      <div class="row g-3">

        {{-- Left Column --}}
        <div class="col-12 col-lg-8">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-clipboard2-data me-2" style="color:var(--ncv-blue-500);"></i>Survey Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="name">Survey Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $survey?->name) }}"
                         placeholder="e.g. Q1 2026 Customer Satisfaction" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="description">Description</label>
                  <textarea class="ncv-input @error('description') is-invalid @enderror"
                            id="description" name="description" rows="4"
                            placeholder="Describe the purpose of this survey...">{{ old('description', $survey?->description) }}</textarea>
                  @error('description')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-4">

          {{-- Settings Card --}}
          <div class="ncv-card mb-3">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-gear me-2" style="color:var(--ncv-blue-500);"></i>Settings</h6>
            </div>
            <div class="ncv-card-body">
              <div class="mb-3">
                <label class="ncv-label" for="type">Type <span class="required">*</span></label>
                <select class="ncv-select @error('type') is-invalid @enderror" id="type" name="type" required>
                  <option value="">Select type...</option>
                  <option value="nps" {{ old('type', $survey?->type?->value) === 'nps' ? 'selected' : '' }}>NPS</option>
                  <option value="csat" {{ old('type', $survey?->type?->value) === 'csat' ? 'selected' : '' }}>CSAT</option>
                </select>
                @error('type')<span class="ncv-form-error">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="ncv-label" for="status">Status</label>
                <select class="ncv-select @error('status') is-invalid @enderror" id="status" name="status">
                  <option value="draft" {{ old('status', $survey?->status?->value ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="active" {{ old('status', $survey?->status?->value) === 'active' ? 'selected' : '' }}>Active</option>
                  <option value="closed" {{ old('status', $survey?->status?->value) === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                @error('status')<span class="ncv-form-error">{{ $message }}</span>@enderror
              </div>
            </div>
          </div>

          {{-- Targeting Card --}}
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-bullseye me-2" style="color:var(--ncv-blue-500);"></i>Targeting</h6>
            </div>
            <div class="ncv-card-body">
              <div class="mb-3">
                <label class="ncv-label" for="account_id">Account</label>
                <select class="ncv-select @error('account_id') is-invalid @enderror" id="account_id" name="account_id">
                  <option value="">All accounts</option>
                  @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('account_id', $survey?->account_id) == $account->id ? 'selected' : '' }}>
                      {{ $account->name }}
                    </option>
                  @endforeach
                </select>
                @error('account_id')<span class="ncv-form-error">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="ncv-label" for="ticket_id">Ticket <small class="text-muted">(for CSAT)</small></label>
                <select class="ncv-select @error('ticket_id') is-invalid @enderror" id="ticket_id" name="ticket_id">
                  <option value="">No specific ticket</option>
                  @foreach($tickets as $ticket)
                    <option value="{{ $ticket->id }}" {{ old('ticket_id', $survey?->ticket_id) == $ticket->id ? 'selected' : '' }}>
                      {{ $ticket->ticket_number }} - {{ Str::limit($ticket->subject, 40) }}
                    </option>
                  @endforeach
                </select>
                @error('ticket_id')<span class="ncv-form-error">{{ $message }}</span>@enderror
              </div>
            </div>
          </div>

        </div>

        {{-- Bottom Actions --}}
        <div class="col-12">
          <div class="d-flex align-items-center justify-content-between pt-2">
            <div class="d-flex gap-2">
              <button type="submit" class="ncv-btn ncv-btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                {{ isset($survey) && $survey->exists ? 'Update Survey' : 'Create Survey' }}
              </button>
              <a href="{{ route('success.surveys.index') }}" class="ncv-btn ncv-btn-ghost">Cancel</a>
            </div>
            @if(isset($survey) && $survey->exists)
            <form method="POST" action="{{ route('success.surveys.destroy', $survey) }}"
                  onsubmit="return confirm('Are you sure you want to delete this survey? This action cannot be undone.')">
              @csrf @method('DELETE')
              <button type="submit" class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:.625rem;">
                <i class="bi bi-trash me-1"></i> Delete Survey
              </button>
            </form>
            @endif
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

@endsection
