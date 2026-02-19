@extends('layouts.app')

@section('title', 'Sales Performance Report')
@section('page-title', 'Sales Performance')

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
  .perf-progress { height:6px; border-radius:999px; background:#e8eff8; overflow:hidden; margin-top:.3rem; }
  .perf-progress-bar { height:100%; border-radius:999px; background:#2563eb; transition:width .4s; }
</style>
@endpush

@section('content')

{{-- Header + filter --}}
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Sales Performance Report</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">Revenue, win rate, and activity by sales rep.</p>
  </div>
  <form method="GET" class="report-filter-bar">
    <label class="text-muted" style="font-size:.8rem; white-space:nowrap;">From</label>
    <input type="date" name="from" class="ncv-input ncv-input-sm" value="{{ $from }}">
    <label class="text-muted" style="font-size:.8rem;">to</label>
    <input type="date" name="to"   class="ncv-input ncv-input-sm" value="{{ $to }}">
    <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  </form>
</div>

@php
  $totalRevenue = collect($data)->sum('revenue');
  $maxRevenue   = collect($data)->max('revenue') ?: 1;
@endphp

<div class="row g-3">

  {{-- Horizontal bar chart --}}
  <div class="col-lg-6">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Revenue by Rep</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        <div style="position:relative; height:{{ max(200, count($data) * 40) }}px;">
          <canvas id="chartSalesPerf"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Team summary --}}
  <div class="col-lg-6">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Team Summary</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        <div class="row g-3 mb-3">
          <div class="col-6">
            <div style="font-size:1.4rem; font-weight:900; color:#0d1f4e;">${{ number_format($totalRevenue / 1000, 1) }}k</div>
            <div style="font-size:.75rem; color:var(--text-muted);">Total Team Revenue</div>
          </div>
          <div class="col-6">
            <div style="font-size:1.4rem; font-weight:900; color:#0d1f4e;">{{ collect($data)->sum('won_count') }}</div>
            <div style="font-size:.75rem; color:var(--text-muted);">Deals Won</div>
          </div>
          <div class="col-6">
            <div style="font-size:1.4rem; font-weight:900; color:#0d1f4e;">{{ collect($data)->sum('call_count') }}</div>
            <div style="font-size:.75rem; color:var(--text-muted);">Total Calls</div>
          </div>
          <div class="col-6">
            <div style="font-size:1.4rem; font-weight:900; color:#0d1f4e;">{{ collect($data)->sum('meeting_count') }}</div>
            <div style="font-size:.75rem; color:var(--text-muted);">Total Meetings</div>
          </div>
        </div>

        {{-- Per-rep revenue bars --}}
        @foreach($data as $rep)
        <div class="mb-2">
          <div class="d-flex justify-content-between align-items-baseline">
            <span style="font-size:.8rem; font-weight:500;">{{ $rep['user_name'] }}</span>
            <span style="font-size:.78rem; color:var(--text-muted);">${{ number_format($rep['revenue']) }}</span>
          </div>
          <div class="perf-progress">
            <div class="perf-progress-bar" style="width:{{ $maxRevenue > 0 ? round(($rep['revenue'] / $maxRevenue) * 100) : 0 }}%;"></div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

</div>

{{-- Detailed table --}}
<div class="ncv-card mt-3">
  <div class="ncv-card-header"><h6 class="ncv-card-title">Detailed Performance</h6></div>
  <div class="ncv-card-body p-0">
    <table class="ncv-table">
      <thead>
        <tr>
          <th>Rep</th>
          <th style="text-align:right;">Revenue</th>
          <th style="text-align:center;">Won</th>
          <th style="text-align:center;">Lost</th>
          <th style="text-align:center;">Win Rate</th>
          <th style="text-align:center;">Calls</th>
          <th style="text-align:center;">Meetings</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $rep)
        <tr>
          <td style="font-weight:500;">{{ $rep['user_name'] }}</td>
          <td style="text-align:right; font-weight:700; color:#0d1f4e;">${{ number_format($rep['revenue']) }}</td>
          <td style="text-align:center;">
            <span class="ncv-badge" style="background:#f0fdf4; color:#10b981;">{{ $rep['won_count'] }}</span>
          </td>
          <td style="text-align:center;">
            <span class="ncv-badge" style="background:#fef2f2; color:#ef4444;">{{ $rep['lost_count'] }}</span>
          </td>
          <td style="text-align:center;">
            @php $wr = $rep['win_rate']; @endphp
            <span class="ncv-badge" style="background:{{ $wr >= 60 ? '#f0fdf4' : ($wr >= 40 ? '#fff7ed' : '#fef2f2') }}; color:{{ $wr >= 60 ? '#10b981' : ($wr >= 40 ? '#f59e0b' : '#ef4444') }};">
              {{ $wr }}%
            </span>
          </td>
          <td style="text-align:center;">{{ $rep['call_count'] }}</td>
          <td style="text-align:center;">{{ $rep['meeting_count'] }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No data for this period</td></tr>
        @endforelse
      </tbody>
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

const PERF_DATA = @json($data);

if (PERF_DATA.length) {
  new Chart(document.getElementById('chartSalesPerf'), {
    type: 'bar',
    data: {
      labels:   PERF_DATA.map(d => d.user_name),
      datasets: [{
        label:           'Revenue',
        data:            PERF_DATA.map(d => d.revenue),
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
          ticks: { callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v) }
        },
        y: { grid: { display: false } }
      }
    }
  });
}
</script>
@endpush
