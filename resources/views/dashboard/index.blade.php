@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
  /* Chart placeholder */
  .ncv-chart-placeholder {
    width: 100%;
    height: 220px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 6px;
    padding: .5rem 0;
  }
  .ncv-chart-bar {
    flex: 1;
    background: linear-gradient(0deg, #2563eb 0%, #60a5fa 100%);
    border-radius: 4px 4px 0 0;
    opacity: .85;
    transition: opacity .2s;
    position: relative;
  }
  .ncv-chart-bar:hover { opacity: 1; }
  .ncv-chart-bar.muted {
    background: linear-gradient(0deg, #cbd5e1 0%, #e2e8f0 100%);
  }
  .ncv-chart-label {
    display: flex;
    justify-content: space-between;
    gap: 6px;
    padding-top: .5rem;
    border-top: 1px solid var(--border-color);
  }
  .ncv-chart-label span {
    flex: 1;
    text-align: center;
    font-size: .65rem;
    color: var(--text-muted);
    font-weight: 600;
  }

  /* Donut chart (CSS) */
  .ncv-donut {
    width: 120px; height: 120px;
    border-radius: 50%;
    background: conic-gradient(
      #2563eb 0% 45%,
      #10b981 45% 72%,
      #f59e0b 72% 88%,
      #e2e8f0 88% 100%
    );
    position: relative;
    display: flex; align-items: center; justify-content: center;
  }
  .ncv-donut::after {
    content: '';
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #fff;
    position: absolute;
  }
  .ncv-donut-center {
    position: absolute;
    z-index: 1;
    text-align: center;
  }
  .ncv-donut-value {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
  }
  .ncv-donut-label {
    font-size: .6rem;
    color: var(--text-muted);
    font-weight: 600;
  }

  /* Quick action cards */
  .ncv-quick-action {
    background: var(--card-bg);
    border: 1.5px dashed var(--card-border);
    border-radius: var(--card-radius);
    padding: 1.25rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    display: block;
  }
  .ncv-quick-action:hover {
    border-color: var(--ncv-blue-400);
    background: var(--ncv-blue-50);
    transform: translateY(-2px);
  }
  .ncv-quick-action-icon {
    width: 46px; height: 46px;
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto .75rem;
    font-size: 1.2rem;
  }
  .ncv-quick-action-label {
    font-size: .8rem;
    font-weight: 700;
    color: var(--text-primary);
  }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="ncv-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">
      Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
      {{ explode(' ', auth()->user()?->name ?? 'there')[0] }} 👋
    </h1>
    <p class="ncv-page-subtitle">Here's what's happening with your sales today.</p>
  </div>
  <div class="d-flex gap-2 align-items-center flex-wrap">
    <select class="ncv-select" style="width:140px;height:38px;font-size:.8rem;">
      <option>This Month</option>
      <option>Last Month</option>
      <option>This Quarter</option>
      <option>This Year</option>
    </select>
    <a href="{{ route('leads.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> Add Lead
    </a>
  </div>
</div>

{{-- ─── KPI Stats Row ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

  <div class="col-6 col-lg-3">
    <div class="ncv-stat-card primary">
      <div class="ncv-stat-label">Total Revenue</div>
      <div class="ncv-stat-value">$248k</div>
      <span class="ncv-stat-change up">
        <i class="bi bi-arrow-up-short"></i> 12.5%
      </span>
      <div class="ncv-stat-icon-wrap">
        <i class="bi bi-currency-dollar" style="font-size:1.4rem;"></i>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="ncv-stat-card success">
      <div class="ncv-stat-label">Deals Won</div>
      <div class="ncv-stat-value">34</div>
      <span class="ncv-stat-change up">
        <i class="bi bi-arrow-up-short"></i> 8.2%
      </span>
      <div class="ncv-stat-icon-wrap">
        <i class="bi bi-trophy" style="font-size:1.3rem;"></i>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="ncv-stat-card warning">
      <div class="ncv-stat-label">Open Leads</div>
      <div class="ncv-stat-value">127</div>
      <span class="ncv-stat-change down">
        <i class="bi bi-arrow-down-short"></i> 3.1%
      </span>
      <div class="ncv-stat-icon-wrap">
        <i class="bi bi-lightning" style="font-size:1.3rem;"></i>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="ncv-stat-card purple">
      <div class="ncv-stat-label">Pipeline Value</div>
      <div class="ncv-stat-value">$1.2M</div>
      <span class="ncv-stat-change up">
        <i class="bi bi-arrow-up-short"></i> 21.3%
      </span>
      <div class="ncv-stat-icon-wrap">
        <i class="bi bi-bar-chart" style="font-size:1.3rem;"></i>
      </div>
    </div>
  </div>

</div>

{{-- ─── Charts Row ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

  {{-- Revenue Chart --}}
  <div class="col-12 col-lg-8">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">
          <i class="bi bi-graph-up me-2" style="color:var(--ncv-blue-500);"></i>
          Monthly Revenue
        </h6>
        <div class="d-flex gap-2">
          <span class="ncv-badge ncv-badge-primary"><span class="dot"></span>Revenue</span>
          <span class="ncv-badge ncv-badge-muted"><span class="dot"></span>Target</span>
        </div>
      </div>
      <div class="ncv-card-body">
        {{-- Simple CSS bar chart --}}
        <div class="ncv-chart-placeholder">
          <div class="ncv-chart-bar muted" style="height:45%;" title="Jan: $32k"></div>
          <div class="ncv-chart-bar muted" style="height:60%;" title="Feb: $41k"></div>
          <div class="ncv-chart-bar muted" style="height:50%;" title="Mar: $38k"></div>
          <div class="ncv-chart-bar muted" style="height:75%;" title="Apr: $52k"></div>
          <div class="ncv-chart-bar muted" style="height:65%;" title="May: $48k"></div>
          <div class="ncv-chart-bar muted" style="height:80%;" title="Jun: $56k"></div>
          <div class="ncv-chart-bar muted" style="height:55%;" title="Jul: $42k"></div>
          <div class="ncv-chart-bar muted" style="height:90%;" title="Aug: $63k"></div>
          <div class="ncv-chart-bar muted" style="height:70%;" title="Sep: $51k"></div>
          <div class="ncv-chart-bar muted" style="height:85%;" title="Oct: $61k"></div>
          <div class="ncv-chart-bar" style="height:95%;" title="Nov: $68k"></div>
          <div class="ncv-chart-bar" style="height:100%;" title="Dec: $72k (current)"></div>
        </div>
        <div class="ncv-chart-label">
          <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span>
          <span>May</span><span>Jun</span><span>Jul</span><span>Aug</span>
          <span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Pipeline Breakdown --}}
  <div class="col-12 col-lg-4">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">
          <i class="bi bi-pie-chart me-2" style="color:var(--ncv-blue-500);"></i>
          Pipeline Stages
        </h6>
      </div>
      <div class="ncv-card-body d-flex flex-column align-items-center justify-content-between" style="gap:1.25rem;">
        <div style="position:relative;display:inline-flex;align-items:center;justify-content:center;">
          <div class="ncv-donut"></div>
          <div class="ncv-donut-center">
            <div class="ncv-donut-value">72</div>
            <div class="ncv-donut-label">Deals</div>
          </div>
        </div>
        <div class="w-100">
          @foreach([
            ['label' => 'Prospecting',    'pct' => 45, 'color' => '#2563eb', 'count' => 32],
            ['label' => 'Qualified',       'pct' => 27, 'color' => '#10b981', 'count' => 20],
            ['label' => 'Proposal Sent',  'pct' => 16, 'color' => '#f59e0b', 'count' => 12],
            ['label' => 'Closed',         'pct' => 12, 'color' => '#e2e8f0', 'count' =>  8],
          ] as $stage)
          <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.625rem;">
            <span style="width:10px;height:10px;border-radius:50%;background:{{ $stage['color'] }};flex-shrink:0;"></span>
            <span style="flex:1;font-size:.8rem;color:var(--text-secondary);">{{ $stage['label'] }}</span>
            <span style="font-size:.8rem;font-weight:700;color:var(--text-primary);">{{ $stage['count'] }}</span>
            <span style="font-size:.75rem;color:var(--text-muted);">{{ $stage['pct'] }}%</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ─── Recent Activity + Top Deals ───────────────────────────────────────── --}}
<div class="row g-3 mb-4">

  {{-- Recent Activity --}}
  <div class="col-12 col-lg-5">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">
          <i class="bi bi-activity me-2" style="color:var(--ncv-blue-500);"></i>
          Recent Activity
        </h6>
        <a href="{{ route('activities.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
          View all
        </a>
      </div>
      <div class="ncv-card-body" style="padding:.75rem 1.25rem;">
        <ul class="ncv-timeline">
          @foreach([
            ['icon'=>'bi-telephone-fill', 'bg'=>'#dbeafe', 'color'=>'#2563eb',
             'title'=>'Call with Acme Corp', 'time'=>'2h ago',
             'desc'=>'Discussed Q1 renewal terms with John.'],
            ['icon'=>'bi-envelope-fill',   'bg'=>'#d1fae5', 'color'=>'#10b981',
             'title'=>'Email to TechStart', 'time'=>'4h ago',
             'desc'=>'Sent proposal for 50-seat license.'],
            ['icon'=>'bi-calendar-check',  'bg'=>'#fef3c7', 'color'=>'#d97706',
             'title'=>'Meeting scheduled', 'time'=>'Yesterday',
             'desc'=>'Demo with Globex Inc. on Thursday.'],
            ['icon'=>'bi-trophy-fill',     'bg'=>'#ede9fe', 'color'=>'#7c3aed',
             'title'=>'Deal won — $28k',   'time'=>'Yesterday',
             'desc'=>'Opportunity #0045 closed successfully.'],
            ['icon'=>'bi-file-text-fill',  'bg'=>'#cffafe', 'color'=>'#0891b2',
             'title'=>'Quote sent',         'time'=>'2 days ago',
             'desc'=>'Quote #QT-0089 delivered to Initech.'],
          ] as $item)
          <li class="ncv-timeline-item">
            <div class="ncv-timeline-icon" style="background:{{ $item['bg'] }};color:{{ $item['color'] }};">
              <i class="bi {{ $item['icon'] }}" style="font-size:.8rem;"></i>
            </div>
            <div class="ncv-timeline-body">
              <div class="ncv-timeline-title">{{ $item['title'] }}</div>
              <div class="ncv-timeline-time">{{ $item['time'] }}</div>
              <div class="ncv-timeline-desc">{{ $item['desc'] }}</div>
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  {{-- Top Opportunities --}}
  <div class="col-12 col-lg-7">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">
          <i class="bi bi-stars me-2" style="color:var(--ncv-blue-500);"></i>
          Top Opportunities
        </h6>
        <a href="{{ route('opportunities.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
          View pipeline
        </a>
      </div>
      <div class="ncv-card-body p-0">
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Opportunity</th>
              <th>Stage</th>
              <th>Value</th>
              <th>Close Date</th>
              <th>Prob.</th>
            </tr>
          </thead>
          <tbody>
            @foreach([
              ['name'=>'Acme Corp — Enterprise', 'account'=>'Acme Corp',     'stage'=>'Proposal', 'stage_color'=>'warning', 'value'=>'$85,000', 'date'=>'Jan 31', 'prob'=>75],
              ['name'=>'TechStart Expansion',    'account'=>'TechStart Inc',  'stage'=>'Negotiation','stage_color'=>'success','value'=>'$42,000','date'=>'Feb 15','prob'=>90],
              ['name'=>'Globex SaaS License',    'account'=>'Globex Inc',     'stage'=>'Qualified',  'stage_color'=>'primary','value'=>'$31,500','date'=>'Mar 08','prob'=>50],
              ['name'=>'Initech Platform',       'account'=>'Initech LLC',    'stage'=>'Prospecting','stage_color'=>'muted',  'value'=>'$22,000','date'=>'Mar 20','prob'=>30],
              ['name'=>'Umbrella Corp BI',       'account'=>'Umbrella Corp',  'stage'=>'Proposal',   'stage_color'=>'warning','value'=>'$18,750','date'=>'Feb 28','prob'=>65],
            ] as $opp)
            <tr>
              <td>
                <div class="ncv-table-name">
                  <div class="ncv-table-avatar">
                    {{ strtoupper(substr($opp['account'], 0, 2)) }}
                  </div>
                  <div>
                    <div class="ncv-table-cell-primary" style="font-size:.82rem;">{{ $opp['name'] }}</div>
                    <div class="ncv-table-cell-sub">{{ $opp['account'] }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="ncv-badge ncv-badge-{{ $opp['stage_color'] }}">
                  <span class="dot"></span>{{ $opp['stage'] }}
                </span>
              </td>
              <td style="font-weight:700;color:var(--text-primary);font-size:.85rem;">{{ $opp['value'] }}</td>
              <td style="font-size:.82rem;color:var(--text-muted);">{{ $opp['date'] }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <div class="ncv-progress-bar" style="width:60px;">
                    <div class="ncv-progress-fill" style="width:{{ $opp['prob'] }}%;background:{{ $opp['prob'] >= 80 ? '#10b981' : ($opp['prob'] >= 50 ? '#2563eb' : '#f59e0b') }};"></div>
                  </div>
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

</div>

{{-- ─── Quick Actions + Recent Contacts ───────────────────────────────────── --}}
<div class="row g-3">

  {{-- Quick Actions --}}
  <div class="col-12 col-lg-4">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">Quick Actions</h6>
      </div>
      <div class="ncv-card-body">
        <div class="row g-2">
          @foreach([
            ['label'=>'New Contact',      'icon'=>'bi-person-plus-fill', 'bg'=>'#dbeafe', 'color'=>'#2563eb', 'route'=>'contacts.create'],
            ['label'=>'New Lead',         'icon'=>'bi-lightning-fill',   'bg'=>'#fef3c7', 'color'=>'#d97706', 'route'=>'leads.create'],
            ['label'=>'New Opportunity',  'icon'=>'bi-bar-chart-fill',   'bg'=>'#d1fae5', 'color'=>'#059669', 'route'=>'opportunities.create'],
            ['label'=>'New Quote',        'icon'=>'bi-file-earmark-text-fill','bg'=>'#ede9fe','color'=>'#7c3aed','route'=>'quotes.create'],
          ] as $action)
          <div class="col-6">
            <a href="{{ route($action['route']) }}" class="ncv-quick-action">
              <div class="ncv-quick-action-icon" style="background:{{ $action['bg'] }};color:{{ $action['color'] }};">
                <i class="bi {{ $action['icon'] }}"></i>
              </div>
              <div class="ncv-quick-action-label">{{ $action['label'] }}</div>
            </a>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Contacts --}}
  <div class="col-12 col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title">
          <i class="bi bi-people me-2" style="color:var(--ncv-blue-500);"></i>
          Recent Contacts
        </h6>
        <a href="{{ route('contacts.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
          View all
        </a>
      </div>
      <div class="ncv-card-body p-0">
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Company</th>
              <th>Phone</th>
              <th>Added</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach([
              ['name'=>'Sarah Johnson',  'initials'=>'SJ', 'company'=>'Acme Corp',    'phone'=>'+1 555-0101', 'added'=>'Today',      'color'=>'#2563eb'],
              ['name'=>'Michael Chen',   'initials'=>'MC', 'company'=>'TechStart',    'phone'=>'+1 555-0182', 'added'=>'Today',      'color'=>'#10b981'],
              ['name'=>'Emma Williams',  'initials'=>'EW', 'company'=>'Globex Inc',   'phone'=>'+1 555-0234', 'added'=>'Yesterday',  'color'=>'#f59e0b'],
              ['name'=>'James Rodriguez','initials'=>'JR', 'company'=>'Initech LLC',  'phone'=>'+1 555-0317', 'added'=>'2 days ago', 'color'=>'#8b5cf6'],
              ['name'=>'Olivia Brown',   'initials'=>'OB', 'company'=>'Umbrella Corp','phone'=>'+1 555-0459', 'added'=>'3 days ago', 'color'=>'#06b6d4'],
            ] as $contact)
            <tr>
              <td>
                <div class="ncv-table-name">
                  <div class="ncv-table-avatar" style="background:{{ $contact['color'] }}20;color:{{ $contact['color'] }};">
                    {{ $contact['initials'] }}
                  </div>
                  <div class="ncv-table-cell-primary">{{ $contact['name'] }}</div>
                </div>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted);">{{ $contact['company'] }}</td>
              <td style="font-size:.82rem;color:var(--text-muted);">{{ $contact['phone'] }}</td>
              <td style="font-size:.75rem;color:var(--text-muted);">{{ $contact['added'] }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="#" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                    <i class="bi bi-eye" style="font-size:.8rem;"></i>
                  </a>
                  <a href="#" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Email">
                    <i class="bi bi-envelope" style="font-size:.8rem;"></i>
                  </a>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@endsection
