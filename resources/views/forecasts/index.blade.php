@extends('layouts.app')

@section('title', 'Forecast Dashboard')
@section('page-title', 'Forecasts')

@section('breadcrumb-items')
  <span style="color:inherit;">Forecasts</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  /* Period toggle */
  .ncv-period-toggle {
    display: inline-flex;
    background: #f0f4fb;
    border-radius: .625rem;
    padding: 3px;
    gap: 2px;
  }
  .ncv-period-btn {
    padding: .35rem .875rem;
    border: none;
    background: transparent;
    border-radius: .5rem;
    font-size: .82rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all .18s;
  }
  .ncv-period-btn.active {
    background: #fff;
    color: var(--ncv-blue-600);
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
  }

  /* Attainment ring */
  .attainment-ring {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
  }
  .attainment-ring svg { transform: rotate(-90deg); }
  .attainment-ring .ring-text {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
  }
  .attainment-ring .ring-pct {
    font-size: 1.75rem;
    font-weight: 900;
    color: #0d1f4e;
    line-height: 1;
  }
  .attainment-ring .ring-label {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--text-muted);
    margin-top: .25rem;
  }

  /* Grouped bar chart */
  .fcst-chart-wrap {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    height: 200px;
    padding-bottom: .25rem;
  }
  .fcst-chart-group {
    flex: 1;
    display: flex;
    align-items: flex-end;
    gap: 3px;
  }
  .fcst-bar {
    flex: 1;
    border-radius: 4px 4px 0 0;
    transition: opacity .2s;
    cursor: default;
    position: relative;
  }
  .fcst-bar:hover { opacity: .85; }
  .fcst-bar.bar-quota  { background: linear-gradient(0deg, #93c5fd 0%, #bfdbfe 100%); }
  .fcst-bar.bar-actual { background: linear-gradient(0deg, #2563eb 0%, #60a5fa 100%); }
  .fcst-bar.bar-pipeline { background: linear-gradient(0deg, #10b981 0%, #6ee7b7 100%); }
  .fcst-chart-labels {
    display: flex;
    gap: 10px;
    padding-top: .5rem;
    border-top: 1px solid var(--border-color);
  }
  .fcst-chart-labels span {
    flex: 1;
    text-align: center;
    font-size: .63rem;
    color: var(--text-muted);
    font-weight: 700;
  }

  /* Attainment progress bar (table rows) */
  .attain-bar-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
  }
  .attain-bar-track {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 99px;
    overflow: hidden;
  }
  .attain-bar-fill {
    height: 100%;
    border-radius: 99px;
    transition: width .5s;
  }
  .attain-bar-pct {
    font-size: .75rem;
    font-weight: 700;
    min-width: 34px;
    text-align: right;
  }

  /* Category card */
  .fcst-cat-card {
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: .75rem;
    padding: 1rem 1.125rem;
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  .fcst-cat-icon {
    width: 42px;
    height: 42px;
    border-radius: .625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
  }

  /* Trend chip */
  .trend-up   { color: #10b981; font-weight: 700; font-size: .78rem; }
  .trend-down { color: #ef4444; font-weight: 700; font-size: .78rem; }
  .trend-flat { color: #f59e0b; font-weight: 700; font-size: .78rem; }

  /* Waterfall segment (commit chart) */
  .commit-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .5rem 0;
    border-bottom: 1px solid var(--border-color);
    font-size: .83rem;
  }
  .commit-row:last-child { border-bottom: none; }
  .commit-label { min-width: 110px; color: var(--text-muted); font-weight: 600; }
  .commit-bar-wrap { flex: 1; height: 20px; background: #f0f4fb; border-radius: 4px; overflow: hidden; }
  .commit-bar { height: 100%; border-radius: 4px; }
  .commit-value { min-width: 72px; text-align: right; font-weight: 700; color: var(--text-primary); }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Forecast Dashboard</h1>
    <p class="ncv-page-subtitle mb-0">Q1 2026 · Jan – Mar</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    {{-- Period toggle --}}
    <div class="ncv-period-toggle" id="periodToggle">
      <button class="ncv-period-btn" onclick="setPeriod('monthly',this)">Monthly</button>
      <button class="ncv-period-btn active" onclick="setPeriod('quarterly',this)">Quarterly</button>
      <button class="ncv-period-btn" onclick="setPeriod('annual',this)">Annual</button>
    </div>
    <select class="ncv-select" style="width:auto;font-size:.82rem;padding:.35rem .75rem;height:36px;" id="periodSelect">
      <option value="q1-2026" selected>Q1 2026</option>
      <option value="q4-2025">Q4 2025</option>
      <option value="q3-2025">Q3 2025</option>
      <option value="q2-2025">Q2 2025</option>
    </select>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="window.print()">
      <i class="bi bi-download"></i> Export
    </button>
  </div>
</div>

{{-- ── KPI Stat Cards ── --}}
<div class="row g-3 mb-3">
  @foreach([
    ['icon'=>'bi-bullseye',         'label'=>'Quota',             'value'=>'$480,000', 'sub'=>'Q1 2026 target',           'color'=>'#2563eb','bg'=>'#dbeafe'],
    ['icon'=>'bi-check-circle-fill','label'=>'Closed Won',        'value'=>'$312,450', 'sub'=>'65.1% of quota · +12% QoQ','color'=>'#10b981','bg'=>'#d1fae5'],
    ['icon'=>'bi-funnel-fill',      'label'=>'Weighted Pipeline', 'value'=>'$198,720', 'sub'=>'Open opp. × probability',  'color'=>'#f59e0b','bg'=>'#fef3c7'],
    ['icon'=>'bi-graph-up-arrow',   'label'=>'Projected Total',   'value'=>'$511,170', 'sub'=>'Won + weighted pipeline',  'color'=>'#8b5cf6','bg'=>'#ede9fe'],
  ] as $kpi)
  <div class="col-6 col-xl-3">
    <div class="ncv-card h-100">
      <div class="ncv-card-body" style="padding:1.125rem;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
          <div>
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.5rem;">{{ $kpi['label'] }}</div>
            <div style="font-size:1.5rem;font-weight:900;color:#0d1f4e;letter-spacing:-.02em;line-height:1;">{{ $kpi['value'] }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.375rem;">{{ $kpi['sub'] }}</div>
          </div>
          <div style="width:40px;height:40px;border-radius:.625rem;background:{{ $kpi['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi {{ $kpi['icon'] }}" style="font-size:1rem;color:{{ $kpi['color'] }};"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- ── Main Charts Row ── --}}
<div class="row g-3 mb-3">

  {{-- Quota vs Actual vs Pipeline (grouped bars) --}}
  <div class="col-12 col-lg-8">
    <div class="ncv-card h-100">
      <div class="ncv-card-header" style="flex-wrap:wrap;gap:.5rem;">
        <h6 class="ncv-card-title"><i class="bi bi-bar-chart-fill me-2" style="color:var(--ncv-blue-500);"></i>Quota vs Actual vs Pipeline</h6>
        <div style="display:flex;align-items:center;gap:1rem;font-size:.72rem;font-weight:600;color:var(--text-muted);">
          <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#93c5fd;margin-right:4px;"></span>Quota</span>
          <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#2563eb;margin-right:4px;"></span>Won</span>
          <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#10b981;margin-right:4px;"></span>Weighted Pipe</span>
        </div>
      </div>
      <div class="ncv-card-body">
        {{-- Bar chart (CSS, max value ~200k per month) --}}
        <div class="fcst-chart-wrap" id="fcstChart">
          @foreach([
            ['m'=>'Jan','quota'=>160,'actual'=>148,'pipe'=>38],
            ['m'=>'Feb','quota'=>160,'actual'=>112,'pipe'=>85],
            ['m'=>'Mar','quota'=>160,'actual'=>52, 'pipe'=>75],
          ] as $mo)
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;">
            <div class="fcst-chart-group" style="height:100%;width:100%;">
              <div class="fcst-bar bar-quota"    style="height:{{ $mo['quota'] }}%;"></div>
              <div class="fcst-bar bar-actual"   style="height:{{ $mo['actual'] }}%;"></div>
              <div class="fcst-bar bar-pipeline" style="height:{{ $mo['pipe'] }}%;"></div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="fcst-chart-labels">
          @foreach(['January','February','March'] as $ml)
          <span>{{ $ml }}</span>
          @endforeach
        </div>
        {{-- Y-axis labels --}}
        <div style="display:flex;justify-content:space-between;margin-top:.5rem;">
          @foreach(['$0','$40k','$80k','$120k','$160k'] as $yl)
          <span style="font-size:.65rem;color:var(--text-muted);font-weight:600;">{{ $yl }}</span>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Quota Attainment Ring --}}
  <div class="col-12 col-lg-4">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-bullseye me-2" style="color:var(--ncv-blue-500);"></i>Quota Attainment</h6>
      </div>
      <div class="ncv-card-body" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.25rem;padding-top:1.5rem;padding-bottom:1.5rem;">
        {{-- SVG ring --}}
        <div class="attainment-ring">
          <svg width="140" height="140" viewBox="0 0 140 140">
            {{-- Track --}}
            <circle cx="70" cy="70" r="58" fill="none" stroke="#e2e8f0" stroke-width="14"/>
            {{-- Won (65.1%) --}}
            <circle cx="70" cy="70" r="58" fill="none" stroke="#2563eb" stroke-width="14"
              stroke-dasharray="{{ round(58 * 2 * 3.14159 * 0.651) }} 999" stroke-linecap="round"/>
            {{-- Weighted Pipeline overlay --}}
            <circle cx="70" cy="70" r="58" fill="none" stroke="#10b981" stroke-width="14" stroke-opacity=".45"
              stroke-dasharray="{{ round(58 * 2 * 3.14159 * (0.413 - 0)) }} 999"
              stroke-dashoffset="-{{ round(58 * 2 * 3.14159 * 0.651) }}"
              stroke-linecap="round"/>
          </svg>
          <div class="ring-text">
            <div class="ring-pct">65%</div>
            <div class="ring-label">Attained</div>
          </div>
        </div>

        {{-- Legend --}}
        <div style="width:100%;display:flex;flex-direction:column;gap:.5rem;">
          @foreach([
            ['color'=>'#2563eb','label'=>'Closed Won',        'val'=>'$312,450','pct'=>'65.1%'],
            ['color'=>'#10b981','label'=>'Weighted Pipeline', 'val'=>'$198,720','pct'=>'+41.4%'],
            ['color'=>'#e2e8f0','label'=>'Remaining Quota',   'val'=>'$167,550','pct'=>'34.9%'],
          ] as $leg)
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:.8rem;">
            <div style="display:flex;align-items:center;gap:.5rem;">
              <span style="width:10px;height:10px;border-radius:2px;background:{{ $leg['color'] }};display:inline-block;flex-shrink:0;"></span>
              <span style="color:var(--text-muted);font-weight:600;">{{ $leg['label'] }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;">
              <span style="font-weight:700;color:var(--text-primary);">{{ $leg['val'] }}</span>
              <span style="color:var(--text-muted);font-size:.72rem;">{{ $leg['pct'] }}</span>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ── Sales Reps Targets Table + Commit Waterfall ── --}}
<div class="row g-3 mb-3">

  {{-- Sales Rep Performance --}}
  <div class="col-12 col-lg-7">
    <div class="ncv-card h-100">
      <div class="ncv-card-header" style="flex-wrap:wrap;gap:.5rem;">
        <h6 class="ncv-card-title"><i class="bi bi-people-fill me-2" style="color:var(--ncv-blue-500);"></i>Rep Performance</h6>
        <select class="ncv-select" style="width:auto;font-size:.78rem;height:30px;padding:.2rem .625rem;">
          <option>All Reps</option>
          <option>Team Alpha</option>
          <option>Team Beta</option>
        </select>
      </div>
      <div class="ncv-card-body" style="padding:0;">
        <table class="ncv-table" style="margin:0;">
          <thead>
            <tr>
              <th>Sales Rep</th>
              <th style="text-align:right;">Quota</th>
              <th style="text-align:right;">Won</th>
              <th style="min-width:140px;">Attainment</th>
              <th style="text-align:right;">Pipeline</th>
              <th>Trend</th>
            </tr>
          </thead>
          <tbody>
            @foreach([
              ['name'=>'Sarah Johnson', 'avatar'=>'SJ','quota'=>120000,'won'=>98400, 'pipe'=>28500,'trend'=>'up',  'pct'=>82],
              ['name'=>'Marcus Williams','avatar'=>'MW','quota'=>120000,'won'=>75600, 'pipe'=>44200,'trend'=>'up',  'pct'=>63],
              ['name'=>'Priya Mehta',   'avatar'=>'PM','quota'=>80000, 'won'=>72000, 'pipe'=>15800,'trend'=>'flat','pct'=>90],
              ['name'=>'David Chen',    'avatar'=>'DC','quota'=>80000, 'won'=>42000, 'pipe'=>61000,'trend'=>'down','pct'=>53],
              ['name'=>'Lisa Park',     'avatar'=>'LP','quota'=>80000, 'won'=>24450, 'pipe'=>49220,'trend'=>'up',  'pct'=>31],
            ] as $rep)
            @php
              $barColor = $rep['pct'] >= 80 ? '#10b981' : ($rep['pct'] >= 50 ? '#f59e0b' : '#ef4444');
              $trendIcon = $rep['trend'] === 'up' ? 'bi-arrow-up-right' : ($rep['trend'] === 'down' ? 'bi-arrow-down-right' : 'bi-dash');
              $trendClass = 'trend-' . $rep['trend'];
            @endphp
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:.625rem;">
                  <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:.7rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $rep['avatar'] }}</div>
                  <span style="font-weight:600;font-size:.83rem;color:var(--text-primary);">{{ $rep['name'] }}</span>
                </div>
              </td>
              <td style="text-align:right;font-size:.83rem;color:var(--text-muted);">${{ number_format($rep['quota']) }}</td>
              <td style="text-align:right;font-size:.83rem;font-weight:700;color:var(--text-primary);">${{ number_format($rep['won']) }}</td>
              <td>
                <div class="attain-bar-wrap">
                  <div class="attain-bar-track">
                    <div class="attain-bar-fill" style="width:{{ min($rep['pct'],100) }}%;background:{{ $barColor }};"></div>
                  </div>
                  <span class="attain-bar-pct" style="color:{{ $barColor }};">{{ $rep['pct'] }}%</span>
                </div>
              </td>
              <td style="text-align:right;font-size:.83rem;color:#10b981;font-weight:600;">${{ number_format($rep['pipe']) }}</td>
              <td><span class="{{ $trendClass }}"><i class="bi {{ $trendIcon }}"></i> {{ ucfirst($rep['trend']) }}</span></td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:#f8faff;">
              <td style="font-weight:800;font-size:.83rem;color:var(--text-primary);">Team Total</td>
              <td style="text-align:right;font-weight:800;font-size:.83rem;">$480,000</td>
              <td style="text-align:right;font-weight:800;font-size:.83rem;color:#2563eb;">$312,450</td>
              <td>
                <div class="attain-bar-wrap">
                  <div class="attain-bar-track">
                    <div class="attain-bar-fill" style="width:65%;background:#2563eb;"></div>
                  </div>
                  <span class="attain-bar-pct" style="color:#2563eb;">65%</span>
                </div>
              </td>
              <td style="text-align:right;font-weight:800;font-size:.83rem;color:#10b981;">$198,720</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  {{-- Forecast Commit / Waterfall --}}
  <div class="col-12 col-lg-5">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-layers-fill me-2" style="color:var(--ncv-blue-500);"></i>Forecast Commit (Q1)</h6>
      </div>
      <div class="ncv-card-body">
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem;">Revenue breakdown by forecast category, scaled to quota ($480k).</p>
        @foreach([
          ['label'=>'Closed Won',  'value'=>312450,'color'=>'#2563eb','pct'=>65,'fmt'=>'$312,450'],
          ['label'=>'Commit',      'value'=>84200, 'color'=>'#10b981','pct'=>18,'fmt'=>'$84,200'],
          ['label'=>'Best Case',   'value'=>67800, 'color'=>'#f59e0b','pct'=>14,'fmt'=>'$67,800'],
          ['label'=>'Pipeline',    'value'=>46720, 'color'=>'#a78bfa','pct'=>10,'fmt'=>'$46,720'],
          ['label'=>'Omitted',     'value'=>22000, 'color'=>'#e2e8f0','pct'=>5, 'fmt'=>'$22,000'],
        ] as $seg)
        <div class="commit-row">
          <div class="commit-label">{{ $seg['label'] }}</div>
          <div class="commit-bar-wrap">
            <div class="commit-bar" style="width:{{ $seg['pct'] }}%;background:{{ $seg['color'] }};"></div>
          </div>
          <div class="commit-value">{{ $seg['fmt'] }}</div>
        </div>
        @endforeach

        <div style="margin-top:1.25rem;padding:1rem;background:#f8faff;border-radius:.625rem;border:1px solid var(--border-color);">
          <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:.5rem;">
            <span style="color:var(--text-muted);font-weight:600;">Projected Total</span>
            <span style="font-weight:900;color:#0d1f4e;font-size:1.05rem;">$511,170</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.82rem;">
            <span style="color:var(--text-muted);font-weight:600;">vs Quota</span>
            <span style="color:#10b981;font-weight:700;">+6.5% <i class="bi bi-arrow-up-right"></i></span>
          </div>
          <div style="margin-top:.75rem;background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
            <div style="height:100%;width:65%;background:linear-gradient(90deg,#2563eb,#10b981);border-radius:99px;"></div>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:.65rem;color:var(--text-muted);margin-top:.3rem;">
            <span>$0</span><span>$480k quota</span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ── Category / Territory Breakdown ── --}}
<div class="row g-3 mb-3">
  <div class="col-12">
    <div class="ncv-card">
      <div class="ncv-card-header" style="flex-wrap:wrap;gap:.5rem;">
        <h6 class="ncv-card-title"><i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--ncv-blue-500);"></i>Category Tracking</h6>
        <div style="display:flex;gap:.375rem;">
          <button class="ncv-chip active" id="catChipProduct"  onclick="setCatView('product',this)"  style="font-size:.72rem;">By Product Line</button>
          <button class="ncv-chip"        id="catChipTerritory"onclick="setCatView('territory',this)" style="font-size:.72rem;">By Territory</button>
        </div>
      </div>
      <div class="ncv-card-body">

        {{-- Product Line view --}}
        <div id="catViewProduct">
          <div class="row g-2">
            @foreach([
              ['icon'=>'bi-app-indicator','bg'=>'#dbeafe','ic'=>'#2563eb','name'=>'Enterprise License','quota'=>210000,'won'=>148000,'pipe'=>82000,'pct'=>70],
              ['icon'=>'bi-plug-fill',    'bg'=>'#fef3c7','ic'=>'#d97706','name'=>'API & Integrations', 'quota'=>90000, 'won'=>68400, 'pipe'=>31200,'pct'=>76],
              ['icon'=>'bi-person-video3','bg'=>'#d1fae5','ic'=>'#059669','name'=>'Services & Training','quota'=>80000, 'won'=>54250, 'pipe'=>28900,'pct'=>68],
              ['icon'=>'bi-arrow-repeat', 'bg'=>'#ede9fe','ic'=>'#7c3aed','name'=>'Renewals',           'quota'=>60000, 'won'=>41800, 'pipe'=>22400,'pct'=>70],
              ['icon'=>'bi-plus-circle',  'bg'=>'#fce7f3','ic'=>'#db2777','name'=>'Add-Ons',            'quota'=>40000, 'won'=>0,     'pipe'=>34220,'pct'=>0],
            ] as $cat)
            @php $barColor = $cat['pct'] >= 70 ? '#10b981' : ($cat['pct'] >= 50 ? '#f59e0b' : '#ef4444'); @endphp
            <div class="col-12 col-md-6 col-xl-4">
              <div class="fcst-cat-card">
                <div class="fcst-cat-icon" style="background:{{ $cat['bg'] }};color:{{ $cat['ic'] }};">
                  <i class="bi {{ $cat['icon'] }}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:.83rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $cat['name'] }}</div>
                  <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted);margin-bottom:.4rem;">
                    <span>Won: <strong style="color:var(--text-primary);">${{ number_format($cat['won']) }}</strong></span>
                    <span>Target: <strong style="color:var(--text-primary);">${{ number_format($cat['quota']) }}</strong></span>
                  </div>
                  <div class="attain-bar-track" style="height:8px;">
                    <div class="attain-bar-fill" style="width:{{ $cat['pct'] }}%;background:{{ $barColor }};height:100%;border-radius:99px;"></div>
                  </div>
                  <div style="display:flex;justify-content:space-between;font-size:.7rem;margin-top:.3rem;">
                    <span style="color:var(--text-muted);">Pipeline: ${{ number_format($cat['pipe']) }}</span>
                    <span style="font-weight:700;color:{{ $barColor }};">{{ $cat['pct'] }}%</span>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- Territory view (hidden) --}}
        <div id="catViewTerritory" style="display:none;">
          <div class="row g-2">
            @foreach([
              ['icon'=>'bi-geo-alt-fill','bg'=>'#dbeafe','ic'=>'#2563eb','name'=>'North America','quota'=>240000,'won'=>168000,'pipe'=>91000,'pct'=>70],
              ['icon'=>'bi-geo-alt-fill','bg'=>'#d1fae5','ic'=>'#059669','name'=>'Europe (EMEA)',  'quota'=>120000,'won'=>76250, 'pipe'=>58200,'pct'=>64],
              ['icon'=>'bi-geo-alt-fill','bg'=>'#fef3c7','ic'=>'#d97706','name'=>'Asia Pacific',  'quota'=>80000, 'won'=>48200, 'pipe'=>34100,'pct'=>60],
              ['icon'=>'bi-geo-alt-fill','bg'=>'#ede9fe','ic'=>'#7c3aed','name'=>'Latin America', 'quota'=>40000, 'won'=>20000, 'pipe'=>15420,'pct'=>50],
            ] as $ter)
            @php $barColor = $ter['pct'] >= 70 ? '#10b981' : ($ter['pct'] >= 50 ? '#f59e0b' : '#ef4444'); @endphp
            <div class="col-12 col-md-6">
              <div class="fcst-cat-card">
                <div class="fcst-cat-icon" style="background:{{ $ter['bg'] }};color:{{ $ter['ic'] }};">
                  <i class="bi {{ $ter['icon'] }}"></i>
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:.83rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem;">{{ $ter['name'] }}</div>
                  <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted);margin-bottom:.4rem;">
                    <span>Won: <strong style="color:var(--text-primary);">${{ number_format($ter['won']) }}</strong></span>
                    <span>Target: <strong style="color:var(--text-primary);">${{ number_format($ter['quota']) }}</strong></span>
                  </div>
                  <div class="attain-bar-track" style="height:8px;">
                    <div class="attain-bar-fill" style="width:{{ $ter['pct'] }}%;background:{{ $barColor }};height:100%;border-radius:99px;"></div>
                  </div>
                  <div style="display:flex;justify-content:space-between;font-size:.7rem;margin-top:.3rem;">
                    <span style="color:var(--text-muted);">Pipeline: ${{ number_format($ter['pipe']) }}</span>
                    <span style="font-weight:700;color:{{ $barColor }};">{{ $ter['pct'] }}%</span>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ── Open Pipeline Table ── --}}
