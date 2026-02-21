@extends('layouts.app')

@section('title', 'Health Scores')
@section('page-title', 'Health Scores')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .stat-card { border-radius:.75rem; padding:1.25rem; text-align:center; }
  .stat-card .stat-value { font-size:1.6rem; font-weight:800; line-height:1.2; }
  .stat-card .stat-label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-top:.25rem; }
  .score-bar { height:6px; border-radius:3px; background:#e5e7eb; overflow:hidden; }
  .score-bar-fill { height:100%; border-radius:3px; transition:width .3s ease; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Health Scores</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">Account health overview</p>
  </div>
  <form method="POST" action="{{ route('success.health-scores.recalculate-all') }}" class="d-inline">
    @csrf
    <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm"
            onclick="return confirm('Recalculate health scores for all accounts? This may take a moment.')">
      <i class="bi bi-arrow-clockwise me-1"></i> Recalculate All
    </button>
  </form>
</div>

{{-- Stats cards --}}
<div class="row g-3 mb-3">
  {{-- Average Score --}}
  <div class="col-sm-6 col-lg-3">
    @php
      $avg = $stats['avg_score'] ?? 0;
      if ($avg >= 70) { $avgBg = '#f0fdf4'; $avgColor = '#16a34a'; }
      elseif ($avg >= 40) { $avgBg = '#fefce8'; $avgColor = '#ca8a04'; }
      else { $avgBg = '#fef2f2'; $avgColor = '#dc2626'; }
    @endphp
    <div class="stat-card" style="background:{{ $avgBg }};">
      <div class="stat-value" style="color:{{ $avgColor }};">{{ number_format($avg, 0) }}</div>
      <div class="stat-label" style="color:{{ $avgColor }};">Average Score</div>
    </div>
  </div>

  {{-- Healthy --}}
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card" style="background:#f0fdf4;">
      <div class="stat-value" style="color:#16a34a;">{{ $stats['healthy'] }}</div>
      <div class="stat-label" style="color:#16a34a;">Healthy</div>
    </div>
  </div>

  {{-- At Risk --}}
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card" style="background:#fefce8;">
      <div class="stat-value" style="color:#ca8a04;">{{ $stats['at_risk'] }}</div>
      <div class="stat-label" style="color:#ca8a04;">At Risk</div>
    </div>
  </div>

  {{-- Critical --}}
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card" style="background:#fef2f2;">
      <div class="stat-value" style="color:#dc2626;">{{ $stats['critical'] }}</div>
      <div class="stat-label" style="color:#dc2626;">Critical</div>
    </div>
  </div>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('success.health-scores.index') }}" class="filter-bar mb-3">
  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search account name..."
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:200px;">
  </div>

  <select name="health_range" class="ncv-select ncv-select-sm" style="width:170px;">
    <option value="">All Ranges</option>
    <option value="healthy" {{ request('health_range') === 'healthy' ? 'selected' : '' }}>Healthy (70-100)</option>
    <option value="at_risk" {{ request('health_range') === 'at_risk' ? 'selected' : '' }}>At Risk (40-69)</option>
    <option value="critical" {{ request('health_range') === 'critical' ? 'selected' : '' }}>Critical (0-39)</option>
  </select>

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','health_range']))
    <a href="{{ route('success.health-scores.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($accounts->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-heart-pulse" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No accounts found</p>
        <p class="small mb-0">Adjust your filters or recalculate scores.</p>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Account Name</th>
            <th style="min-width:160px;">Overall Score</th>
            <th style="text-align:center;">Login</th>
            <th style="text-align:center;">Ticket</th>
            <th style="text-align:center;">Payment</th>
            <th>Last Calculated</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($accounts as $account)
          @php $score = $account->latestHealthScore; @endphp
          <tr>
            <td>
              <a href="{{ route('success.health-scores.show', $account) }}"
                 class="ncv-table-cell-primary text-decoration-none" style="color:inherit;font-size:.875rem;">
                {{ $account->name }}
              </a>
            </td>

            @if($score)
              @php
                $overall = $score->overall_score;
                if ($overall >= 70) { $barColor = '#16a34a'; $badgeClass = 'success'; }
                elseif ($overall >= 40) { $barColor = '#ca8a04'; $badgeClass = 'warning'; }
                else { $barColor = '#dc2626'; $badgeClass = 'danger'; }
              @endphp
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span style="font-weight:700;font-size:.85rem;color:{{ $barColor }};min-width:28px;">{{ $overall }}</span>
                  <div class="score-bar flex-grow-1">
                    <div class="score-bar-fill" style="width:{{ $overall }}%;background:{{ $barColor }};"></div>
                  </div>
                </div>
              </td>
              <td style="text-align:center;font-size:.82rem;color:var(--text-muted);">{{ $score->login_score }}</td>
              <td style="text-align:center;font-size:.82rem;color:var(--text-muted);">{{ $score->ticket_score }}</td>
              <td style="text-align:center;font-size:.82rem;color:var(--text-muted);">{{ $score->payment_score }}</td>
              <td style="font-size:.82rem;color:var(--text-muted);">{{ $score->calculated_at?->format('M j, Y') ?? '—' }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('success.health-scores.show', $account) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                    <i class="bi bi-eye" style="font-size:.8rem;"></i>
                  </a>
                  <form method="POST" action="{{ route('success.health-scores.recalculate', $account) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Recalculate">
                      <i class="bi bi-arrow-clockwise" style="font-size:.8rem;"></i>
                    </button>
                  </form>
                </div>
              </td>
            @else
              <td colspan="4" style="font-size:.82rem;color:var(--text-muted);font-style:italic;">Not calculated</td>
              <td style="font-size:.82rem;color:var(--text-muted);">—</td>
              <td>
                <form method="POST" action="{{ route('success.health-scores.recalculate', $account) }}" class="d-inline">
                  @csrf
                  <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm" title="Recalculate">
                    <i class="bi bi-arrow-clockwise me-1" style="font-size:.75rem;"></i> Calculate
                  </button>
                </form>
              </td>
            @endif
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($accounts->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-top:1px solid var(--border-color); font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} of {{ $accounts->total() }}
    </span>
    {{ $accounts->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
