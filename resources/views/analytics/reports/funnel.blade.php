@extends('layouts.app')

@section('title', 'Funnel Analysis')
@section('page-title', 'Funnel Analysis')

@section('breadcrumb-items')
  <a href="{{ route('analytics.dashboard') }}" class="text-decoration-none" style="color:inherit;">Analytics</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:inherit;">Reports</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .funnel-bar-wrap { margin-bottom:.75rem; }
  .funnel-bar-label { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:.3rem; }
  .funnel-bar-track { height:22px; border-radius:4px; background:#e8eff8; overflow:hidden; position:relative; }
  .funnel-bar-fill  { height:100%; border-radius:4px; transition:width .5s; }
  .funnel-bar-count { position:absolute; right:.5rem; top:50%; transform:translateY(-50%); font-size:.75rem; font-weight:600; color:#fff; }
  .funnel-conv-badge { font-size:.68rem; padding:.15rem .45rem; border-radius:.25rem; font-weight:600; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Funnel Analysis</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">Stage-by-stage opportunity counts and conversion rates.</p>
  </div>
</div>

{{-- KPI summary --}}
@php
  $stages     = $data['stages'];
  $wonCount   = $data['won_count'];
  $overallPct = $data['overall_conversion'];
  $topCount   = $stages[0]['count'] ?? 0;
  $totalValue = collect($stages)->sum('open_value');
@endphp
<div class="row g-3 mb-3">
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#2563eb;">{{ $topCount }}</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Deals in Pipeline</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#10b981;">{{ $wonCount }}</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Total Deals Won</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#f59e0b;">{{ $overallPct }}%</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Overall Win Rate</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="ncv-card ncv-card-body text-center" style="padding:1rem;">
      <div style="font-size:1.6rem; font-weight:900; color:#0d1f4e;">${{ number_format($totalValue / 1000, 1) }}k</div>
      <div style="font-size:.78rem; color:var(--text-muted); margin-top:.2rem;">Open Pipeline Value</div>
    </div>
  </div>
</div>

<div class="row g-3">

  {{-- Funnel bars --}}
  <div class="col-lg-6">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Pipeline Funnel</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        @if(count($stages))
          @php $maxCount = max(collect($stages)->max('count'), 1); @endphp
          @foreach($stages as $i => $stage)
          <div class="funnel-bar-wrap">
            <div class="funnel-bar-label">
              <span style="font-size:.82rem; font-weight:600;">{{ $stage['stage_name'] }}</span>
              <div class="d-flex align-items-center gap-2">
                @if($i > 0 && $stage['conversion'] > 0)
                  <span class="funnel-conv-badge" style="background:#f0fdf4; color:#10b981;">
                    ↓ {{ $stage['conversion'] }}%
                  </span>
                @elseif($i > 0)
                  <span class="funnel-conv-badge" style="background:#fef2f2; color:#ef4444;">0%</span>
                @endif
                <span style="font-size:.8rem; color:var(--text-muted);">${{ number_format($stage['open_value']) }}</span>
              </div>
            </div>
            <div class="funnel-bar-track">
              <div class="funnel-bar-fill"
                   style="width:{{ $maxCount > 0 ? round(($stage['count'] / $maxCount) * 100) : 0 }}%; background:{{ $stage['color'] }};"></div>
              <span class="funnel-bar-count">{{ $stage['count'] }}</span>
            </div>
          </div>
          @endforeach
        @else
          <p class="text-muted text-center py-4" style="font-size:.85rem;">No pipeline stages configured.</p>
        @endif
      </div>
    </div>
  </div>

  {{-- Chart --}}
  <div class="col-lg-6">
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><h6 class="ncv-card-title">Stage Distribution</h6></div>
      <div class="ncv-card-body" style="padding:.875rem;">
        <div style="position:relative; height:{{ max(220, count($stages) * 40) }}px;">
          <canvas id="chartFunnel"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Stage detail table --}}
<div class="ncv-card mt-3">
  <div class="ncv-card-header"><h6 class="ncv-card-title">Stage Detail</h6></div>
  <div class="ncv-card-body p-0">
    <table class="ncv-table">
      <thead>
        <tr>
          <th>Stage</th>
          <th style="text-align:center;">Deals</th>
          <th style="text-align:right;">Open Value</th>
          <th style="text-align:center;">Conv. from Prev.</th>
        </tr>
      </thead>
      <tbody>
        @forelse($stages as $i => $stage)
        <tr>
          <td>
            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $stage['color'] }}; margin-right:.4rem;"></span>
            <span style="font-weight:500;">{{ $stage['stage_name'] }}</span>
          </td>
          <td style="text-align:center; font-weight:600;">{{ $stage['count'] }}</td>
          <td style="text-align:right;">${{ number_format($stage['open_value']) }}</td>
          <td style="text-align:center;">
            @if($i === 0)
              <span style="color:var(--text-muted); font-size:.78rem;">—</span>
            @else
              @php $conv = $stage['conversion']; @endphp
              <span class="ncv-badge" style="background:{{ $conv >= 50 ? '#f0fdf4' : ($conv >= 25 ? '#fff7ed' : '#fef2f2') }}; color:{{ $conv >= 50 ? '#10b981' : ($conv >= 25 ? '#f59e0b' : '#ef4444') }};">
                {{ $conv }}%
              </span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem;">No stages found</td></tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr style="background:var(--bg-secondary); font-weight:700;">
          <td>Won (Closed)</td>
          <td style="text-align:center;">{{ $wonCount }}</td>
          <td style="text-align:right;">—</td>
          <td style="text-align:center;">
            <span class="ncv-badge" style="background:{{ $overallPct >= 30 ? '#f0fdf4' : '#fff7ed' }}; color:{{ $overallPct >= 30 ? '#10b981' : '#f59e0b' }};">
              {{ $overallPct }}% overall
            </span>
          </td>
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

const FUNNEL_DATA = @json($stages);

if (FUNNEL_DATA.length) {
  new Chart(document.getElementById('chartFunnel'), {
    type: 'bar',
    data: {
      labels:   FUNNEL_DATA.map(d => d.stage_name),
      datasets: [{
        label:           'Deals',
        data:            FUNNEL_DATA.map(d => d.count),
        backgroundColor: FUNNEL_DATA.map(d => d.color),
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
