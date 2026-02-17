@extends('layouts.app')

@section('title', isset($lead) ? 'Edit Lead' : 'New Lead')
@section('page-title', isset($lead) ? 'Edit Lead' : 'New Lead')

@section('breadcrumb-items')
  <a href="{{ route('leads.index') }}" style="color:inherit;text-decoration:none;">Leads</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($lead) ? 'Edit Lead' : 'New Lead' }}</h1>
        <p class="ncv-page-subtitle">Capture and qualify a new potential customer.</p>
      </div>
      <a href="{{ route('leads.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($lead) ? route('leads.update', $lead->id) : route('leads.store') }}">
      @csrf
      @if(isset($lead)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Lead Info --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-lightning-charge me-2" style="color:var(--ncv-blue-500);"></i>Lead Information</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="first_name">First Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('first_name') is-invalid @enderror"
                         id="first_name" name="first_name"
                         value="{{ old('first_name', $lead->first_name ?? '') }}"
                         placeholder="Alex" required />
                  @error('first_name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="last_name">Last Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('last_name') is-invalid @enderror"
                         id="last_name" name="last_name"
                         value="{{ old('last_name', $lead->last_name ?? '') }}"
                         placeholder="Turner" required />
                  @error('last_name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="title">Job Title</label>
                  <input type="text" class="ncv-input" id="title" name="title"
                         value="{{ old('title', $lead->title ?? '') }}"
                         placeholder="Head of Engineering" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="company">Company <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('company') is-invalid @enderror"
                         id="company" name="company"
                         value="{{ old('company', $lead->company ?? '') }}"
                         placeholder="CloudCo Inc" required />
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="website">Company Website</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-globe2 ncv-input-icon"></i>
                    <input type="url" class="ncv-input" id="website" name="website"
                           value="{{ old('website', $lead->website ?? '') }}"
                           placeholder="https://cloudco.io" />
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="email">Email <span class="required">*</span></label>
                  <div class="ncv-input-group">
                    <i class="bi bi-envelope ncv-input-icon"></i>
                    <input type="email" class="ncv-input @error('email') is-invalid @enderror"
                           id="email" name="email"
                           value="{{ old('email', $lead->email ?? '') }}"
                           placeholder="alex@cloudco.io" required />
                  </div>
                  @error('email')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="phone">Phone</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-telephone ncv-input-icon"></i>
                    <input type="tel" class="ncv-input" id="phone" name="phone"
                           value="{{ old('phone', $lead->phone ?? '') }}"
                           placeholder="+1 (555) 000-0000" />
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="mobile">Mobile</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-phone ncv-input-icon"></i>
                    <input type="tel" class="ncv-input" id="mobile" name="mobile"
                           value="{{ old('mobile', $lead->mobile ?? '') }}"
                           placeholder="+1 (555) 000-0000" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Qualification --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-sliders me-2" style="color:var(--ncv-blue-500);"></i>Qualification</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="status">Lead Status</label>
                  <select class="ncv-select" id="status" name="status">
                    @foreach(['New','Contacted','Qualified','Converted','Recycled'] as $s)
                    <option value="{{ $s }}" {{ old('status', $lead->status ?? 'New') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <label class="ncv-label">Lead Score</label>
                  <div style="display:flex;gap:.625rem;">
                    @foreach(['Hot'=>['bg'=>'#fee2e2','color'=>'#b91c1c','icon'=>'🔥'],'Warm'=>['bg'=>'#fef3c7','color'=>'#92400e','icon'=>'🌡️'],'Cold'=>['bg'=>'#dbeafe','color'=>'#1e40af','icon'=>'❄️']] as $score => $cfg)
                    <label style="flex:1;display:flex;align-items:center;justify-content:center;gap:.35rem;padding:.625rem;border-radius:.625rem;border:1.5px solid {{ old('score', $lead->score ?? 'Warm') === $score ? $cfg['color'] : 'var(--border-color)' }};background:{{ old('score', $lead->score ?? 'Warm') === $score ? $cfg['bg'] : '#fff' }};cursor:pointer;font-size:.8rem;font-weight:700;color:{{ old('score', $lead->score ?? 'Warm') === $score ? $cfg['color'] : 'var(--text-muted)' }};" id="scoreLabel{{ $score }}">
                      <input type="radio" name="score" value="{{ $score }}" style="display:none;"
                             {{ old('score', $lead->score ?? 'Warm') === $score ? 'checked' : '' }}
                             onchange="updateScoreLabel()" />
                      {{ $cfg['icon'] }} {{ $score }}
                    </label>
                    @endforeach
                  </div>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="lead_source">Lead Source</label>
                  <select class="ncv-select" id="lead_source" name="lead_source">
                    @foreach(['Web Form','Cold Email','LinkedIn','Referral','Trade Show','Google Ads','Partnership','Phone Inquiry','Other'] as $src)
                    <option value="{{ $src }}" {{ old('lead_source', $lead->lead_source ?? '') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="assigned_to">Assign To</label>
                  <select class="ncv-select" id="assigned_to" name="owner_id">
                    <option value="">— Unassigned —</option>
                    @foreach($owners ?? [] as $owner)
                      <option value="{{ $owner->id }}" {{ old('owner_id', $lead->owner_id ?? auth()->id()) == $owner->id ? 'selected' : '' }}>
                        {{ $owner->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Company Info --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-building me-2" style="color:var(--ncv-blue-500);"></i>Company Info</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="industry">Industry</label>
                  <select class="ncv-select" id="industry" name="industry">
                    <option value="">— Select —</option>
                    @foreach(['Technology','Finance','Healthcare','Manufacturing','Retail','Education','Real Estate','Media','Other'] as $ind)
                    <option value="{{ $ind }}" {{ old('industry', $lead->industry ?? '') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="employees">Company Size</label>
                  <select class="ncv-select" id="employees" name="employees">
                    <option value="">— Select —</option>
                    @foreach(['1–10','11–50','51–200','201–500','501–1000','1000+'] as $sz)
                    <option value="{{ $sz }}" {{ old('employees', $lead->employees ?? '') === $sz ? 'selected' : '' }}>{{ $sz }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="annual_revenue">Estimated Revenue</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="text" class="ncv-input" id="annual_revenue" name="annual_revenue"
                           value="{{ old('annual_revenue', $lead->annual_revenue ?? '') }}"
                           placeholder="e.g. 8000000" />
                  </div>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="city">City / Region</label>
                  <input type="text" class="ncv-input" id="city" name="city"
                         value="{{ old('city', $lead->city ?? '') }}"
                         placeholder="Austin, TX" />
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Description --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-sticky me-2" style="color:var(--ncv-blue-500);"></i>Description / Notes</h6>
            </div>
            <div class="ncv-card-body">
              <textarea class="ncv-textarea" name="description" rows="4"
                        placeholder="Key details about this lead — pain points, budget, timeline, how they found us…">{{ old('description', $lead->description ?? '') }}</textarea>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('leads.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($lead) ? 'Update Lead' : 'Create Lead' }}
            </button>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

@endsection
