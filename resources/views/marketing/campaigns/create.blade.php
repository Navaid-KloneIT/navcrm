@extends('layouts.app')

@section('title', isset($campaign) ? 'Edit Campaign' : 'New Campaign')
@section('page-title', isset($campaign) ? 'Edit Campaign' : 'New Campaign')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.campaigns.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Campaigns</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($campaign) ? 'Edit Campaign' : 'New Campaign' }}</h1>
        <p class="ncv-page-subtitle">{{ isset($campaign) ? 'Update campaign details and budget.' : 'Create a new marketing campaign.' }}</p>
      </div>
      <a href="{{ route('marketing.campaigns.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($campaign) ? route('marketing.campaigns.update', $campaign) : route('marketing.campaigns.store') }}">
      @csrf
      @if(isset($campaign)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Campaign Info --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-megaphone me-2" style="color:var(--ncv-blue-500);"></i>Campaign Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-8">
                  <label class="ncv-label" for="name">Campaign Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $campaign->name ?? '') }}"
                         placeholder="e.g. Spring Product Launch 2026" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="status">Status</label>
                  <select class="ncv-select" id="status" name="status">
                    @foreach(['draft'=>'Draft','active'=>'Active','paused'=>'Paused','completed'=>'Completed'] as $val=>$label)
                      <option value="{{ $val }}" {{ old('status', $campaign->status->value ?? 'draft') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <label class="ncv-label">Campaign Type <span class="required">*</span></label>
                  <div class="row g-2">
                    @foreach([
                      'email'       => ['icon'=>'bi-envelope',       'label'=>'Email',       'color'=>'#2563eb'],
                      'webinar'     => ['icon'=>'bi-camera-video',   'label'=>'Webinar',     'color'=>'#7c3aed'],
                      'event'       => ['icon'=>'bi-calendar-event', 'label'=>'Event',       'color'=>'#059669'],
                      'digital_ads' => ['icon'=>'bi-display',        'label'=>'Digital Ads', 'color'=>'#d97706'],
                      'direct_mail' => ['icon'=>'bi-mailbox',        'label'=>'Direct Mail', 'color'=>'#db2777'],
                    ] as $val => $cfg)
                    <div class="col-6 col-md">
                      <label class="ncv-type-card" id="typeCard_{{ $val }}"
                             style="display:flex;flex-direction:column;align-items:center;gap:.375rem;padding:.875rem;border-radius:.75rem;border:1.5px solid var(--border-color);cursor:pointer;transition:all .15s;text-align:center;">
                        <input type="radio" name="type" value="{{ $val }}" style="display:none;"
                               {{ old('type', $campaign->type->value ?? 'email') === $val ? 'checked' : '' }}
                               onchange="updateTypeCard()" />
                        <i class="bi {{ $cfg['icon'] }}" style="font-size:1.25rem;color:{{ $cfg['color'] }};"></i>
                        <span style="font-size:.78rem;font-weight:600;color:var(--text-secondary);">{{ $cfg['label'] }}</span>
                      </label>
                    </div>
                    @endforeach
                  </div>
                  @error('type')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="description">Description</label>
                  <textarea class="ncv-textarea" name="description" rows="3"
                            placeholder="Campaign objectives, target audience, key messages…">{{ old('description', $campaign->description ?? '') }}</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Schedule --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-calendar3 me-2" style="color:var(--ncv-blue-500);"></i>Schedule</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="start_date">Start Date</label>
                  <input type="date" class="ncv-input" id="start_date" name="start_date"
                         value="{{ old('start_date', $campaign?->start_date?->format('Y-m-d') ?? '') }}" />
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="end_date">End Date</label>
                  <input type="date" class="ncv-input" id="end_date" name="end_date"
                         value="{{ old('end_date', $campaign?->end_date?->format('Y-m-d') ?? '') }}" />
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="owner_id">Campaign Owner</label>
                  <select class="ncv-select" id="owner_id" name="owner_id">
                    <option value="">— Unassigned —</option>
                    @foreach($owners as $owner)
                      <option value="{{ $owner->id }}"
                              {{ old('owner_id', $campaign->owner_id ?? auth()->id()) == $owner->id ? 'selected' : '' }}>
                        {{ $owner->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Budget & Revenue --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-currency-dollar me-2" style="color:var(--ncv-blue-500);"></i>Budget & ROI</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="planned_budget">Planned Budget ($)</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="number" step="0.01" min="0" class="ncv-input" id="planned_budget"
                           name="planned_budget"
                           value="{{ old('planned_budget', $campaign->planned_budget ?? '') }}"
                           placeholder="0.00" />
                  </div>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="actual_budget">Actual Spend ($)</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="number" step="0.01" min="0" class="ncv-input" id="actual_budget"
                           name="actual_budget"
                           value="{{ old('actual_budget', $campaign->actual_budget ?? '') }}"
                           placeholder="0.00" />
                  </div>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="target_revenue">Target Revenue ($)</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-graph-up ncv-input-icon"></i>
                    <input type="number" step="0.01" min="0" class="ncv-input" id="target_revenue"
                           name="target_revenue"
                           value="{{ old('target_revenue', $campaign->target_revenue ?? '') }}"
                           placeholder="0.00" />
                  </div>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="actual_revenue">Actual Revenue ($)</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-graph-up-arrow ncv-input-icon"></i>
                    <input type="number" step="0.01" min="0" class="ncv-input" id="actual_revenue"
                           name="actual_revenue"
                           value="{{ old('actual_revenue', $campaign->actual_revenue ?? '') }}"
                           placeholder="0.00" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('marketing.campaigns.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($campaign) ? 'Update Campaign' : 'Create Campaign' }}
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
  const typeValues = ['email','webinar','event','digital_ads','direct_mail'];
  const typeColors = {
    email:       '#2563eb', webinar: '#7c3aed', event:       '#059669',
    digital_ads: '#d97706', direct_mail: '#db2777'
  };

  function updateTypeCard() {
    const checked = document.querySelector('input[name="type"]:checked')?.value;
    typeValues.forEach(v => {
      const card = document.getElementById('typeCard_' + v);
      if (!card) return;
      if (v === checked) {
        card.style.borderColor = typeColors[v];
        card.style.background  = typeColors[v] + '12';
      } else {
        card.style.borderColor = 'var(--border-color)';
        card.style.background  = '';
      }
    });
  }
  updateTypeCard();
  document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', updateTypeCard));
</script>
@endpush
