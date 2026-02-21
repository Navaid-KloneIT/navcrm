@extends('layouts.app')

@section('title', $account->name . ' — Health Score')
@section('page-title', 'Health Score')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('success.health-scores.index') }}" class="ncv-breadcrumb-item">Health Scores</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .hs-hero {
    background: linear-gradient(135deg, #0d1f4e, #1e3a8f);
    color: #fff;
    border-radius: .75rem;
    padding: 2rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
  }
  .hs-hero-info h2 { font-size: 1.3rem; font-weight: 800; margin: 0; }
  .hs-hero-info p  { font-size: .85rem; opacity: .75; margin: .25rem 0 0; }

  .hs-score-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    flex-shrink: 0;
  }
  .hs-score-circle .score-num { font-size: 2.2rem; font-weight: 900; line-height: 1; }
  .hs-score-circle .score-lbl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-top: .15rem; }

  .breakdown-card { text-align: center; }
  .breakdown-card .breakdown-value { font-size: 1.8rem; font-weight: 800; line-height: 1.2; }
  .breakdown-card .breakdown-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); margin-top: .25rem; }

  .score-bar { height: 8px; border-radius: 4px; background: #e5e7eb; overflow: hidden; margin-top: .6rem; }
  .score-bar-fill { height: 100%; border-radius: 4px; transition: width .3s ease; }

  .factor-detail { font-size: .8rem; color: var(--text-muted); margin-top: .6rem; line-height: 1.5; }
</style>
@endpush

@section('content')

@php
  $latest = $account->latestHealthScore;
  $overall = $latest?->overall_score ?? 0;

  if ($overall >= 70) {
    $circBg   = 'rgba(22,163,74,.15)';
    $circClr  = '#16a34a';
    $circLbl  = 'Healthy';
  } elseif ($overall >= 40) {
    $circBg   = 'rgba(202,138,4,.15)';
    $circClr  = '#ca8a04';
    $circLbl  = 'At Risk';
  } else {
    $circBg   = 'rgba(220,38,38,.15)';
    $circClr  = '#dc2626';
    $circLbl  = 'Critical';
  }
@endphp

{{-- Hero header --}}
<div class="hs-hero">
  <div class="hs-hero-info">
    <h2>{{ $account->name }}</h2>
    <p>
      @if($latest)
        Last calculated {{ $latest->calculated_at?->format('M j, Y \a\t g:i A') }}
      @else
        No health score calculated yet
      @endif
    </p>
    <div class="d-flex gap-2 mt-3">
      <form method="POST" action="{{ route('success.health-scores.recalculate', $account) }}" class="d-inline">
        @csrf
        <button type="submit" class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
          <i class="bi bi-arrow-clockwise me-1"></i> Recalculate
        </button>
      </form>
      <a href="{{ route('success.health-scores.index') }}"
         class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.15);">
        <i class="bi bi-arrow-left me-1"></i> Back to Health Scores
      </a>
    </div>
  </div>

  <div class="hs-score-circle" style="background:{{ $circBg }};">
    <div class="score-num" style="color:{{ $circClr }};">{{ $overall }}</div>
    <div class="score-lbl" style="color:{{ $circClr }};">{{ $circLbl }}</div>
  </div>
</div>

{{-- Score breakdown cards --}}
@if($latest)
<div class="row g-3 mb-4">
  {{-- Engagement Score --}}
  <div class="col-md-4">
    @php
      $ls = $latest->login_score;
      $lsColor = $ls >= 70 ? '#16a34a' : ($ls >= 40 ? '#ca8a04' : '#dc2626');
      $loginDetail = $history->first()?->factors['login']['detail'] ?? null;
    @endphp
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><span class="ncv-card-title"><i class="bi bi-person-check me-1"></i> Engagement Score</span></div>
      <div class="ncv-card-body breakdown-card">
        <div class="breakdown-value" style="color:{{ $lsColor }};">{{ $ls }}</div>
        <div class="breakdown-label">Login Score</div>
        <div class="score-bar">
          <div class="score-bar-fill" style="width:{{ $ls }}%;background:{{ $lsColor }};"></div>
        </div>
        @if($loginDetail)
          <div class="factor-detail">{{ $loginDetail }}</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Ticket Health --}}
  <div class="col-md-4">
    @php
      $ts = $latest->ticket_score;
      $tsColor = $ts >= 70 ? '#16a34a' : ($ts >= 40 ? '#ca8a04' : '#dc2626');
      $ticketDetail = $history->first()?->factors['ticket']['detail'] ?? null;
    @endphp
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><span class="ncv-card-title"><i class="bi bi-ticket-detailed me-1"></i> Ticket Health</span></div>
      <div class="ncv-card-body breakdown-card">
        <div class="breakdown-value" style="color:{{ $tsColor }};">{{ $ts }}</div>
        <div class="breakdown-label">Ticket Score</div>
        <div class="score-bar">
          <div class="score-bar-fill" style="width:{{ $ts }}%;background:{{ $tsColor }};"></div>
        </div>
        @if($ticketDetail)
          <div class="factor-detail">{{ $ticketDetail }}</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Payment Health --}}
  <div class="col-md-4">
    @php
      $ps = $latest->payment_score;
      $psColor = $ps >= 70 ? '#16a34a' : ($ps >= 40 ? '#ca8a04' : '#dc2626');
      $paymentDetail = $history->first()?->factors['payment']['detail'] ?? null;
    @endphp
    <div class="ncv-card h-100">
      <div class="ncv-card-header"><span class="ncv-card-title"><i class="bi bi-credit-card me-1"></i> Payment Health</span></div>
      <div class="ncv-card-body breakdown-card">
        <div class="breakdown-value" style="color:{{ $psColor }};">{{ $ps }}</div>
        <div class="breakdown-label">Payment Score</div>
        <div class="score-bar">
          <div class="score-bar-fill" style="width:{{ $ps }}%;background:{{ $psColor }};"></div>
        </div>
        @if($paymentDetail)
          <div class="factor-detail">{{ $paymentDetail }}</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endif

