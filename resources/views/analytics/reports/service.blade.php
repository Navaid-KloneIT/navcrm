@extends('layouts.app')

@section('title', 'Service Report')
@section('page-title', 'Service Report')

@section('breadcrumb-items')
  <a href="{{ route('analytics.dashboard') }}" class="text-decoration-none" style="color:inherit;">Analytics</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:inherit;">Reports</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .report-filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .svc-timing-val { font-size:1.4rem; font-weight:900; color:#0d1f4e; }
  .svc-timing-label { font-size:.75rem; color:var(--text-muted); margin-top:.1rem; }
</style>
@endpush

@section('content')

{{-- Header + filter --}}
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Service Report</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">Ticket volumes, response times, and SLA compliance.</p>
  </div>
  <form method="GET" class="report-filter-bar">
    <label class="text-muted" style="font-size:.8rem; white-space:nowrap;">From</label>
    <input type="date" name="from" class="ncv-input ncv-input-sm" value="{{ $from }}">
    <label class="text-muted" style="font-size:.8rem;">to</label>
    <input type="date" name="to"   class="ncv-input ncv-input-sm" value="{{ $to }}">
    <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  </form>
</div>

{{-- KPI summary cards --}}
<div class="row g-3 mb-3">
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#2563eb;">{{ number_format($data['total_tickets']) }}</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Total Tickets</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      @if($data['avg_response_minutes'] !== null)
        @php
          $respMins = $data['avg_response_minutes'];
          $respLabel = $respMins >= 60
            ? round($respMins / 60, 1) . 'h'
            : $respMins . 'm';
        @endphp
        <div style="font-size:1.6rem; font-weight:900; color:#10b981;">{{ $respLabel }}</div>
      @else
        <div style="font-size:1.6rem; font-weight:900; color:#9ca3af;">—</div>
      @endif
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Avg First Response</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      @if($data['avg_resolution_hours'] !== null)
        <div style="font-size:1.6rem; font-weight:900; color:#f59e0b;">{{ $data['avg_resolution_hours'] }}h</div>
      @else
        <div style="font-size:1.6rem; font-weight:900; color:#9ca3af;">—</div>
      @endif
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Avg Resolution Time</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:{{ $data['sla_breach_count'] > 0 ? '#ef4444' : '#10b981' }};">
        {{ $data['sla_breach_count'] }}
      </div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">SLA Breaches</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">

  {{-- Status doughnut --}}
  <div class="col-lg-4">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Tickets by Status</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        <div style="position:relative; height:220px;">
          <canvas id="chartTicketStatus"></canvas>
        </div>
        <div class="mt-3">
          @foreach($data['by_status'] as $row)
          <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.8rem;">
            <span>{{ $row['label'] }}</span>
            <span class="ncv-badge" style="background:#f1f5f9; color:#334155;">{{ $row['count'] }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Priority doughnut --}}
  <div class="col-lg-4">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Tickets by Priority</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        <div style="position:relative; height:220px;">
          <canvas id="chartTicketPriority"></canvas>
        </div>
        <div class="mt-3">
          @php
            $priorityColors = [
              'critical' => '#ef4444',
              'high'     => '#f59e0b',
              'medium'   => '#2563eb',
              'low'      => '#10b981',
            ];
          @endphp
          @foreach($data['by_priority'] as $row)
          <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.8rem;">
            <span style="display:flex; align-items:center; gap:.35rem;">
              <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $priorityColors[$row['priority']] ?? '#6b7280' }};"></span>
              {{ $row['label'] }}
            </span>
            <span class="ncv-badge" style="background:#f1f5f9; color:#334155;">{{ $row['count'] }}</span>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Agent bar chart --}}
  <div class="col-lg-4">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Tickets by Agent</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        <div style="position:relative; height:{{ max(200, count($data['by_agent']) * 40) }}px;">
          <canvas id="chartTicketAgent"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Ticket breakdown tables --}}
<div class="row g-3">

  {{-- By status table --}}
  <div class="col-lg-6">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Status Breakdown</h6></div>
      <div class="ncv-card-body p-0">
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Status</th>
              <th style="text-align:center;">Count</th>
              <th style="text-align:right;">Share</th>
            </tr>
          </thead>
          <tbody>
            @php $totalTickets = $data['total_tickets'] ?: 1; @endphp
            @forelse($data['by_status'] as $row)
            <tr>
              <td>{{ $row['label'] }}</td>
              <td style="text-align:center; font-weight:600;">{{ $row['count'] }}</td>
              <td style="text-align:right; color:var(--text-muted);">
                {{ round(($row['count'] / $totalTickets) * 100, 1) }}%
              </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- By agent table --}}
  <div class="col-lg-6">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Agent Workload</h6></div>
      <div class="ncv-card-body p-0">
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Agent</th>
              <th style="text-align:center;">Tickets</th>
              <th style="text-align:right;">Share</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data['by_agent'] as $row)
            <tr>
              <td style="font-weight:500;">{{ $row['agent_name'] }}</td>
              <td style="text-align:center; font-weight:600;">{{ $row['count'] }}</td>
              <td style="text-align:right; color:var(--text-muted);">
                {{ round(($row['count'] / $totalTickets) * 100, 1) }}%
              </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#7a9bc4';

const STATUS_DATA   = @json($data['by_status']);
const PRIORITY_DATA = @json($data['by_priority']);
const AGENT_DATA    = @json($data['by_agent']);

const STATUS_PALETTE = {
  open:        '#2563eb',
  in_progress: '#f59e0b',
  on_hold:     '#6b7280',
  resolved:    '#10b981',
  closed:      '#9ca3af',
};
const PRIORITY_PALETTE = {
  critical: '#ef4444',
  high:     '#f59e0b',
  medium:   '#2563eb',
  low:      '#10b981',
};

// Tickets by Status — doughnut
if (STATUS_DATA.length) {
  new Chart(document.getElementById('chartTicketStatus'), {
    type: 'doughnut',
    data: {
      labels:   STATUS_DATA.map(d => d.label),
      datasets: [{
        data:            STATUS_DATA.map(d => d.count),
        backgroundColor: STATUS_DATA.map(d => STATUS_PALETTE[d.status] ?? '#6b7280'),
        borderWidth:     2,
        borderColor:     '#fff',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false }
      }
    }
  });
}

// Tickets by Priority — doughnut
if (PRIORITY_DATA.length) {
  new Chart(document.getElementById('chartTicketPriority'), {
    type: 'doughnut',
    data: {
      labels:   PRIORITY_DATA.map(d => d.label),
      datasets: [{
        data:            PRIORITY_DATA.map(d => d.count),
        backgroundColor: PRIORITY_DATA.map(d => PRIORITY_PALETTE[d.priority] ?? '#6b7280'),
        borderWidth:     2,
        borderColor:     '#fff',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false }
      }
    }
  });
}

// Tickets by Agent — horizontal bar
if (AGENT_DATA.length) {
  new Chart(document.getElementById('chartTicketAgent'), {
    type: 'bar',
    data: {
      labels:   AGENT_DATA.map(d => d.agent_name),
      datasets: [{
        label:           'Tickets',
        data:            AGENT_DATA.map(d => d.count),
        backgroundColor: '#2563eb',
        borderRadius:    4,
        borderSkipped:   false,
      }]
    },
    options: {
      indexAxis:  'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid:  { color: '#f0f4fb' },
          ticks: { precision: 0 }
        },
        y: { grid: { display: false } }
      }
    }
  });
}
</script>
@endpush
