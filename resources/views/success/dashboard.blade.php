@extends('layouts.app')

@section('title', 'Customer Success Dashboard')
@section('page-title', 'Customer Success Dashboard')

@push('styles')
<style>
  .cs-hero {
    background: linear-gradient(135deg, #0d6efd 0%, #0143a3 100%);
    border-radius: var(--card-radius);
    padding: 1.75rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
  }
  .cs-hero::before {
    content: '';
    position: absolute;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    top: -80px; right: -60px;
  }
  .cs-kpi {
    text-align: center;
    padding: .75rem;
  }
  .cs-kpi-value {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -.03em;
  }
  .cs-kpi-label {
    font-size: .72rem;
    color: rgba(255,255,255,.6);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-top: .125rem;
  }
</style>
@endpush

@section('content')

{{-- Hero KPIs --}}
<div class="cs-hero">
  <h4 style="font-weight:800;letter-spacing:-.02em;margin-bottom:1.25rem;position:relative;z-index:1;">
    <i class="bi bi-activity me-2"></i>Customer Success Overview
  </h4>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:1rem;position:relative;z-index:1;">
    <div class="cs-kpi">
      <div class="cs-kpi-value">{{ $activeOnboarding }}</div>
      <div class="cs-kpi-label">Active Onboarding</div>
    </div>
    <div class="cs-kpi">
      <div class="cs-kpi-value" style="color:{{ $avgHealth !== null && $avgHealth >= 70 ? '#86efac' : ($avgHealth !== null && $avgHealth >= 40 ? '#fde68a' : '#fca5a5') }};">
        {{ $avgHealth ?? '—' }}
      </div>
      <div class="cs-kpi-label">Avg Health Score</div>
    </div>
    <div class="cs-kpi">
      <div class="cs-kpi-value" style="color:{{ $npsScore !== null && $npsScore > 0 ? '#86efac' : ($npsScore !== null && $npsScore == 0 ? '#fde68a' : '#fca5a5') }};">
        {{ $npsScore !== null ? ($npsScore > 0 ? '+' : '') . $npsScore : '—' }}
      </div>
      <div class="cs-kpi-label">NPS Score</div>
    </div>
    <div class="cs-kpi">
      <div class="cs-kpi-value">{{ $csatAvg ?? '—' }}</div>
      <div class="cs-kpi-label">Avg CSAT</div>
    </div>
  </div>
</div>

<div class="row g-3">

  {{-- At-Risk Accounts --}}
  <div class="col-12 col-lg-6">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-exclamation-triangle me-2" style="color:#ef4444;"></i>At-Risk Accounts</h6>
        <a href="{{ route('success.health-scores.index') }}?health_range=critical" class="ncv-btn ncv-btn-ghost ncv-btn-sm">View All</a>
      </div>
      <div class="ncv-card-body p-0">
        @if($atRiskAccounts->isEmpty())
          <div class="text-center py-4" style="color:var(--text-muted);">
            <i class="bi bi-check-circle" style="font-size:2rem;color:#10b981;"></i>
            <p class="mt-2 mb-0">No at-risk accounts</p>
          </div>
        @else
          <div class="table-responsive">
            <table class="ncv-table mb-0">
              <thead>
                <tr>
                  <th>Account</th>
                  <th>Score</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($atRiskAccounts as $acct)
                  @php $hs = $acct->latestHealthScore; @endphp
                  <tr>
                    <td>
                      <a href="{{ route('success.health-scores.show', $acct) }}" style="color:var(--text-primary);text-decoration:none;font-weight:600;">
                        {{ $acct->name }}
                      </a>
                    </td>
                    <td>
                      <span style="font-weight:700;color:{{ $hs && $hs->overall_score < 40 ? '#ef4444' : '#f59e0b' }};">
                        {{ $hs?->overall_score ?? '—' }}
                      </span>
                    </td>
                    <td>
                      <span class="ncv-badge bg-{{ $hs?->health_color ?? 'secondary' }}">{{ $hs?->health_label ?? 'N/A' }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Active Onboarding Pipelines --}}
  <div class="col-12 col-lg-6">
    <div class="ncv-card h-100">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-clipboard-check me-2" style="color:#0d6efd;"></i>Active Onboarding</h6>
        <a href="{{ route('success.onboarding.index') }}?status=in_progress" class="ncv-btn ncv-btn-ghost ncv-btn-sm">View All</a>
      </div>
      <div class="ncv-card-body p-0">
        @if($activePipelines->isEmpty())
          <div class="text-center py-4" style="color:var(--text-muted);">
            <i class="bi bi-clipboard-check" style="font-size:2rem;"></i>
            <p class="mt-2 mb-0">No active onboarding pipelines</p>
          </div>
        @else
          <div class="table-responsive">
            <table class="ncv-table mb-0">
              <thead>
                <tr>
                  <th>Pipeline</th>
                  <th>Account</th>
                  <th>Progress</th>
                  <th>Due</th>
                </tr>
              </thead>
              <tbody>
                @foreach($activePipelines as $p)
                  <tr>
                    <td>
                      <a href="{{ route('success.onboarding.show', $p) }}" style="color:var(--text-primary);text-decoration:none;font-weight:600;">
                        {{ Str::limit($p->name, 30) }}
                      </a>
                    </td>
                    <td style="color:var(--text-secondary);">{{ $p->account?->name }}</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div style="flex:1;height:6px;background:var(--border-color);border-radius:3px;overflow:hidden;">
                          <div style="width:{{ $p->progress }}%;height:100%;background:#0d6efd;border-radius:3px;"></div>
                        </div>
                        <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">{{ $p->progress }}%</span>
                      </div>
                    </td>
                    <td style="font-size:.8rem;color:{{ $p->due_date && $p->due_date->isPast() ? '#ef4444' : 'var(--text-muted)' }};">
                      {{ $p->due_date?->format('M d, Y') ?? '—' }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Recent Survey Responses --}}
  <div class="col-12">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-chat-square-text me-2" style="color:#7c3aed;"></i>Recent Survey Responses</h6>
        <a href="{{ route('success.surveys.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">View All Surveys</a>
      </div>
      <div class="ncv-card-body p-0">
        @if($recentResponses->isEmpty())
          <div class="text-center py-4" style="color:var(--text-muted);">
            <i class="bi bi-chat-square-text" style="font-size:2rem;"></i>
            <p class="mt-2 mb-0">No survey responses yet</p>
          </div>
        @else
          <div class="table-responsive">
            <table class="ncv-table mb-0">
              <thead>
                <tr>
                  <th>Survey</th>
                  <th>Type</th>
                  <th>Contact</th>
                  <th>Score</th>
                  <th>Comment</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentResponses as $r)
                  <tr>
                    <td>
                      <a href="{{ route('success.surveys.show', $r->survey) }}" style="color:var(--text-primary);text-decoration:none;font-weight:600;">
                        {{ Str::limit($r->survey?->name, 25) }}
                      </a>
                    </td>
                    <td>
                      <span class="ncv-badge bg-{{ $r->survey?->type?->color() ?? 'secondary' }}">
                        {{ $r->survey?->type?->label() ?? '—' }}
                      </span>
                    </td>
                    <td style="color:var(--text-secondary);">
                      {{ $r->contact?->full_name ?? 'Anonymous' }}
                    </td>
                    <td>
                      <span class="ncv-badge bg-{{ $r->score >= 9 ? 'success' : ($r->score >= 7 ? 'warning' : 'danger') }}">
                        {{ $r->score }}/10
                      </span>
                    </td>
                    <td style="font-size:.8rem;color:var(--text-muted);max-width:200px;" class="text-truncate">
                      {{ $r->comment ?? '—' }}
                    </td>
                    <td style="font-size:.8rem;color:var(--text-muted);">
                      {{ $r->responded_at?->format('M d, Y') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
