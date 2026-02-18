@extends('layouts.app')

@section('title', isset($emailCampaign) ? 'Edit Email Campaign' : 'New Email Campaign')
@section('page-title', isset($emailCampaign) ? 'Edit Email Campaign' : 'New Email Campaign')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.email-campaigns.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Email Campaigns</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($emailCampaign) ? 'Edit Email Campaign' : 'New Email Campaign' }}</h1>
        <p class="ncv-page-subtitle">Configure sender, subject, scheduling, and A/B test settings.</p>
      </div>
      <a href="{{ route('marketing.email-campaigns.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($emailCampaign) ? route('marketing.email-campaigns.update', $emailCampaign) : route('marketing.email-campaigns.store') }}">
      @csrf
      @if(isset($emailCampaign)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Type Selector --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-envelope me-2" style="color:var(--ncv-blue-500);"></i>Campaign Type</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="type-option" id="opt_single" style="display:block;padding:1rem;border-radius:.75rem;border:2px solid var(--border-color);cursor:pointer;transition:all .15s;">
                    <input type="radio" name="type" value="single" style="display:none;"
                           {{ old('type', $emailCampaign->type ?? 'single') === 'single' ? 'checked' : '' }}
                           onchange="syncType()" />
                    <div style="font-weight:700;font-size:.875rem;color:var(--text-primary);margin-bottom:.25rem;">
                      <i class="bi bi-send me-2" style="color:#2563eb;"></i>Single Send
                    </div>
                    <p style="font-size:.78rem;color:var(--text-muted);margin:0;">One-time email blast to all recipients.</p>
                  </label>
                </div>
                <div class="col-12 col-md-4">
                  <label class="type-option" id="opt_drip" style="display:block;padding:1rem;border-radius:.75rem;border:2px solid var(--border-color);cursor:pointer;transition:all .15s;">
                    <input type="radio" name="type" value="drip" style="display:none;"
                           {{ old('type', $emailCampaign->type ?? '') === 'drip' ? 'checked' : '' }}
                           onchange="syncType()" />
                    <div style="font-weight:700;font-size:.875rem;color:var(--text-primary);margin-bottom:.25rem;">
                      <i class="bi bi-arrow-repeat me-2" style="color:#059669;"></i>Drip Sequence
                    </div>
                    <p style="font-size:.78rem;color:var(--text-muted);margin:0;">Automated series of emails over time.</p>
                  </label>
                </div>
                <div class="col-12 col-md-4">
                  <label class="type-option" id="opt_ab_test" style="display:block;padding:1rem;border-radius:.75rem;border:2px solid var(--border-color);cursor:pointer;transition:all .15s;">
                    <input type="radio" name="type" value="ab_test" style="display:none;"
                           {{ old('type', $emailCampaign->type ?? '') === 'ab_test' ? 'checked' : '' }}
                           onchange="syncType()" />
                    <div style="font-weight:700;font-size:.875rem;color:var(--text-primary);margin-bottom:.25rem;">
                      <i class="bi bi-diagram-2 me-2" style="color:#7c3aed;"></i>A/B Test
                    </div>
                    <p style="font-size:.78rem;color:var(--text-muted);margin:0;">Test two subject lines or templates.</p>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Basic Info --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="name">Campaign Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $emailCampaign->name ?? '') }}"
                         placeholder="e.g. March Newsletter" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="campaign_id">Parent Campaign</label>
                  <select class="ncv-select" id="campaign_id" name="campaign_id">
                    <option value="">— None —</option>
                    @foreach($campaigns as $c)
                      <option value="{{ $c->id }}" {{ old('campaign_id', $emailCampaign->campaign_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="from_name">From Name</label>
                  <input type="text" class="ncv-input" id="from_name" name="from_name"
                         value="{{ old('from_name', $emailCampaign->from_name ?? '') }}"
                         placeholder="NavCRM Team" />
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="from_email">From Email</label>
                  <input type="email" class="ncv-input" id="from_email" name="from_email"
                         value="{{ old('from_email', $emailCampaign->from_email ?? '') }}"
                         placeholder="hello@yourcompany.com" />
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="email_template_id">Email Template</label>
                  <select class="ncv-select" id="email_template_id" name="email_template_id">
                    <option value="">— None —</option>
                    @foreach($templates as $t)
                      <option value="{{ $t->id }}" {{ old('email_template_id', $emailCampaign->email_template_id ?? '') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Subject (single/drip) --}}
        <div class="col-12" id="singleSubjectSection">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-fonts me-2" style="color:var(--ncv-blue-500);"></i>Subject Line</h6>
            </div>
            <div class="ncv-card-body">
              <label class="ncv-label" for="subject">Subject <span class="required">*</span></label>
              <input type="text" class="ncv-input" id="subject" name="subject"
                     value="{{ old('subject', $emailCampaign->subject ?? '') }}"
                     placeholder="e.g. Your March update from NavCRM 🚀" />
            </div>
          </div>
        </div>

        {{-- A/B Subjects --}}
        <div class="col-12" id="abSubjectSection" style="display:none;">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-diagram-2 me-2" style="color:#7c3aed;"></i>A/B Subject Lines</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="ncv-label">Subject A</label>
                  <div class="ncv-input-group">
                    <span class="ncv-input-icon" style="font-weight:800;color:#2563eb;font-size:.8rem;">A</span>
                    <input type="text" class="ncv-input" name="subject_a"
                           value="{{ old('subject_a', $emailCampaign->subject_a ?? '') }}"
                           placeholder="Variant A subject line" />
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label">Subject B</label>
                  <div class="ncv-input-group">
                    <span class="ncv-input-icon" style="font-weight:800;color:#7c3aed;font-size:.8rem;">B</span>
                    <input type="text" class="ncv-input" name="subject_b"
                           value="{{ old('subject_b', $emailCampaign->subject_b ?? '') }}"
                           placeholder="Variant B subject line" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Schedule & Owner --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-clock me-2" style="color:var(--ncv-blue-500);"></i>Schedule</h6>
            </div>
            <div class="ncv-card-body">
              <label class="ncv-label" for="scheduled_at">Send Date & Time</label>
              <input type="datetime-local" class="ncv-input" id="scheduled_at" name="scheduled_at"
                     value="{{ old('scheduled_at', isset($emailCampaign) && $emailCampaign->scheduled_at ? $emailCampaign->scheduled_at->format('Y-m-d\TH:i') : '') }}" />
              <p style="font-size:.75rem;color:var(--text-muted);margin-top:.375rem;">Leave blank to save as draft.</p>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-person me-2" style="color:var(--ncv-blue-500);"></i>Assignment</h6>
            </div>
            <div class="ncv-card-body">
              <label class="ncv-label" for="owner_id">Owner</label>
              <select class="ncv-select" id="owner_id" name="owner_id">
                <option value="">— Unassigned —</option>
                @foreach($owners as $owner)
                  <option value="{{ $owner->id }}" {{ old('owner_id', $emailCampaign->owner_id ?? auth()->id()) == $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('marketing.email-campaigns.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($emailCampaign) ? 'Update' : 'Create Campaign' }}
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
  function syncType() {
    const val = document.querySelector('input[name="type"]:checked')?.value ?? 'single';
    const opts = ['single','drip','ab_test'];
    const colors = { single:'#2563eb', drip:'#059669', ab_test:'#7c3aed' };
    opts.forEach(o => {
      const el = document.getElementById('opt_' + o);
      if (!el) return;
      el.style.borderColor = val === o ? colors[o] : 'var(--border-color)';
      el.style.background  = val === o ? colors[o] + '10' : '';
    });
    document.getElementById('singleSubjectSection').style.display = val !== 'ab_test' ? '' : 'none';
    document.getElementById('abSubjectSection').style.display     = val === 'ab_test' ? '' : 'none';
  }
  syncType();
  document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', syncType));
</script>
@endpush
