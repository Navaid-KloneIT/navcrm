@extends('layouts.app')

@section('title', $account->name ?? 'Account Profile')
@section('page-title', $account->name ?? 'Account Profile')

@section('breadcrumb-items')
  <a href="{{ route('accounts.index') }}" style="color:inherit;text-decoration:none;">Accounts</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .account-hero {
    background: linear-gradient(135deg, #0d1f4e 0%, #1e3a8f 50%, #1d4ed8 100%);
    border-radius: var(--card-radius);
    padding: 2rem 1.75rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  .account-hero::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    top: -80px; right: -60px;
  }
  .acc-icon-lg {
    width: 72px; height: 72px;
    border-radius: 1.125rem;
    background: rgba(255,255,255,.15);
    border: 2px solid rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 800; color: #fff;
    flex-shrink: 0;
  }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="account-hero">
  <div class="d-flex align-items-start gap-3 position-relative" style="z-index:1;">
    <div class="acc-icon-lg">AC</div>
    <div class="flex-1">
      <h1 style="font-size:1.5rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .25rem;">
        {{ $account->name ?? 'Acme Corporation' }}
      </h1>
      <div style="font-size:.9rem;color:rgba(255,255,255,.8);display:flex;gap:1.25rem;flex-wrap:wrap;margin-top:.375rem;">
        <span><i class="bi bi-briefcase"></i> {{ $account->industry ?? 'Technology' }}</span>
        <span><i class="bi bi-people"></i> {{ $account->employees ?? '500–1,000' }} employees</span>
        <span><i class="bi bi-globe2"></i> <a href="#" style="color:rgba(255,255,255,.85);text-decoration:none;">{{ $account->website ?? 'acme.com' }}</a></span>
        <span><i class="bi bi-currency-dollar"></i> {{ $account->annual_revenue ?? '$45M' }} ARR</span>
      </div>
    </div>
    <div class="d-flex gap-2 ms-auto" style="position:relative;z-index:1;">
      <a href="{{ route('opportunities.create') }}"
         class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
        <i class="bi bi-plus-lg"></i> New Deal
      </a>
      <a href="{{ route('accounts.edit', $account->id ?? 1) }}"
         class="ncv-btn ncv-btn-sm" style="background:#fff;color:#1d4ed8;border:none;font-weight:700;">
        <i class="bi bi-pencil"></i> Edit
      </a>
    </div>
  </div>

  {{-- KPIs --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:.875rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.1);position:relative;z-index:1;">
    @php
      $hs = $account->latestHealthScore;
      $hsValue = $hs ? $hs->overall_score : '—';
      $hsColor = $hs ? ($hs->overall_score >= 70 ? '#86efac' : ($hs->overall_score >= 40 ? '#fde68a' : '#fca5a5')) : 'rgba(255,255,255,.6)';
    @endphp
    @foreach([
      ['label'=>'Contacts',      'value'=>'12'],
      ['label'=>'Open Deals',    'value'=>'3'],
      ['label'=>'Open Quotes',   'value'=>'2'],
      ['label'=>'Activities',    'value'=>'47'],
      ['label'=>'Won Revenue',   'value'=>'$128k'],
      ['label'=>'Health Score',  'value'=>$hsValue, 'color'=>$hsColor, 'link'=>route('success.health-scores.show', $account)],
    ] as $kpi)
    <div style="text-align:center;">
      @if(isset($kpi['link']))
        <a href="{{ $kpi['link'] }}" style="text-decoration:none;">
          <div style="font-size:1.5rem;font-weight:800;color:{{ $kpi['color'] ?? '#fff' }};letter-spacing:-.02em;">{{ $kpi['value'] }}</div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.6);font-weight:600;text-transform:uppercase;letter-spacing:.06em;">{{ $kpi['label'] }}</div>
        </a>
      @else
        <div style="font-size:1.5rem;font-weight:800;color:#fff;letter-spacing:-.02em;">{{ $kpi['value'] }}</div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.6);font-weight:600;text-transform:uppercase;letter-spacing:.06em;">{{ $kpi['label'] }}</div>
      @endif
    </div>
    @endforeach
  </div>
</div>

