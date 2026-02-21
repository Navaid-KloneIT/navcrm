@extends('layouts.app')

@section('title', $survey->name)
@section('page-title', $survey->name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Customer Success</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('success.surveys.index') }}" class="ncv-breadcrumb-item" style="color:inherit;text-decoration:none;">Surveys</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Hero Header --}}
<div class="ncv-card mb-4" style="background:linear-gradient(135deg,#7c3aed,#4c1d95);border:none;color:#fff;overflow:hidden;position:relative;">
  <div style="position:absolute;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.04);top:-80px;right:-60px;"></div>
  <div class="ncv-card-body" style="padding:1.75rem;position:relative;z-index:1;">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-2">
          <code style="font-size:.8rem;color:rgba(255,255,255,.7);background:rgba(255,255,255,.1);padding:.15rem .5rem;border-radius:.375rem;">{{ $survey->survey_number }}</code>
          <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;">{{ $survey->type->label() }}</span>
          <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem;">{{ $survey->status->label() }}</span>
        </div>
        <h1 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .75rem;">
          {{ $survey->name }}
        </h1>
        <div style="display:flex;gap:1.5rem;font-size:.875rem;color:rgba(255,255,255,.75);flex-wrap:wrap;">
          <span><i class="bi bi-people-fill me-1"></i> {{ $survey->responses_count ?? $survey->responses->count() }} Responses</span>
          <span><i class="bi bi-graph-up me-1"></i> Avg Score: <strong style="color:#fff;">{{ $survey->avg_score !== null ? number_format($survey->avg_score, 1) : '--' }}</strong></span>
          <span><i class="bi bi-calendar3 me-1"></i> Created {{ $survey->created_at->format('M j, Y') }}</span>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('success.surveys.edit', $survey) }}"
           class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
          <i class="bi bi-pencil"></i> Edit
        </a>
        <form method="POST" action="{{ route('success.surveys.destroy', $survey) }}"
              onsubmit="return confirm('Delete this survey?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-sm" style="background:rgba(239,68,68,.3);color:#fca5a5;border:1px solid rgba(239,68,68,.3);">
            <i class="bi bi-trash"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">

  {{-- Left Column: Score Data & Responses --}}
  <div class="col-12 col-lg-8">

    {{-- NPS Section --}}
    @if(isset($npsData))
    <div class="row g-3 mb-4">

      {{-- NPS Score --}}
      <div class="col-12 col-md-5">
        <div class="ncv-card h-100">
          <div class="ncv-card-header">
            <h6 class="ncv-card-title"><i class="bi bi-speedometer2 me-2" style="color:#7c3aed;"></i>NPS Score</h6>
          </div>
          <div class="ncv-card-body text-center" style="padding:1.5rem;">
            @php
              $npsScore = (int) ($npsData['score'] ?? 0);
              $npsColor = $npsScore > 0 ? '#10b981' : ($npsScore === 0 ? '#f59e0b' : '#ef4444');
            @endphp
            <div style="font-size:3.5rem;font-weight:900;color:{{ $npsColor }};line-height:1;">
              {{ $npsScore > 0 ? '+' : '' }}{{ $npsScore }}
            </div>
            <div style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem;">Score range: -100 to +100</div>
          </div>
        </div>
      </div>

      {{-- NPS Breakdown --}}
      <div class="col-12 col-md-7">
        <div class="ncv-card h-100">
          <div class="ncv-card-header">
            <h6 class="ncv-card-title"><i class="bi bi-bar-chart-fill me-2" style="color:#7c3aed;"></i>Breakdown</h6>
          </div>
          <div class="ncv-card-body" style="padding:1.5rem;">
            @php
              $promoters  = $npsData['promoters'] ?? 0;
              $passives   = $npsData['passives'] ?? 0;
              $detractors = $npsData['detractors'] ?? 0;
              $npsTotal   = $promoters + $passives + $detractors;
              $pPct = $npsTotal > 0 ? round($promoters / $npsTotal * 100) : 0;
              $paPct = $npsTotal > 0 ? round($passives / $npsTotal * 100) : 0;
              $dPct = $npsTotal > 0 ? round($detractors / $npsTotal * 100) : 0;
            @endphp

            {{-- Stacked Bar --}}
            <div style="display:flex;height:32px;border-radius:.5rem;overflow:hidden;margin-bottom:1rem;">
              @if($pPct > 0)
              <div style="width:{{ $pPct }}%;background:#10b981;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.72rem;font-weight:700;">
                {{ $pPct }}%
              </div>
              @endif
              @if($paPct > 0)
              <div style="width:{{ $paPct }}%;background:#f59e0b;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.72rem;font-weight:700;">
                {{ $paPct }}%
              </div>
              @endif
              @if($dPct > 0)
              <div style="width:{{ $dPct }}%;background:#ef4444;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.72rem;font-weight:700;">
                {{ $dPct }}%
              </div>
              @endif
              @if($npsTotal === 0)
              <div style="width:100%;background:var(--bg-subtle);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:.75rem;">
                No responses yet
              </div>
              @endif
            </div>

            {{-- Legend --}}
            <div class="d-flex justify-content-between" style="font-size:.8rem;">
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:2px;background:#10b981;display:inline-block;"></span>
                <span style="color:var(--text-secondary);">Promoters (9-10)</span>
                <strong style="color:var(--text-primary);">{{ $promoters }}</strong>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:2px;background:#f59e0b;display:inline-block;"></span>
                <span style="color:var(--text-secondary);">Passives (7-8)</span>
                <strong style="color:var(--text-primary);">{{ $passives }}</strong>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:2px;background:#ef4444;display:inline-block;"></span>
                <span style="color:var(--text-secondary);">Detractors (0-6)</span>
                <strong style="color:var(--text-primary);">{{ $detractors }}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    @endif

    {{-- CSAT Section --}}
    @if(isset($csatData))
    <div class="row g-3 mb-4">

      {{-- CSAT Average --}}
      <div class="col-12 col-md-5">
        <div class="ncv-card h-100">
          <div class="ncv-card-header">
            <h6 class="ncv-card-title"><i class="bi bi-star-half me-2" style="color:#f59e0b;"></i>CSAT Average</h6>
          </div>
          <div class="ncv-card-body text-center" style="padding:1.5rem;">
            @php
              $csatAvg = $csatData['average'] ?? 0;
              $csatColor = $csatAvg >= 7 ? '#10b981' : ($csatAvg >= 4 ? '#f59e0b' : '#ef4444');
            @endphp
            <div style="font-size:3.5rem;font-weight:900;color:{{ $csatColor }};line-height:1;">
              {{ number_format($csatAvg, 1) }}
            </div>
            <div style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem;">Score range: 0 to 10</div>
          </div>
        </div>
      </div>

      {{-- CSAT Distribution --}}
      <div class="col-12 col-md-7">
        <div class="ncv-card h-100">
          <div class="ncv-card-header">
            <h6 class="ncv-card-title"><i class="bi bi-bar-chart-fill me-2" style="color:#f59e0b;"></i>Score Distribution</h6>
          </div>
          <div class="ncv-card-body" style="padding:1.5rem;">
            <canvas id="csatDistributionChart" height="150"></canvas>
          </div>
        </div>
      </div>

    </div>
    @endif

    {{-- Responses Table --}}
    <div class="ncv-card">
      <div class="ncv-card-header d-flex align-items-center justify-content-between">
        <h6 class="ncv-card-title mb-0"><i class="bi bi-chat-square-text me-2" style="color:var(--ncv-blue-500);"></i>Responses</h6>
        <span class="ncv-badge ncv-badge-muted">{{ $responses->total() }} total</span>
      </div>
      <div class="ncv-card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:.875rem;">
            <thead>
              <tr style="border-bottom:1px solid var(--border-color);">
                <th style="padding:.75rem 1.25rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Contact</th>
                <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Account</th>
                <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Score</th>
                <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Comment</th>
                <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($responses as $response)
              @php
                $score = $response->score;
                if(isset($npsData)) {
                  $scoreColor = $score >= 9 ? '#10b981' : ($score >= 7 ? '#f59e0b' : '#ef4444');
                } else {
                  $scoreColor = $score >= 7 ? '#10b981' : ($score >= 4 ? '#f59e0b' : '#ef4444');
                }
              @endphp
              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:.75rem 1.25rem;color:var(--text-secondary);">
                  {{ $response->contact?->full_name ?? 'Anonymous' }}
                </td>
                <td style="padding:.75rem 1rem;color:var(--text-muted);">
                  {{ $response->contact?->account?->name ?? '--' }}
                </td>
                <td style="padding:.75rem 1rem;">
                  <span class="badge" style="background:{{ $scoreColor }}18;color:{{ $scoreColor }};font-weight:700;font-size:.8rem;padding:.35rem .6rem;">
                    {{ $score }}
                  </span>
                </td>
                <td style="padding:.75rem 1rem;color:var(--text-secondary);max-width:300px;" class="text-truncate">
                  {{ $response->comment ?? '--' }}
                </td>
                <td style="padding:.75rem 1rem;color:var(--text-muted);font-size:.8rem;">
                  {{ $response->created_at->format('M j, Y') }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" style="padding:3rem;text-align:center;color:var(--text-muted);">
                  <i class="bi bi-chat-square-text" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
                  No responses yet. Share the survey link to start collecting feedback.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($responses->hasPages())
        <div class="d-flex align-items-center justify-content-between px-4 py-3"
             style="border-top:1px solid var(--border-color);">
          <span style="color:var(--text-muted);font-size:.875rem;">
            Showing {{ $responses->firstItem() }}--{{ $responses->lastItem() }} of {{ $responses->total() }}
          </span>
          {{ $responses->links('pagination::bootstrap-5') }}
        </div>
        @endif
      </div>
    </div>

  </div>

  {{-- Right Column: Public Link & Info --}}
  <div class="col-12 col-lg-4">

    {{-- Public Survey Link --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-link-45deg me-2" style="color:var(--ncv-blue-500);"></i>Public Survey Link</h6>
      </div>
      <div class="ncv-card-body">
        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">
          Share this link with customers to collect responses.
        </p>
        <div class="d-flex gap-2">
          <input type="text" class="ncv-input flex-fill" id="publicUrl" value="{{ $publicUrl }}" readonly
                 style="font-size:.8rem;background:var(--bg-subtle);cursor:text;" />
          <button type="button" class="ncv-btn ncv-btn-primary ncv-btn-sm" onclick="copyLink()" id="copyBtn" title="Copy link">
            <i class="bi bi-clipboard"></i> Copy
          </button>
        </div>
      </div>
    </div>

    {{-- Survey Info --}}
    <div class="ncv-card mb-4">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Survey Info</h6>
      </div>
      <div class="ncv-card-body">
        <dl class="mb-0" style="font-size:.875rem;">
          <dt class="text-muted fw-normal mb-1">Type</dt>
          <dd class="mb-3">
            <span class="ncv-badge ncv-badge-{{ $survey->type->color() }}">{{ $survey->type->label() }}</span>
          </dd>

          <dt class="text-muted fw-normal mb-1">Status</dt>
          <dd class="mb-3">
            <span class="ncv-badge ncv-badge-{{ $survey->status->color() }}">{{ $survey->status->label() }}</span>
          </dd>

          @if($survey->account)
          <dt class="text-muted fw-normal mb-1">Account</dt>
          <dd class="mb-3">
            <a href="{{ route('accounts.show', $survey->account) }}" class="text-decoration-none">
              {{ $survey->account->name }}
            </a>
          </dd>
          @endif

          @if($survey->ticket)
          <dt class="text-muted fw-normal mb-1">Ticket</dt>
          <dd class="mb-3">
            <a href="{{ route('support.tickets.show', $survey->ticket) }}" class="text-decoration-none">
              {{ $survey->ticket->ticket_number }}
            </a>
          </dd>
          @endif

          <dt class="text-muted fw-normal mb-1">Created</dt>
          <dd class="mb-3">{{ $survey->created_at->format('M j, Y g:i A') }}</dd>

          <dt class="text-muted fw-normal mb-1">Last Updated</dt>
          <dd class="mb-0">{{ $survey->updated_at->format('M j, Y g:i A') }}</dd>
        </dl>
      </div>
    </div>

    @if($survey->description)
    {{-- Description --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-text-paragraph me-2" style="color:var(--ncv-blue-500);"></i>Description</h6>
      </div>
      <div class="ncv-card-body">
        <p class="mb-0" style="color:var(--text-secondary);font-size:.875rem;white-space:pre-wrap;">{{ $survey->description }}</p>
      </div>
    </div>
    @endif

  </div>

</div>

@endsection

@push('scripts')
<script>
function copyLink() {
  const url = document.getElementById('publicUrl').value;
  navigator.clipboard.writeText(url).then(function() {
    const btn = document.getElementById('copyBtn');
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
    setTimeout(function() {
      btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
    }, 2000);
  });
}

@if(isset($csatData))
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('csatDistributionChart').getContext('2d');
  const distribution = @json($csatData['distribution'] ?? []);

  const labels = [];
  const data = [];
  const bgColors = [];

  for (let i = 1; i <= 10; i++) {
    labels.push(i.toString());
    data.push(distribution[i] ?? 0);
    if (i >= 9) bgColors.push('#10b981');
    else if (i >= 7) bgColors.push('#34d399');
    else if (i >= 4) bgColors.push('#f59e0b');
    else bgColors.push('#ef4444');
  }

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Responses',
        data: data,
        backgroundColor: bgColors,
        borderRadius: 4,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            color: 'var(--text-muted)',
            font: { size: 11 }
          },
          grid: { color: 'rgba(0,0,0,.06)' }
        },
        x: {
          ticks: {
            color: 'var(--text-muted)',
            font: { size: 11 }
          },
          grid: { display: false }
        }
      }
    }
  });
});
@endif
</script>
@endpush
