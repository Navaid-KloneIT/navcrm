@extends('layouts.app')

@section('title', 'Lead Profile')
@section('page-title', 'Lead Profile')

@section('breadcrumb-items')
  <a href="{{ route('leads.index') }}" style="color:inherit;text-decoration:none;">Leads</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row g-3">

  {{-- Lead Card --}}
  <div class="col-12">
    <div class="ncv-card" style="background:linear-gradient(135deg,#0d1f4e,#1e3a8f,#2563eb);border:none;color:#fff;position:relative;overflow:hidden;">
      <div style="position:absolute;width:250px;height:250px;border-radius:50%;background:rgba(255,255,255,.04);top:-80px;right:-60px;"></div>
      <div class="ncv-card-body" style="padding:1.75rem;position:relative;z-index:1;">
        <div class="d-flex align-items-start gap-3 flex-wrap">
          <div style="width:72px;height:72px;border-radius:1.125rem;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;flex-shrink:0;">
            AT
          </div>
          <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.25rem;flex-wrap:wrap;">
              <h1 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0;">{{ $lead->name ?? 'Alex Turner' }}</h1>
              <span style="display:inline-flex;align-items:center;gap:.35rem;background:rgba(239,68,68,.25);color:#fca5a5;border-radius:9999px;padding:.25rem .75rem;font-size:.78rem;font-weight:700;">
                🔥 Hot Lead
              </span>
            </div>
            <div style="font-size:.875rem;color:rgba(255,255,255,.75);display:flex;gap:1.25rem;flex-wrap:wrap;margin-top:.35rem;">
              <span><i class="bi bi-building"></i> {{ $lead->company ?? 'CloudCo Inc' }}</span>
              <span><i class="bi bi-envelope"></i> {{ $lead->email ?? 'alex@cloudco.io' }}</span>
              <span><i class="bi bi-telephone"></i> {{ $lead->phone ?? '+1 555-0201' }}</span>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap" style="position:relative;z-index:1;">
            <a href="{{ route('leads.edit', $lead->id ?? 1) }}"
               class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
              <i class="bi bi-pencil"></i> Edit
            </a>
            <button class="ncv-btn ncv-btn-sm" style="background:#10b981;color:#fff;border:none;box-shadow:0 4px 12px rgba(16,185,129,.4);"
                    onclick="openConvertModal()">
              <i class="bi bi-arrow-right-circle-fill"></i> Convert Lead
            </button>
          </div>
        </div>

        {{-- Status pills --}}
        <div style="display:flex;gap:.75rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.1);flex-wrap:wrap;">
          @foreach([
            ['label'=>'Status',     'value'=>'Contacted'],
            ['label'=>'Source',     'value'=>'Web Form'],
            ['label'=>'Assigned',   'value'=>'You'],
            ['label'=>'Lead Score', 'value'=>'85 / 100'],
            ['label'=>'Created',    'value'=>'Feb 18, 2026'],
          ] as $meta)
          <div style="text-align:center;min-width:90px;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.5);">{{ $meta['label'] }}</div>
            <div style="font-size:.9rem;font-weight:700;color:#fff;margin-top:.2rem;">{{ $meta['value'] }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Left: Details --}}
  <div class="col-12 col-lg-4">
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-lightning-charge me-2" style="color:var(--ncv-blue-500);"></i>Lead Details</h6>
        <a href="{{ route('leads.edit', $lead->id ?? 1) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil"></i></a>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['l'=>'Company',     'v'=>'CloudCo Inc'],
          ['l'=>'Title',       'v'=>'Head of Engineering'],
          ['l'=>'Department',  'v'=>'Engineering'],
          ['l'=>'Email',       'v'=>'alex@cloudco.io', 'type'=>'email'],
          ['l'=>'Phone',       'v'=>'+1 555-0201'],
          ['l'=>'Website',     'v'=>'cloudco.io', 'type'=>'link'],
          ['l'=>'Address',     'v'=>'Austin, TX, USA'],
          ['l'=>'Employees',   'v'=>'51–200'],
          ['l'=>'Lead Source', 'v'=>'Web Form'],
          ['l'=>'Industry',    'v'=>'SaaS / Cloud'],
          ['l'=>'Rating',      'v'=>'Hot'],
          ['l'=>'Annual Rev.', 'v'=>'$8M'],
        ] as $row)
        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.5rem 0;border-bottom:1px solid var(--border-color);font-size:.85rem;">
          <span style="min-width:95px;font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;padding-top:2px;">{{ $row['l'] }}</span>
          <span style="color:var(--text-secondary);">
            @if(isset($row['type']) && $row['type'] === 'email')
              <a href="mailto:{{ $row['v'] }}" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $row['v'] }}</a>
            @elseif(isset($row['type']) && $row['type'] === 'link')
              <a href="https://{{ $row['v'] }}" target="_blank" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $row['v'] }} <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i></a>
            @else
              {{ $row['v'] }}
            @endif
          </span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Lead Scoring --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-speedometer2 me-2" style="color:var(--ncv-blue-500);"></i>Lead Score</h6>
      </div>
      <div class="ncv-card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.875rem;">
          <span style="font-size:2rem;font-weight:800;color:#2563eb;letter-spacing:-.05em;">85</span>
          <span style="font-size:.8rem;color:var(--text-muted);">out of 100</span>
          <span style="background:#fee2e2;color:#b91c1c;border-radius:9999px;padding:.3rem .875rem;font-size:.8rem;font-weight:800;">🔥 Hot</span>
        </div>
        <div class="ncv-progress-bar mb-3" style="height:10px;">
          <div class="ncv-progress-fill" style="width:85%;background:linear-gradient(90deg,#ef4444,#f97316);"></div>
        </div>
        @foreach([
          ['factor'=>'Budget confirmed',        'pts'=>20,'max'=>20,'color'=>'#10b981'],
          ['factor'=>'Authority (decision maker)','pts'=>15,'max'=>20,'color'=>'#2563eb'],
          ['factor'=>'Need expressed',           'pts'=>20,'max'=>20,'color'=>'#10b981'],
          ['factor'=>'Timeline within 3 months', 'pts'=>15,'max'=>20,'color'=>'#2563eb'],
          ['factor'=>'Engaged 3+ times',         'pts'=>10,'max'=>15,'color'=>'#f59e0b'],
          ['factor'=>'Company size match',       'pts'=>5, 'max'=>5, 'color'=>'#10b981'],
        ] as $factor)
        <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.5rem;">
          <i class="bi bi-{{ $factor['pts'] === $factor['max'] ? 'check-circle-fill' : 'dash-circle-fill' }}" style="color:{{ $factor['color'] }};font-size:.875rem;flex-shrink:0;"></i>
          <span style="flex:1;font-size:.82rem;color:var(--text-secondary);">{{ $factor['factor'] }}</span>
          <span style="font-size:.78rem;font-weight:700;color:var(--text-primary);">+{{ $factor['pts'] }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Right: Activity + Notes --}}
  <div class="col-12 col-lg-8">

    {{-- Quick actions --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
        <div class="d-flex gap-2 flex-wrap">
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-telephone"></i> Log Call</button>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-envelope"></i> Send Email</button>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-calendar-plus"></i> Schedule Meeting</button>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-sticky"></i> Add Note</button>
          <select class="ncv-select ms-auto" style="width:180px;height:34px;font-size:.8rem;">
            <option>Change Status…</option>
            <option>→ New</option>
            <option>→ Contacted</option>
            <option>→ Qualified</option>
            <option>→ Recycled</option>
          </select>
        </div>
      </div>
    </div>

    {{-- Activity Timeline --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-activity me-2" style="color:var(--ncv-blue-500);"></i>Activity Timeline</h6>
      </div>
      <div class="ncv-card-body">
        <ul class="ncv-timeline">
          @foreach([
            ['icon'=>'bi-telephone-fill','bg'=>'#dbeafe','color'=>'#2563eb','title'=>'Inbound call — 18 min','time'=>'Today, 11:00 AM','desc'=>'Alex reached out with questions about pricing. Very interested in Enterprise plan. Requested a demo.'],
            ['icon'=>'bi-envelope-fill', 'bg'=>'#d1fae5','color'=>'#059669','title'=>'Follow-up email sent','time'=>'Yesterday, 3:30 PM','desc'=>'Sent product overview and case studies per Alex\'s request.'],
            ['icon'=>'bi-file-earmark-text','bg'=>'#ede9fe','color'=>'#7c3aed','title'=>'Lead captured via Web Form','time'=>'Feb 17, 9:00 AM','desc'=>'Auto-captured from website contact form. IP: Austin, TX. Source: Google Ads (Campaign: Q1-2026-SaaS).'],
          ] as $act)
          <li class="ncv-timeline-item">
            <div class="ncv-timeline-icon" style="background:{{ $act['bg'] }};color:{{ $act['color'] }};"><i class="bi {{ $act['icon'] }}" style="font-size:.8rem;"></i></div>
            <div class="ncv-timeline-body">
              <div class="ncv-timeline-title">{{ $act['title'] }}</div>
              <div class="ncv-timeline-time">{{ $act['time'] }}</div>
              <div class="ncv-timeline-desc">{{ $act['desc'] }}</div>
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</div>

{{-- CONVERT MODAL --}}
<div class="ncv-modal-overlay" id="convertModal" style="display:none;">
  <div class="ncv-modal" style="max-width:560px;">
    <div class="ncv-modal-header">
      <h5 class="ncv-modal-title">
        <i class="bi bi-arrow-right-circle-fill me-2" style="color:#10b981;"></i>
        Convert Lead to Opportunity
      </h5>
      <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="closeConvertModal()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="ncv-modal-body">
      <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.25rem;">
        Converting <strong>Alex Turner</strong> will create a Contact, optionally an Account, and an Opportunity.
      </p>
      <div class="row g-2">
        <div class="col-12">
          <div style="display:flex;align-items:center;justify-content:space-between;background:#f0f9ff;border:1px solid #bae6fd;border-radius:.625rem;padding:.75rem 1rem;margin-bottom:.5rem;">
            <div style="font-size:.875rem;font-weight:600;color:var(--text-primary);">
              <i class="bi bi-person-check-fill me-2" style="color:#0891b2;"></i> Create Contact
            </div>
            <input type="checkbox" checked style="accent-color:#2563eb;width:18px;height:18px;" />
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.625rem;padding:.75rem 1rem;margin-bottom:.5rem;">
            <div style="font-size:.875rem;font-weight:600;color:var(--text-primary);">
              <i class="bi bi-building-check me-2" style="color:#15803d;"></i> Create Account (CloudCo Inc)
            </div>
            <input type="checkbox" checked style="accent-color:#2563eb;width:18px;height:18px;" />
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:.625rem;padding:.75rem 1rem;margin-bottom:1rem;">
            <div style="font-size:.875rem;font-weight:600;color:var(--text-primary);">
              <i class="bi bi-bar-chart-fill me-2" style="color:#7c3aed;"></i> Create Opportunity
            </div>
            <input type="checkbox" checked style="accent-color:#2563eb;width:18px;height:18px;" />
          </div>
        </div>
        <div class="col-12 col-md-6">
          <label class="ncv-label">Opportunity Name</label>
          <input type="text" class="ncv-input" value="CloudCo Inc — Deal" />
        </div>
        <div class="col-12 col-md-6">
          <label class="ncv-label">Pipeline Stage</label>
          <select class="ncv-select">
            <option>Prospecting</option>
            <option>Qualification</option>
            <option selected>Proposal</option>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="ncv-label">Deal Amount ($)</label>
          <input type="number" class="ncv-input" placeholder="0.00" />
        </div>
        <div class="col-12 col-md-6">
          <label class="ncv-label">Close Date</label>
          <input type="date" class="ncv-input" value="{{ date('Y-m-d', strtotime('+30 days')) }}" />
        </div>
      </div>
    </div>
    <div class="ncv-modal-footer">
      <button class="ncv-btn ncv-btn-outline" onclick="closeConvertModal()">Cancel</button>
      <form method="POST" action="{{ route('leads.convert', $lead->id ?? 1) }}" style="display:inline;">
        @csrf
        <button type="submit" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-arrow-right-circle-fill"></i> Convert Now
        </button>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function openConvertModal()  { document.getElementById('convertModal').style.display = 'flex'; }
  function closeConvertModal() { document.getElementById('convertModal').style.display = 'none'; }
  document.getElementById('convertModal').addEventListener('click', function(e) {
    if (e.target === this) closeConvertModal();
  });
</script>
@endpush