<div class="row g-3">

  {{-- LEFT: Details --}}
  <div class="col-12 col-lg-4">

    {{-- Company Details --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-building me-2" style="color:var(--ncv-blue-500);"></i>Company Details</h6>
        <a href="{{ route('accounts.edit', $account->id ?? 1) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil"></i></a>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['label'=>'Legal Name',   'value'=>'Acme Corp LLC'],
          ['label'=>'Tax ID',       'value'=>'12-3456789'],
          ['label'=>'Industry',     'value'=>'Technology / SaaS'],
          ['label'=>'Employees',    'value'=>'500 – 1,000'],
          ['label'=>'Revenue',      'value'=>'$45M annually'],
          ['label'=>'Founded',      'value'=>'2008'],
          ['label'=>'Website',      'value'=>'acme.com', 'type'=>'link'],
          ['label'=>'Parent Co.',   'value'=>'Acme Holdings Ltd', 'type'=>'link'],
          ['label'=>'Type',         'value'=>'Enterprise Client'],
          ['label'=>'Rating',       'value'=>'Hot'],
        ] as $row)
        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.55rem 0;border-bottom:1px solid var(--border-color);font-size:.85rem;">
          <span style="min-width:105px;font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;padding-top:2px;">{{ $row['label'] }}</span>
          <span style="color:var(--text-secondary);">
            @if(isset($row['type']) && $row['type'] === 'link')
              <a href="#" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $row['value'] }} <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i></a>
            @else
              {{ $row['value'] }}
            @endif
          </span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Addresses --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-geo-alt me-2" style="color:var(--ncv-blue-500);"></i>Addresses</h6>
        <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Add address"><i class="bi bi-plus-lg"></i></button>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['type'=>'Billing',  'address'=>'1600 Amphitheatre Pkwy', 'city'=>'Mountain View, CA 94043', 'country'=>'United States'],
          ['type'=>'Shipping', 'address'=>'1 Infinite Loop',        'city'=>'Cupertino, CA 95014',     'country'=>'United States'],
        ] as $addr)
        <div style="margin-bottom:.875rem;padding-bottom:.875rem;border-bottom:1px solid var(--border-color);">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.375rem;">
            <span class="ncv-badge ncv-badge-primary" style="font-size:.68rem;">{{ $addr['type'] }}</span>
            <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="font-size:.72rem;padding:.2rem .5rem;">Edit</button>
          </div>
          <p style="font-size:.855rem;color:var(--text-secondary);margin:0;line-height:1.65;">
            {{ $addr['address'] }}<br/>{{ $addr['city'] }}<br/>{{ $addr['country'] }}
          </p>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Account Hierarchy --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-diagram-3 me-2" style="color:var(--ncv-blue-500);"></i>Hierarchy</h6>
      </div>
      <div class="ncv-card-body">
        {{-- Parent --}}
        <div style="text-align:center;margin-bottom:.5rem;">
          <div style="display:inline-flex;align-items:center;gap:.5rem;background:var(--ncv-blue-50);border:1.5px solid var(--ncv-blue-200);border-radius:.625rem;padding:.5rem .875rem;font-size:.8rem;font-weight:600;color:var(--ncv-blue-700);">
            <i class="bi bi-building-up"></i> Acme Holdings Ltd (Parent)
          </div>
        </div>
        <div style="text-align:center;color:var(--text-muted);font-size:1.25rem;margin:.25rem 0;"><i class="bi bi-arrow-down"></i></div>
        {{-- Current --}}
        <div style="text-align:center;margin-bottom:.5rem;">
          <div style="display:inline-flex;align-items:center;gap:.5rem;background:#dbeafe;border:2px solid #3b82f6;border-radius:.625rem;padding:.5rem .875rem;font-size:.85rem;font-weight:700;color:#1d4ed8;">
            <i class="bi bi-building"></i> Acme Corporation (Current)
          </div>
        </div>
        <div style="text-align:center;color:var(--text-muted);font-size:1.25rem;margin:.25rem 0;"><i class="bi bi-arrow-down"></i></div>
        {{-- Children --}}
        @foreach(['Acme Labs (R&D)', 'Acme Cloud (SaaS)', 'Acme Finance'] as $child)
        <div style="display:flex;align-items:center;gap:.5rem;background:#f8faff;border:1px solid var(--border-color);border-radius:.5rem;padding:.4rem .75rem;font-size:.78rem;color:var(--text-secondary);margin-bottom:.35rem;">
          <i class="bi bi-building-down" style="color:var(--text-muted);"></i>
          <a href="#" style="color:var(--ncv-blue-600);text-decoration:none;font-weight:600;">{{ $child }}</a>
          <span style="margin-left:auto;color:var(--text-muted);font-size:.7rem;">Subsidiary</span>
        </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- RIGHT: Tabs --}}
  <div class="col-12 col-lg-8">

    <div class="ncv-tabs mb-3" id="accTabs">
      <a class="ncv-tab active" onclick="accTab('stakeholders')" href="#">Stakeholders</a>
      <a class="ncv-tab" onclick="accTab('opportunities')" href="#">Deals</a>
      <a class="ncv-tab" onclick="accTab('activity')" href="#">Activity</a>
      <a class="ncv-tab" onclick="accTab('quotes')" href="#">Quotes</a>
    </div>

    {{-- STAKEHOLDERS --}}
    <div id="acc-stakeholders">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Stakeholders ({{ 12 }})</h6>
          <a href="{{ route('contacts.create') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> Add Contact
          </a>
        </div>
        <div class="ncv-card-body p-0">
          <table class="ncv-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Title</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Last Contact</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach([
                ['name'=>'Sarah Johnson',  'initials'=>'SJ','title'=>'VP of Sales',    'email'=>'sarah@acme.com',   'phone'=>'+1 555-0101','last'=>'Today',      'color'=>'#2563eb'],
                ['name'=>'Robert Acme',    'initials'=>'RA','title'=>'CEO',             'email'=>'robert@acme.com',  'phone'=>'+1 555-0100','last'=>'2 days ago', 'color'=>'#10b981'],
                ['name'=>'Linda Chen',     'initials'=>'LC','title'=>'CFO',             'email'=>'linda@acme.com',   'phone'=>'+1 555-0102','last'=>'1 week ago', 'color'=>'#f59e0b'],
                ['name'=>'Marcus Webb',    'initials'=>'MW','title'=>'CTO',             'email'=>'marcus@acme.com',  'phone'=>'+1 555-0103','last'=>'2 weeks ago','color'=>'#8b5cf6'],
                ['name'=>'Jessica Park',   'initials'=>'JP','title'=>'Procurement Mgr','email'=>'jessica@acme.com', 'phone'=>'+1 555-0104','last'=>'3 weeks ago','color'=>'#06b6d4'],
              ] as $contact)
              <tr>
                <td>
                  <div class="ncv-table-name">
                    <div class="ncv-table-avatar" style="background:{{ $contact['color'] }}18;color:{{ $contact['color'] }};">{{ $contact['initials'] }}</div>
                    <a href="{{ route('contacts.show', 1) }}" class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;">{{ $contact['name'] }}</a>
                  </div>
                </td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $contact['title'] }}</td>
                <td><a href="mailto:{{ $contact['email'] }}" style="font-size:.82rem;color:var(--ncv-blue-600);text-decoration:none;">{{ $contact['email'] }}</a></td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $contact['phone'] }}</td>
                <td style="font-size:.775rem;color:var(--text-muted);">{{ $contact['last'] }}</td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('contacts.show', 1) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
                    <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Remove"><i class="bi bi-x-lg" style="font-size:.75rem;color:#ef4444;"></i></button>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- DEALS --}}
    <div id="acc-opportunities" style="display:none;">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Open Deals</h6>
          <a href="{{ route('opportunities.create') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-plus-lg"></i> New Deal</a>
        </div>
        <div class="ncv-card-body p-0">
          <table class="ncv-table">
            <thead>
              <tr><th>Opportunity</th><th>Stage</th><th>Value</th><th>Close Date</th><th>Probability</th></tr>
            </thead>
            <tbody>
              @foreach([
                ['name'=>'Enterprise Renewal 2026','stage'=>'Negotiation','stage_c'=>'success','value'=>'$85,000','date'=>'Jan 31','prob'=>90],
                ['name'=>'Add-On Seats (50)',      'stage'=>'Prospecting','stage_c'=>'muted',  'value'=>'$12,500','date'=>'Mar 15','prob'=>30],
                ['name'=>'API Integration Module', 'stage'=>'Proposal',   'stage_c'=>'warning','value'=>'$22,000','date'=>'Feb 28','prob'=>60],
              ] as $opp)
              <tr>
                <td><a href="{{ route('opportunities.show', 1) }}" style="font-weight:600;color:var(--text-primary);text-decoration:none;font-size:.875rem;">{{ $opp['name'] }}</a></td>
                <td><span class="ncv-badge ncv-badge-{{ $opp['stage_c'] }}"><span class="dot"></span>{{ $opp['stage'] }}</span></td>
                <td style="font-weight:700;">{{ $opp['value'] }}</td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $opp['date'] }}</td>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem;">
                    <div class="ncv-progress-bar" style="width:70px;"><div class="ncv-progress-fill" style="width:{{ $opp['prob'] }}%;"></div></div>
                    <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">{{ $opp['prob'] }}%</span>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ACTIVITY --}}
    <div id="acc-activity" style="display:none;">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Account Activity</h6>
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-plus-lg"></i> Log Activity</button>
        </div>
        <div class="ncv-card-body">
          <ul class="ncv-timeline">
            @foreach([
              ['icon'=>'bi-telephone-fill','bg'=>'#dbeafe','color'=>'#2563eb','title'=>'Call with Sarah Johnson (VP Sales)','time'=>'Today, 2:30 PM','desc'=>'Renewal discussion. Positive outcome.'],
              ['icon'=>'bi-envelope-fill', 'bg'=>'#d1fae5','color'=>'#059669','title'=>'Proposal sent to Robert Acme (CEO)','time'=>'Yesterday, 10:00 AM','desc'=>'Sent enterprise proposal Q1 2026.'],
              ['icon'=>'bi-calendar-check','bg'=>'#fef3c7','color'=>'#d97706','title'=>'On-site meeting at Acme HQ','time'=>'Feb 15, 2026','desc'=>'Full team demo. 3 stakeholders attended.'],
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

    {{-- QUOTES --}}
    <div id="acc-quotes" style="display:none;">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title">Quotes</h6>
          <a href="{{ route('quotes.create') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-plus-lg"></i> New Quote</a>
        </div>
        <div class="ncv-card-body p-0">
          <table class="ncv-table">
            <thead>
              <tr><th>Quote #</th><th>Title</th><th>Status</th><th>Total</th><th>Expires</th><th></th></tr>
            </thead>
            <tbody>
              @foreach([
                ['num'=>'QT-0089','title'=>'Enterprise License 2026','status'=>'Sent',    'status_c'=>'primary','total'=>'$85,000','exp'=>'Feb 28'],
                ['num'=>'QT-0072','title'=>'Add-On Seats Proposal',  'status'=>'Draft',   'status_c'=>'muted',  'total'=>'$12,500','exp'=>'Mar 15'],
                ['num'=>'QT-0055','title'=>'2025 Renewal (Accepted)','status'=>'Accepted','status_c'=>'success','total'=>'$72,000','exp'=>'Expired'],
              ] as $q)
              <tr>
                <td style="font-weight:700;font-size:.82rem;color:var(--ncv-blue-600);">{{ $q['num'] }}</td>
                <td style="font-size:.875rem;">{{ $q['title'] }}</td>
                <td><span class="ncv-badge ncv-badge-{{ $q['status_c'] }}"><span class="dot"></span>{{ $q['status'] }}</span></td>
                <td style="font-weight:700;">{{ $q['total'] }}</td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $q['exp'] }}</td>
                <td><a href="{{ route('quotes.show', 1) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-eye" style="font-size:.8rem;"></i></a></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
  function accTab(tabName) {
    ['stakeholders','opportunities','activity','quotes'].forEach(t => {
      document.getElementById('acc-' + t).style.display = t === tabName ? 'block' : 'none';
    });
    document.querySelectorAll('#accTabs .ncv-tab').forEach(function(tab, i) {
      const tabs = ['stakeholders','opportunities','activity','quotes'];
      tab.classList.toggle('active', tabs[i] === tabName);
    });
    return false;
  }
</script>
@endpush
