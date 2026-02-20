@extends('layouts.app')

@section('title', 'Deal Details')
@section('page-title', 'Deal Details')

@section('breadcrumb-items')
  <a href="{{ route('opportunities.index') }}" style="color:inherit;text-decoration:none;">Pipeline</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Hero --}}
<div class="ncv-card mb-3" style="background:linear-gradient(135deg,#0d1f4e,#1e3a8f,#2563eb);border:none;color:#fff;overflow:hidden;position:relative;">
  <div style="position:absolute;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.04);top:-80px;right:-60px;"></div>
  <div class="ncv-card-body" style="padding:1.75rem;position:relative;z-index:1;">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <h1 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .5rem;">
          {{ $opportunity->title ?? 'TechStart Expansion' }}
        </h1>
        <div style="display:flex;gap:1.25rem;font-size:.875rem;color:rgba(255,255,255,.75);flex-wrap:wrap;">
          <span><i class="bi bi-building"></i> {{ $opportunity->account->name ?? 'TechStart Inc' }}</span>
          <span><i class="bi bi-calendar3"></i> Close: <strong style="color:#fff;">{{ $opportunity->close_date ?? 'Feb 15, 2026' }}</strong></span>
          <span><i class="bi bi-person"></i> Owner: {{ $opportunity->owner->name ?? 'John Smith' }}</span>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('opportunities.edit', $opportunity->id ?? 1) }}"
           class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
          <i class="bi bi-pencil"></i> Edit
        </a>
        <button class="ncv-btn ncv-btn-sm" style="background:#10b981;color:#fff;border:none;">
          <i class="bi bi-trophy-fill"></i> Mark Won
        </button>
        <button class="ncv-btn ncv-btn-sm" style="background:rgba(239,68,68,.3);color:#fca5a5;border:1px solid rgba(239,68,68,.3);">
          <i class="bi bi-x-circle"></i> Mark Lost
        </button>
        <form method="POST" action="{{ route('opportunities.convert-to-project', $opportunity->id ?? 1) }}" style="display:inline;">
          @csrf
          <button type="submit" class="ncv-btn ncv-btn-sm" style="background:rgba(16,185,129,.2);color:#6ee7b7;border:1px solid rgba(16,185,129,.3);"
            onclick="return confirm('Create a project from this opportunity?')">
            <i class="bi bi-kanban"></i> Convert to Project
          </button>
        </form>
      </div>
    </div>

    {{-- Stage Tracker --}}
    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.1);">
      <div style="display:flex;align-items:center;gap:0;overflow-x:auto;scrollbar-width:none;">
        @foreach(['Prospecting','Qualification','Proposal','Negotiation','Closed Won'] as $i => $stage)
        @php $active = $stage === 'Negotiation'; $done = in_array($stage, ['Prospecting','Qualification','Proposal']); @endphp
        <div style="flex:1;min-width:90px;text-align:center;position:relative;">
          @if($i > 0)
          <div style="position:absolute;left:0;top:50%;transform:translateY(-100%) translateY(-2px);width:50%;height:2px;background:{{ $done || $active ? 'rgba(255,255,255,.5)' : 'rgba(255,255,255,.15)' }};"></div>
          <div style="position:absolute;right:0;top:50%;transform:translateY(-100%) translateY(-2px);width:50%;height:2px;background:{{ $done ? 'rgba(255,255,255,.5)' : 'rgba(255,255,255,.15)' }};"></div>
          @else
          <div style="position:absolute;right:0;top:50%;transform:translateY(-100%) translateY(-2px);width:50%;height:2px;background:rgba(255,255,255,.5);"></div>
          @endif
          <div style="display:inline-flex;flex-direction:column;align-items:center;gap:.35rem;position:relative;z-index:1;">
            <div style="width:28px;height:28px;border-radius:50%;background:{{ $active ? '#fff' : ($done ? 'rgba(255,255,255,.3)' : 'rgba(255,255,255,.1)') }};color:{{ $active ? '#1d4ed8' : '#fff' }};display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;">
              @if($done) <i class="bi bi-check-lg"></i> @elseif($active) {{ $i+1 }} @else {{ $i+1 }} @endif
            </div>
            <span style="font-size:.65rem;font-weight:700;color:{{ $active ? '#fff' : 'rgba(255,255,255,.6)' }};white-space:nowrap;">{{ $stage }}</span>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  {{-- Left --}}
  <div class="col-12 col-lg-4">

    {{-- Deal Details --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-bar-chart me-2" style="color:var(--ncv-blue-500);"></i>Deal Details</h6>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['l'=>'Deal Value',    'v'=>'$42,000',      'bold'=>true],
          ['l'=>'Weighted',      'v'=>'$37,800 (90%)'],
          ['l'=>'Stage',         'v'=>'Negotiation'],
          ['l'=>'Close Date',    'v'=>'Feb 15, 2026'],
          ['l'=>'Lead Source',   'v'=>'Referral'],
          ['l'=>'Competitor',    'v'=>'Salesforce'],
          ['l'=>'Next Steps',    'v'=>'Legal review of MSA'],
          ['l'=>'Description',   'v'=>'50-seat expansion of existing SaaS license + API module add-on.'],
          ['l'=>'Created',       'v'=>'Jan 10, 2026'],
        ] as $row)
        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.55rem 0;border-bottom:1px solid var(--border-color);font-size:.85rem;">
          <span style="min-width:95px;font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;padding-top:2px;">{{ $row['l'] }}</span>
          <span style="color:{{ isset($row['bold']) ? 'var(--text-primary)' : 'var(--text-secondary)' }};font-weight:{{ isset($row['bold']) ? '800' : '400' }};font-size:{{ isset($row['bold']) ? '.95rem' : '.85rem' }};">{{ $row['v'] }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Linked Contact --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-person me-2" style="color:var(--ncv-blue-500);"></i>Primary Contact</h6>
      </div>
      <div class="ncv-card-body">
        <div style="display:flex;align-items:center;gap:.75rem;">
          <div style="width:40px;height:40px;border-radius:.625rem;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;">MC</div>
          <div>
            <a href="{{ route('contacts.show', 1) }}" style="font-weight:700;color:var(--text-primary);text-decoration:none;font-size:.9rem;">Michael Chen</a>
            <div style="font-size:.775rem;color:var(--text-muted);">CTO · TechStart Inc</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Sales Team --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-people me-2" style="color:var(--ncv-blue-500);"></i>Sales Team</h6>
        <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="openTeamModal()"><i class="bi bi-plus-lg"></i></button>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['name'=>'John Smith',    'initials'=>'JS','role'=>'Account Executive','credit'=>'60%','color'=>'#2563eb'],
          ['name'=>'Emma Williams', 'initials'=>'EW','role'=>'Sales Engineer',   'credit'=>'30%','color'=>'#10b981'],
          ['name'=>'You',           'initials'=>'ME','role'=>'Manager',          'credit'=>'10%','color'=>'#8b5cf6'],
        ] as $member)
        <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.625rem;">
          <div style="width:34px;height:34px;border-radius:.5rem;background:{{ $member['color'] }}18;color:{{ $member['color'] }};display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0;">{{ $member['initials'] }}</div>
          <div style="flex:1;">
            <div style="font-size:.83rem;font-weight:600;color:var(--text-primary);">{{ $member['name'] }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);">{{ $member['role'] }}</div>
          </div>
          <span class="ncv-badge ncv-badge-primary" style="font-size:.68rem;">{{ $member['credit'] }}</span>
          <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-x-lg" style="font-size:.7rem;"></i></button>
        </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- Right: Activity --}}
  <div class="col-12 col-lg-8">

    {{-- Quick Actions --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
        <div class="d-flex gap-2 flex-wrap">
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-telephone"></i> Log Call</button>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-envelope"></i> Send Email</button>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-file-earmark-text"></i> Create Quote</button>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-sticky"></i> Note</button>
          <select class="ncv-select ms-auto" style="width:160px;height:34px;font-size:.8rem;">
            <option>Change Stage…</option>
            @foreach(['Prospecting','Qualification','Proposal','Negotiation','Closed Won','Closed Lost'] as $s)
            <option>→ {{ $s }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    {{-- Activity --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">Deal Activity</h6>
        <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-plus-lg"></i> Log Activity</button>
      </div>
      <div class="ncv-card-body">
        <ul class="ncv-timeline">
          @foreach([
            ['icon'=>'bi-telephone-fill','bg'=>'#dbeafe','color'=>'#2563eb','title'=>'Call with Michael Chen — 35 min','time'=>'Today, 10:00 AM','desc'=>'Pricing finalized. Legal team reviewing MSA. Expecting sign-off by Feb 12.'],
            ['icon'=>'bi-file-earmark-text','bg'=>'#ede9fe','color'=>'#7c3aed','title'=>'Proposal sent — $42,000','time'=>'Feb 10, 2:00 PM','desc'=>'Sent formal proposal with 50-seat license + API module. 10% discount applied.'],
            ['icon'=>'bi-calendar-check','bg'=>'#fef3c7','color'=>'#d97706','title'=>'Product demo — 60 min','time'=>'Feb 5, 3:00 PM','desc'=>'Demonstrated new reporting module and API capabilities. Very positive feedback.'],
            ['icon'=>'bi-lightning-charge','bg'=>'#d1fae5','color'=>'#059669','title'=>'Deal created from Lead conversion','time'=>'Jan 10, 9:30 AM','desc'=>'Converted from lead "Alex Turner" via one-click conversion.'],
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

@endsection
