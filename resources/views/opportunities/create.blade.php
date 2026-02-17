@extends('layouts.app')

@section('title', isset($opportunity) ? 'Edit Deal' : 'New Deal')
@section('page-title', isset($opportunity) ? 'Edit Deal' : 'New Deal')

@section('breadcrumb-items')
  <a href="{{ route('opportunities.index') }}" style="color:inherit;text-decoration:none;">Pipeline</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($opportunity) ? 'Edit Deal' : 'New Deal' }}</h1>
        <p class="ncv-page-subtitle">{{ isset($opportunity) ? 'Update opportunity details.' : 'Create a new sales opportunity.' }}</p>
      </div>
      <a href="{{ route('opportunities.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($opportunity) ? route('opportunities.update', $opportunity->id) : route('opportunities.store') }}">
      @csrf
      @if(isset($opportunity)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Core Details --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-bar-chart me-2" style="color:var(--ncv-blue-500);"></i>Opportunity Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="name">Deal Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $opportunity->name ?? '') }}"
                         placeholder="e.g. Acme Corporation — Enterprise License" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="account_id">Account <span class="required">*</span></label>
                  <select class="ncv-select @error('account_id') is-invalid @enderror" id="account_id" name="account_id" required>
                    <option value="">— Select Account —</option>
                    <option value="1" {{ old('account_id', $opportunity->account_id ?? '') == 1 ? 'selected' : '' }}>Acme Corporation</option>
                    <option value="2" {{ old('account_id', $opportunity->account_id ?? '') == 2 ? 'selected' : '' }}>TechStart Inc</option>
                    <option value="3" {{ old('account_id', $opportunity->account_id ?? '') == 3 ? 'selected' : '' }}>Globex Inc</option>
                  </select>
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="contact_id">Primary Contact</label>
                  <select class="ncv-select" id="contact_id" name="contact_id">
                    <option value="">— Select Contact —</option>
                    <option value="1">Sarah Johnson</option>
                    <option value="2">Michael Chen</option>
                    <option value="3">Emma Williams</option>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="amount">Deal Value ($) <span class="required">*</span></label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="number" step="0.01" class="ncv-input @error('amount') is-invalid @enderror"
                           id="amount" name="amount"
                           value="{{ old('amount', $opportunity->amount ?? '') }}"
                           placeholder="0.00" required oninput="updateWeighted()" />
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="stage">Pipeline Stage <span class="required">*</span></label>
                  <select class="ncv-select" id="stage" name="stage" required onchange="updateStageDefaults(this.value)">
                    @foreach(['Prospecting'=>20,'Qualification'=>30,'Proposal'=>55,'Negotiation'=>75,'Closed Won'=>100,'Closed Lost'=>0] as $s => $prob)
                    <option value="{{ $s }}" data-prob="{{ $prob }}" {{ old('stage', $opportunity->stage ?? 'Prospecting') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="probability">Probability (%) <span class="required">*</span></label>
                  <input type="number" min="0" max="100" class="ncv-input"
                         id="probability" name="probability"
                         value="{{ old('probability', $opportunity->probability ?? 20) }}"
                         oninput="updateWeighted()" />
                  <div class="ncv-form-hint">Weighted: <strong id="weightedVal">$0</strong></div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="close_date">Expected Close Date <span class="required">*</span></label>
                  <input type="date" class="ncv-input" id="close_date" name="close_date"
                         value="{{ old('close_date', $opportunity->close_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+30 days'))) }}"
                         required />
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="lead_source">Lead Source</label>
                  <select class="ncv-select" id="lead_source" name="lead_source">
                    @foreach(['Referral','Web Form','Cold Email','LinkedIn','Trade Show','Google Ads','Other'] as $src)
                    <option value="{{ $src }}" {{ old('lead_source', $opportunity->lead_source ?? '') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="competitor">Primary Competitor</label>
                  <input type="text" class="ncv-input" id="competitor" name="competitor"
                         value="{{ old('competitor', $opportunity->competitor ?? '') }}"
                         placeholder="e.g. Salesforce, HubSpot" />
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Forecast Category --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-graph-up me-2" style="color:var(--ncv-blue-500);"></i>Forecast</h6>
            </div>
            <div class="ncv-card-body">
              <label class="ncv-label">Forecast Category</label>
              <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach([
                  ['val'=>'pipeline',     'label'=>'Pipeline',     'desc'=>'Early stage, low certainty',     'color'=>'#64748b'],
                  ['val'=>'best_case',    'label'=>'Best Case',    'desc'=>'Optimistic scenario',            'color'=>'#2563eb'],
                  ['val'=>'commit',       'label'=>'Commit',       'desc'=>'High confidence will close',     'color'=>'#10b981'],
                  ['val'=>'closed',       'label'=>'Closed',       'desc'=>'Already won or lost',            'color'=>'#8b5cf6'],
                ] as $cat)
                <label style="display:flex;align-items:center;gap:.875rem;padding:.625rem .875rem;border-radius:.625rem;border:1.5px solid var(--border-color);cursor:pointer;transition:all .15s;" class="forecast-radio-lbl">
                  <input type="radio" name="forecast_category" value="{{ $cat['val'] }}" style="accent-color:{{ $cat['color'] }};width:16px;height:16px;" {{ old('forecast_category', $opportunity->forecast_category ?? 'pipeline') === $cat['val'] ? 'checked' : '' }} />
                  <div>
                    <div style="font-size:.875rem;font-weight:700;color:var(--text-primary);">{{ $cat['label'] }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted);">{{ $cat['desc'] }}</div>
                  </div>
                </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        {{-- Next Steps & Description --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-list-check me-2" style="color:var(--ncv-blue-500);"></i>Additional Info</h6>
            </div>
            <div class="ncv-card-body">
              <div class="ncv-form-group">
                <label class="ncv-label" for="next_steps">Next Steps</label>
                <textarea class="ncv-textarea" id="next_steps" name="next_steps" rows="3"
                          placeholder="What needs to happen to advance this deal?">{{ old('next_steps', $opportunity->next_steps ?? '') }}</textarea>
              </div>
              <div class="ncv-form-group">
                <label class="ncv-label" for="description">Description</label>
                <textarea class="ncv-textarea" id="description" name="description" rows="3"
                          placeholder="Opportunity overview, key requirements, notes…">{{ old('description', $opportunity->description ?? '') }}</textarea>
              </div>
              <div class="ncv-form-group">
                <label class="ncv-label" for="assigned_to">Assigned To</label>
                <select class="ncv-select" id="assigned_to" name="assigned_to">
                  <option value="1" selected>{{ auth()->user()?->name ?? 'You' }}</option>
                  <option value="2">John Smith</option>
                  <option value="3">Emma Williams</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('opportunities.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($opportunity) ? 'Update Deal' : 'Create Deal' }}
            </button>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

@endsection

@push('scripts')
<script>
  function updateWeighted() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const prob   = parseFloat(document.getElementById('probability').value) || 0;
    const weighted = (amount * prob / 100).toLocaleString('en-US', { style:'currency', currency:'USD', maximumFractionDigits:0 });
    document.getElementById('weightedVal').textContent = weighted;
  }

  function updateStageDefaults(stage) {
    const sel = document.getElementById('stage');
    const opt = sel.querySelector(`option[value="${stage}"]`);
    if (opt) {
      document.getElementById('probability').value = opt.dataset.prob || 20;
      updateWeighted();
    }
  }

  // Init
  updateWeighted();
</script>
@endpush
