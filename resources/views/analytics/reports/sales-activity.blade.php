@extends('layouts.app')

@section('title', 'Sales Activity Report')
@section('page-title', 'Sales Activity')

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
</style>
@endpush

@section('content')

{{-- Header + date filter --}}
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Sales Activity Report</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">Calls, meetings, and tasks completed per day.</p>
  </div>
  <form method="GET" class="report-filter-bar">
    <label class="text-muted" style="font-size:.8rem; white-space:nowrap;">From</label>
    <input type="date" name="from" class="ncv-input ncv-input-sm" value="{{ $from }}">
    <label class="text-muted" style="font-size:.8rem;">to</label>
    <input type="date" name="to"   class="ncv-input ncv-input-sm" value="{{ $to }}">
    <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  </form>
</div>

{{-- Summary KPIs --}}
@php
  $totalCalls    = collect($data)->sum('calls');
  $totalMeetings = collect($data)->sum('meetings');
  $totalTasks    = collect($data)->sum('tasks');
@endphp
<div class="row g-3 mb-3">
  <div class="col-sm-4">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#2563eb;">{{ number_format($totalCalls) }}</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Total Calls</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#10b981;">{{ number_format($totalMeetings) }}</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Total Meetings / Events</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#f59e0b;">{{ number_format($totalTasks) }}</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Tasks Completed</div>
    </div>
  </div>
</div>

{{-- Chart --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-header">
    <h6 class="ncv-card-title">Activity Per Day</h6>
  </div>
  <div class="ncv-card-body" style="padding:.875rem;">
    <div style="position:relative; height:320px;">
      <canvas id="chartSalesActivity"></canvas>
    </div>
  </div>
</div>

{{-- Data table --}}
<div class="ncv-card">
  <div class="ncv-card-header">
    <h6 class="ncv-card-title">Daily Breakdown</h6>
  </div>
  <div class="ncv-card-body p-0">
    <table class="ncv-table">
      <thead>
        <tr>
          <th>Date</th>
          <th style="text-align:center;">Calls</th>
          <th style="text-align:center;">Meetings</th>
          <th style="text-align:center;">Tasks Completed</th>
          <th style="text-align:center;">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $row)
        <tr>
          <td style="font-size:.82rem;">{{ $row['label'] }}</td>
          <td style="text-align:center;">
            @if($row['calls'] > 0)
              <span class="ncv-badge" style="background:#eff6ff; color:#2563eb;">{{ $row['calls'] }}</span>
            @else
              <span style="color:var(--text-muted);">—</span>
            @endif
          </td>
          <td style="text-align:center;">
            @if($row['meetings'] > 0)
              <span class="ncv-badge" style="background:#f0fdf4; color:#10b981;">{{ $row['meetings'] }}</span>
            @else
              <span style="color:var(--text-muted);">—</span>
            @endif
          </td>
          <td style="text-align:center;">
            @if($row['tasks'] > 0)
              <span class="ncv-badge" style="background:#fff7ed; color:#f59e0b;">{{ $row['tasks'] }}</span>
            @else
              <span style="color:var(--text-muted);">—</span>
            @endif
          </td>
          <td style="text-align:center; font-weight:600;">{{ $row['calls'] + $row['meetings'] + $row['tasks'] }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background:var(--bg-secondary); font-weight:700;">
          <td>Total</td>
          <td style="text-align:center;">{{ $totalCalls }}</td>
          <td style="text-align:center;">{{ $totalMeetings }}</td>
          <td style="text-align:center;">{{ $totalTasks }}</td>
          <td style="text-align:center;">{{ $totalCalls + $totalMeetings + $totalTasks }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#7a9bc4';

const ACTIVITY_DATA = @json($data);

new Chart(document.getElementById('chartSalesActivity'), {
  type: 'bar',
  data: {
    labels: ACTIVITY_DATA.map(d => d.label),
    datasets: [
      {
        label:           'Calls',
        data:            ACTIVITY_DATA.map(d => d.calls),
        backgroundColor: '#2563eb',
        borderRadius:    3,
        borderSkipped:   false,
      },
      {
        label:           'Meetings',
        data:            ACTIVITY_DATA.map(d => d.meetings),
        backgroundColor: '#10b981',
        borderRadius:    3,
        borderSkipped:   false,
      },
      {
        label:           'Tasks Completed',
        data:            ACTIVITY_DATA.map(d => d.tasks),
        backgroundColor: '#f59e0b',
        borderRadius:    3,
        borderSkipped:   false,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { position: 'top' } },
    scales: {
      x: { grid: { display: false } },
      y: { grid: { color: '#f0f4fb' }, ticks: { precision: 0 } }
    }
  }
});
</script>
@endpush
