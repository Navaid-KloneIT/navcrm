@extends('layouts.app')

@section('title', 'Analytics Dashboard')
@section('page-title', 'Analytics')

@section('breadcrumb-items')
  <span style="color:inherit;">Analytics</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .analytics-grid { display: flex; flex-wrap: wrap; gap: 1rem; }

  /* KPI cards — 5 per row on large screens */
  .widget-kpi   { flex: 1 1 calc(20% - 1rem); min-width: 160px; }

  /* Charts — 2 per row */
  .widget-chart { flex: 1 1 calc(50% - 1rem); min-width: 280px; }

  /* Small charts — 3 per row */
  .widget-chart-sm { flex: 1 1 calc(33.333% - 1rem); min-width: 220px; }

  /* Tables — 2 per row */
  .widget-table { flex: 1 1 calc(50% - 1rem); min-width: 300px; }

  /* Drag state */
  .widget-drag-handle { cursor: grab; color: var(--text-muted); padding: 0 .2rem; line-height:1; }
  .widget-drag-handle:active { cursor: grabbing; }
  .sortable-ghost     { opacity: .35; background: #eef3fb !important; border-style: dashed !important; }

  /* Hidden widget dims */
  .widget-is-hidden { opacity: .4; }

  /* Configure panel chip */
  .ncv-chip {
    display: inline-block; padding: .25rem .65rem;
    background: var(--bg-secondary); border: 1px solid var(--border-color);
    border-radius: 999px; font-size: .75rem; font-weight: 500;
    color: var(--text-secondary); cursor: pointer; transition: all .15s;
  }
  .ncv-chip.active {
    background: #2563eb; border-color: #2563eb; color: #fff;
  }
  .ncv-chip:hover { border-color: #2563eb; color: #2563eb; }
  .ncv-chip.active:hover { background: #1d4ed8; }

  /* KPI number */
  .kpi-value { font-size: 1.5rem; font-weight: 900; color: #0d1f4e; line-height: 1.1; }
  .kpi-label { font-size: .72rem; color: var(--text-muted); margin-top: .2rem; }
  .kpi-sub   { font-size: .75rem; color: var(--text-secondary); margin-top: .4rem; }
  .kpi-icon  { width: 2.2rem; height: 2.2rem; border-radius: .5rem; display: flex; align-items: center; justify-content: center; }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Analytics Dashboard</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">Drag widgets to rearrange. Use Configure to show or hide panels.</p>
  </div>
  <button class="ncv-btn ncv-btn-outline ncv-btn-sm" id="btnWidgetSettings" style="white-space:nowrap;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:.3rem;">
      <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>
    Configure Widgets
  </button>
</div>

{{-- Configure panel --}}
<div id="widgetSettingsPanel" class="ncv-card mb-3" style="display:none;">
  <div class="ncv-card-body" style="padding:.875rem 1rem;">
    <p class="mb-2" style="font-size:.78rem; font-weight:600; color:var(--text-secondary);">TOGGLE VISIBILITY</p>
    <div class="d-flex flex-wrap gap-2">
      @foreach($widgets->sortBy('position') as $type => $widget)
        <button class="ncv-chip {{ $widget->is_visible ? 'active' : '' }}"
                data-type="{{ $type }}"
                onclick="handleToggleWidget('{{ $type }}', this)">
          {{ \App\Models\DashboardWidget::widgetLabel($type) }}
        </button>
      @endforeach
    </div>
  </div>
</div>

{{-- Sortable widget grid --}}
<div class="analytics-grid" id="widgetGrid">

  @foreach($widgets->sortBy('position') as $type => $widget)

  {{-- ── KPI: Total Revenue ───────────────────────────────────────────── --}}
  @if($type === \App\Models\DashboardWidget::KPI_REVENUE)
  <div class="ncv-card widget-kpi {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-body" style="padding:.875rem;">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="kpi-icon" style="background:#eff6ff;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <span class="widget-drag-handle"><i class="bi bi-grip-vertical"></i></span>
      </div>
      <div class="kpi-value">${{ number_format(($widgetData['kpi']['total_revenue'] ?? 0) / 1000, 1) }}k</div>
      <div class="kpi-label">Total Revenue</div>
      <div class="kpi-sub">All-time closed won</div>
    </div>
  </div>

  {{-- ── KPI: New Leads ───────────────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::KPI_LEADS)
  <div class="ncv-card widget-kpi {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-body" style="padding:.875rem;">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="kpi-icon" style="background:#fff7ed;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>
        </div>
        <span class="widget-drag-handle"><i class="bi bi-grip-vertical"></i></span>
      </div>
      <div class="kpi-value">{{ number_format($widgetData['kpi']['new_leads_30d'] ?? 0) }}</div>
      <div class="kpi-label">New Leads</div>
      <div class="kpi-sub">Last 30 days</div>
    </div>
  </div>

  {{-- ── KPI: Open Opportunities ──────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::KPI_OPEN_OPPS)
  <div class="ncv-card widget-kpi {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-body" style="padding:.875rem;">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="kpi-icon" style="background:#f0fdf4;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <span class="widget-drag-handle"><i class="bi bi-grip-vertical"></i></span>
      </div>
      <div class="kpi-value">{{ number_format($widgetData['kpi']['open_opps_count'] ?? 0) }}</div>
      <div class="kpi-label">Open Opportunities</div>
      <div class="kpi-sub">${{ number_format(($widgetData['kpi']['open_opps_value'] ?? 0) / 1000, 1) }}k pipeline</div>
    </div>
  </div>

  {{-- ── KPI: Open Tickets ────────────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::KPI_OPEN_TICKETS)
  <div class="ncv-card widget-kpi {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-body" style="padding:.875rem;">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="kpi-icon" style="background:#fef2f2;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg>
        </div>
        <span class="widget-drag-handle"><i class="bi bi-grip-vertical"></i></span>
      </div>
      <div class="kpi-value">{{ number_format($widgetData['kpi']['open_tickets'] ?? 0) }}</div>
      <div class="kpi-label">Open Tickets</div>
      <div class="kpi-sub">Not resolved or closed</div>
    </div>
  </div>

  {{-- ── KPI: Win Rate ────────────────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::KPI_WIN_RATE)
  <div class="ncv-card widget-kpi {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-body" style="padding:.875rem;">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="kpi-icon" style="background:#faf5ff;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <span class="widget-drag-handle"><i class="bi bi-grip-vertical"></i></span>
      </div>
      <div class="kpi-value">{{ $widgetData['kpi']['win_rate'] ?? 0 }}%</div>
      <div class="kpi-label">Win Rate</div>
      <div class="kpi-sub">Last {{ $widgetData['kpi']['win_rate_period'] ?? '90 days' }}</div>
    </div>
  </div>

  {{-- ── Chart: Monthly Revenue ───────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::CHART_MONTHLY_REVENUE)
  <div class="ncv-card widget-chart {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Monthly Revenue</h6>
    </div>
    <div class="ncv-card-body" style="padding:.875rem;">
      <div style="position:relative; height:220px;">
        <canvas id="chartMonthlyRevenue"></canvas>
      </div>
    </div>
  </div>

  {{-- ── Chart: Pipeline by Stage ─────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::CHART_PIPELINE_STAGES)
  <div class="ncv-card widget-chart-sm {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Pipeline by Stage</h6>
    </div>
    <div class="ncv-card-body" style="padding:.875rem;">
      <div style="position:relative; height:220px;">
        <canvas id="chartPipelineStages"></canvas>
      </div>
    </div>
  </div>

  {{-- ── Chart: Leads by Source ───────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::CHART_LEADS_SOURCE)
  <div class="ncv-card widget-chart-sm {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Leads by Source</h6>
    </div>
    <div class="ncv-card-body" style="padding:.875rem;">
      <div style="position:relative; height:220px;">
        <canvas id="chartLeadsSource"></canvas>
      </div>
    </div>
  </div>

  {{-- ── Chart: Tickets by Status ─────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::CHART_TICKETS_STATUS)
  <div class="ncv-card widget-chart-sm {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Tickets by Status</h6>
    </div>
    <div class="ncv-card-body" style="padding:.875rem;">
      <div style="position:relative; height:220px;">
        <canvas id="chartTicketsStatus"></canvas>
      </div>
    </div>
  </div>

  {{-- ── Chart: Calls per Day ─────────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::CHART_CALLS_PER_DAY)
  <div class="ncv-card widget-chart-sm {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Calls per Day <small class="text-muted fw-normal" style="font-size:.7rem;">(7 days)</small></h6>
    </div>
    <div class="ncv-card-body" style="padding:.875rem;">
      <div style="position:relative; height:220px;">
        <canvas id="chartCallsPerDay"></canvas>
      </div>
    </div>
  </div>

  {{-- ── Table: Top Opportunities ─────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::TABLE_TOP_OPPS)
  <div class="ncv-card widget-table {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Top Opportunities</h6>
    </div>
    <div class="ncv-card-body p-0">
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Deal</th>
            <th>Stage</th>
            <th style="text-align:right;">Value</th>
            <th>Close</th>
          </tr>
        </thead>
        <tbody>
          @forelse($widgetData['top_opps'] ?? [] as $opp)
          <tr>
            <td>
              <a href="{{ route('opportunities.show', $opp['id']) }}" class="text-decoration-none fw-500" style="color:#0d1f4e;">
                {{ $opp['name'] }}
              </a>
              <div style="font-size:.72rem; color:var(--text-muted);">{{ $opp['account_name'] }}</div>
            </td>
            <td>
              <span class="ncv-badge" style="background:{{ $opp['stage_color'] }}22; color:{{ $opp['stage_color'] }}; border:1px solid {{ $opp['stage_color'] }}44; font-size:.68rem;">
                {{ $opp['stage_name'] }}
              </span>
            </td>
            <td style="text-align:right; font-weight:700; color:#0d1f4e;">${{ number_format($opp['amount']) }}</td>
            <td style="font-size:.78rem; color:var(--text-muted);">{{ $opp['close_date'] ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No open opportunities</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ── Table: Top Sales Reps ────────────────────────────────────────── --}}
  @elseif($type === \App\Models\DashboardWidget::TABLE_TOP_REPS)
  <div class="ncv-card widget-table {{ $widget->is_visible ? '' : 'widget-is-hidden' }}" data-type="{{ $type }}">
    <div class="ncv-card-header">
      <span class="widget-drag-handle me-2"><i class="bi bi-grip-vertical"></i></span>
      <h6 class="ncv-card-title">Top Sales Reps <small class="text-muted fw-normal" style="font-size:.7rem;">(this quarter)</small></h6>
    </div>
    <div class="ncv-card-body p-0">
      <table class="ncv-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Rep</th>
            <th style="text-align:right;">Revenue</th>
            <th style="text-align:center;">Deals</th>
          </tr>
        </thead>
        <tbody>
          @forelse($widgetData['top_reps'] ?? [] as $i => $rep)
          <tr>
            <td style="color:var(--text-muted); font-size:.75rem;">{{ $i + 1 }}</td>
            <td style="font-weight:500;">{{ $rep['user_name'] }}</td>
            <td style="text-align:right; font-weight:700; color:#0d1f4e;">${{ number_format($rep['revenue']) }}</td>
            <td style="text-align:center;">
              <span class="ncv-badge ncv-badge-info">{{ $rep['deals_count'] }}</span>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No data this quarter</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @endif
  @endforeach

</div>{{-- end #widgetGrid --}}

@endsection

@push('scripts')
{{-- SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
// ── PHP → JS data bridge ─────────────────────────────────────────────────
const MONTHLY_REVENUE_DATA  = @json($widgetData['monthly_revenue'] ?? []);
const PIPELINE_STAGES_DATA  = @json($widgetData['pipeline_stages'] ?? []);
const LEADS_SOURCE_DATA     = @json($widgetData['leads_source'] ?? []);
const TICKETS_STATUS_DATA   = @json($widgetData['tickets_status'] ?? []);
const CALLS_PER_DAY_DATA    = @json($widgetData['calls_per_day'] ?? []);

// ── Chart.js global defaults ─────────────────────────────────────────────
Chart.defaults.font.family  = "'Inter', sans-serif";
Chart.defaults.font.size    = 11;
Chart.defaults.color        = '#7a9bc4';
Chart.defaults.borderColor  = '#e8eff8';

// ── SortableJS init (BEFORE charts so canvas is stable) ──────────────────
const widgetGrid = document.getElementById('widgetGrid');
const sortable = Sortable.create(widgetGrid, {
  animation:  150,
  ghostClass: 'sortable-ghost',
  handle:     '.widget-drag-handle',
  onEnd: function () {
    const layout = [...widgetGrid.querySelectorAll('[data-type]')]
      .map((el, idx) => ({ type: el.dataset.type, position: idx }));

    fetch('{{ route('analytics.dashboard.layout') }}', {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ layout }),
    }).catch(console.error);
  }
});

// ── Chart initializer (runs after Sortable) ──────────────────────────────
function initCharts() {

  // Monthly Revenue — vertical bar
  const elMonthly = document.getElementById('chartMonthlyRevenue');
  if (elMonthly && MONTHLY_REVENUE_DATA.length) {
    new Chart(elMonthly, {
      type: 'bar',
      data: {
        labels:   MONTHLY_REVENUE_DATA.map(d => d.label),
        datasets: [{
          data:            MONTHLY_REVENUE_DATA.map(d => d.revenue),
          backgroundColor: '#2563eb',
          borderRadius:    4,
          borderSkipped:   false,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            grid:  { color: '#f0f4fb' },
            ticks: { callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v) }
          },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // Pipeline by Stage — doughnut
  const elPipeline = document.getElementById('chartPipelineStages');
  if (elPipeline && PIPELINE_STAGES_DATA.length) {
    const nonZero = PIPELINE_STAGES_DATA.filter(d => d.count > 0);
    new Chart(elPipeline, {
      type: 'doughnut',
      data: {
        labels:   nonZero.map(d => d.stage_name),
        datasets: [{
          data:            nonZero.map(d => d.count),
          backgroundColor: nonZero.map(d => d.color),
          borderWidth:     2,
          borderColor:     '#fff',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
          legend: { position: 'bottom', labels: { padding: 10, boxWidth: 12 } }
        }
      }
    });
  }

  // Leads by Source — doughnut
  const elLeads = document.getElementById('chartLeadsSource');
  if (elLeads && LEADS_SOURCE_DATA.length) {
    const colors = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#84cc16'];
    new Chart(elLeads, {
      type: 'doughnut',
      data: {
        labels:   LEADS_SOURCE_DATA.map(d => d.source),
        datasets: [{
          data:            LEADS_SOURCE_DATA.map(d => d.count),
          backgroundColor: LEADS_SOURCE_DATA.map((_, i) => colors[i % colors.length]),
          borderWidth:     2,
          borderColor:     '#fff',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
          legend: { position: 'bottom', labels: { padding: 10, boxWidth: 12 } }
        }
      }
    });
  }

  // Tickets by Status — doughnut
  const elTickets = document.getElementById('chartTicketsStatus');
  if (elTickets && TICKETS_STATUS_DATA.length) {
    const statusColors = {
      open: '#ef4444', in_progress: '#f59e0b', escalated: '#8b5cf6',
      resolved: '#10b981', closed: '#6b7280'
    };
    new Chart(elTickets, {
      type: 'doughnut',
      data: {
        labels:   TICKETS_STATUS_DATA.map(d => d.label),
        datasets: [{
          data:            TICKETS_STATUS_DATA.map(d => d.count),
          backgroundColor: TICKETS_STATUS_DATA.map(d => statusColors[d.status] || '#2563eb'),
          borderWidth:     2,
          borderColor:     '#fff',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
          legend: { position: 'bottom', labels: { padding: 10, boxWidth: 12 } }
        }
      }
    });
  }

  // Calls per Day — bar
  const elCalls = document.getElementById('chartCallsPerDay');
  if (elCalls && CALLS_PER_DAY_DATA.length) {
    new Chart(elCalls, {
      type: 'bar',
      data: {
        labels:   CALLS_PER_DAY_DATA.map(d => d.label),
        datasets: [{
          data:            CALLS_PER_DAY_DATA.map(d => d.count),
          backgroundColor: '#10b981',
          borderRadius:    4,
          borderSkipped:   false,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: '#f0f4fb' }, ticks: { precision: 0 } },
          x: { grid: { display: false } }
        }
      }
    });
  }
}

// Initialize charts after Sortable
initCharts();

// ── Configure panel toggle ───────────────────────────────────────────────
document.getElementById('btnWidgetSettings').addEventListener('click', function () {
  const panel = document.getElementById('widgetSettingsPanel');
  panel.style.display = panel.style.display === 'none' ? '' : 'none';
});

// ── Widget toggle ────────────────────────────────────────────────────────
function handleToggleWidget(type, btn) {
  fetch('{{ route('analytics.dashboard.toggle') }}', {
    method:  'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ widget_type: type }),
  })
  .then(r => r.json())
  .then(data => {
    btn.classList.toggle('active', data.is_visible);
    const card = widgetGrid.querySelector(`[data-type="${type}"]`);
    if (card) card.classList.toggle('widget-is-hidden', !data.is_visible);
  })
  .catch(console.error);
}
</script>
@endpush