{{-- Score History Chart --}}
@if($history->count() > 1)
<div class="ncv-card mb-4">
  <div class="ncv-card-header"><span class="ncv-card-title"><i class="bi bi-graph-up me-1"></i> Score History</span></div>
  <div class="ncv-card-body">
    <canvas id="healthScoreChart" height="80"></canvas>
  </div>
</div>
@endif

{{-- Score History Table --}}
@if($history->isNotEmpty())
<div class="ncv-card">
  <div class="ncv-card-header"><span class="ncv-card-title"><i class="bi bi-clock-history me-1"></i> Calculation History</span></div>
  <div class="ncv-card-body p-0">
    <table class="ncv-table">
      <thead>
        <tr>
          <th>Date</th>
          <th style="text-align:center;">Overall</th>
          <th style="text-align:center;">Login</th>
          <th style="text-align:center;">Ticket</th>
          <th style="text-align:center;">Payment</th>
        </tr>
      </thead>
      <tbody>
        @foreach($history as $record)
        @php
          $ro = $record->overall_score;
          if ($ro >= 70) { $roColor = '#16a34a'; }
          elseif ($ro >= 40) { $roColor = '#ca8a04'; }
          else { $roColor = '#dc2626'; }
        @endphp
        <tr>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $record->calculated_at?->format('M j, Y g:i A') }}</td>
          <td style="text-align:center;font-weight:700;color:{{ $roColor }};">{{ $ro }}</td>
          <td style="text-align:center;font-size:.82rem;color:var(--text-muted);">{{ $record->login_score }}</td>
          <td style="text-align:center;font-size:.82rem;color:var(--text-muted);">{{ $record->ticket_score }}</td>
          <td style="text-align:center;font-size:.82rem;color:var(--text-muted);">{{ $record->payment_score }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection

@push('scripts')
@if($history->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('healthScoreChart').getContext('2d');

    const data = @json($history->reverse()->values()->map(fn ($h) => [
        'date'    => $h->calculated_at?->format('M j'),
        'overall' => $h->overall_score,
        'login'   => $h->login_score,
        'ticket'  => $h->ticket_score,
        'payment' => $h->payment_score,
    ]));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [
                {
                    label: 'Overall',
                    data: data.map(d => d.overall),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,.08)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    fill: true,
                    tension: 0.3,
                },
                {
                    label: 'Login',
                    data: data.map(d => d.login),
                    borderColor: '#8b5cf6',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    pointBackgroundColor: '#8b5cf6',
                    borderDash: [4, 3],
                    fill: false,
                    tension: 0.3,
                },
                {
                    label: 'Ticket',
                    data: data.map(d => d.ticket),
                    borderColor: '#f59e0b',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    pointBackgroundColor: '#f59e0b',
                    borderDash: [4, 3],
                    fill: false,
                    tension: 0.3,
                },
                {
                    label: 'Payment',
                    data: data.map(d => d.payment),
                    borderColor: '#10b981',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    pointBackgroundColor: '#10b981',
                    borderDash: [4, 3],
                    fill: false,
                    tension: 0.3,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 12 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,.9)',
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { stepSize: 20, font: { size: 11 }, color: '#94a3b8' },
                    grid: { color: 'rgba(148,163,184,.12)' },
                    border: { display: false },
                },
                x: {
                    ticks: { font: { size: 11 }, color: '#94a3b8' },
                    grid: { display: false },
                    border: { display: false },
                }
            }
        }
    });
});
</script>
@endif
@endpush