<div class="row g-3">
  <div class="col-12">
    <div class="ncv-card">
      <div class="ncv-card-header" style="flex-wrap:wrap;gap:.5rem;">
        <h6 class="ncv-card-title"><i class="bi bi-table me-2" style="color:var(--ncv-blue-500);"></i>Open Pipeline Breakdown</h6>
        <div style="display:flex;align-items:center;gap:.5rem;">
          <input type="text" class="ncv-input" placeholder="Search opportunities…"
                 style="width:200px;height:32px;font-size:.78rem;" oninput="filterPipeline(this.value)" />
          <select class="ncv-select" style="width:auto;font-size:.78rem;height:32px;padding:.2rem .625rem;" onchange="filterPipelineStage(this.value)">
            <option value="">All Stages</option>
            <option>Prospecting</option>
            <option>Qualification</option>
            <option>Proposal</option>
            <option>Negotiation</option>
          </select>
        </div>
      </div>
      <div class="ncv-card-body" style="padding:0;">
        <table class="ncv-table" style="margin:0;" id="pipelineTable">
          <thead>
            <tr>
              <th>Opportunity</th>
              <th>Account</th>
              <th>Rep</th>
              <th>Stage</th>
              <th>Forecast Cat.</th>
              <th style="text-align:right;">Amount</th>
              <th style="text-align:right;">Probability</th>
              <th style="text-align:right;">Weighted</th>
              <th>Close Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach([
              ['opp'=>'Enterprise Renewal 2026','acct'=>'Acme Corp',    'rep'=>'SJ','stage'=>'Negotiation',  'cat'=>'Commit',   'amt'=>95000,'prob'=>85,'close'=>'Mar 10'],
              ['opp'=>'Platform Expansion Q1',  'acct'=>'TechStart Inc','rep'=>'MW','stage'=>'Proposal',     'cat'=>'Best Case','amt'=>62000,'prob'=>60,'close'=>'Mar 25'],
              ['opp'=>'API Integration Suite',  'acct'=>'Globex Inc',   'rep'=>'PM','stage'=>'Qualification','cat'=>'Pipeline', 'amt'=>38500,'prob'=>40,'close'=>'Apr 05'],
              ['opp'=>'Professional Services',  'acct'=>'Initech LLC',  'rep'=>'DC','stage'=>'Proposal',     'cat'=>'Commit',   'amt'=>28000,'prob'=>70,'close'=>'Mar 15'],
              ['opp'=>'SMB Starter Bundle',     'acct'=>'Aperture Co',  'rep'=>'LP','stage'=>'Prospecting',  'cat'=>'Pipeline', 'amt'=>12000,'prob'=>20,'close'=>'Apr 20'],
              ['opp'=>'Analytics Add-On',       'acct'=>'Acme Corp',    'rep'=>'SJ','stage'=>'Negotiation',  'cat'=>'Commit',   'amt'=>18500,'prob'=>80,'close'=>'Feb 28'],
            ] as $opp)
            @php
              $weighted = round($opp['amt'] * $opp['prob'] / 100);
              $catColors = ['Commit'=>['#d1fae5','#059669'],'Best Case'=>['#fef3c7','#d97706'],'Pipeline'=>['#ede9fe','#7c3aed'],'Omitted'=>['#fee2e2','#b91c1c']];
              $cc = $catColors[$opp['cat']] ?? ['#f0f4fb','#6b7280'];
              $stageColors = ['Prospecting'=>'#6b7280','Qualification'=>'#f59e0b','Proposal'=>'#2563eb','Negotiation'=>'#10b981'];
              $sc = $stageColors[$opp['stage']] ?? '#6b7280';
            @endphp
            <tr class="pipeline-row" data-stage="{{ $opp['stage'] }}" data-name="{{ strtolower($opp['opp']) }}">
              <td>
                <a href="{{ route('opportunities.show', 1) }}" style="font-weight:700;font-size:.83rem;color:var(--ncv-blue-600);text-decoration:none;">
                  {{ $opp['opp'] }}
                </a>
              </td>
              <td style="font-size:.83rem;color:var(--text-secondary);">{{ $opp['acct'] }}</td>
              <td>
                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-size:.65rem;font-weight:800;display:flex;align-items:center;justify-content:center;">
                  {{ $opp['rep'] }}
                </div>
              </td>
              <td>
                <span style="font-size:.75rem;font-weight:700;color:{{ $sc }};">
                  <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $sc }};margin-right:4px;"></span>
                  {{ $opp['stage'] }}
                </span>
              </td>
              <td>
                <span style="display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:99px;background:{{ $cc[0] }};color:{{ $cc[1] }};font-size:.72rem;font-weight:700;">
                  {{ $opp['cat'] }}
                </span>
              </td>
              <td style="text-align:right;font-size:.875rem;font-weight:700;color:var(--text-primary);">
                ${{ number_format($opp['amt']) }}
              </td>
              <td style="text-align:right;">
                <div class="attain-bar-wrap" style="justify-content:flex-end;">
                  <div class="attain-bar-track" style="width:60px;">
                    <div class="attain-bar-fill" style="width:{{ $opp['prob'] }}%;background:{{ $sc }};"></div>
                  </div>
                  <span style="font-size:.75rem;font-weight:700;color:{{ $sc }};min-width:30px;">{{ $opp['prob'] }}%</span>
                </div>
              </td>
              <td style="text-align:right;font-size:.875rem;font-weight:700;color:#10b981;">
                ${{ number_format($weighted) }}
              </td>
              <td style="font-size:.82rem;color:var(--text-muted);">{{ $opp['close'] }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:#f8faff;">
              <td colspan="5" style="font-weight:800;font-size:.83rem;color:var(--text-primary);">Pipeline Total</td>
              <td style="text-align:right;font-weight:800;font-size:.83rem;">$254,000</td>
              <td></td>
              <td style="text-align:right;font-weight:800;font-size:.83rem;color:#10b981;">$154,360</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // Period toggle
  function setPeriod(period, btn) {
    document.querySelectorAll('.ncv-period-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // In a real app, reload data for the selected period
  }

  // Category view toggle (Product Line / Territory)
  function setCatView(view, btn) {
    document.querySelectorAll('#catViewProduct, #catViewTerritory').forEach(el => el.style.display = 'none');
    document.getElementById('catView' + view.charAt(0).toUpperCase() + view.slice(1)).style.display = '';
    document.querySelectorAll('#catChipProduct, #catChipTerritory').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
  }

  // Pipeline table live search
  function filterPipeline(q) {
    const rows = document.querySelectorAll('.pipeline-row');
    const term = q.toLowerCase();
    rows.forEach(r => {
      r.style.display = r.dataset.name.includes(term) ? '' : 'none';
    });
  }

  // Pipeline table stage filter
  function filterPipelineStage(stage) {
    const rows = document.querySelectorAll('.pipeline-row');
    rows.forEach(r => {
      r.style.display = (!stage || r.dataset.stage === stage) ? '' : 'none';
    });
  }
</script>
@endpush
