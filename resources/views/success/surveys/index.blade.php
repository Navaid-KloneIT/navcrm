@extends('layouts.app')

@section('title', 'Surveys')
@section('page-title', 'Surveys')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Customer Success</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Surveys</h1>
    <p class="mb-0" style="color:var(--text-muted); font-size:.875rem;">Measure customer satisfaction and loyalty with NPS &amp; CSAT surveys.</p>
  </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
  @foreach([
    ['label' => 'Total Surveys',  'value' => $stats['total'] ?? 0,                                           'icon' => 'bi-clipboard2-data', 'color' => '#6366f1'],
    ['label' => 'Active Surveys', 'value' => $stats['active'] ?? 0,                                          'icon' => 'bi-broadcast',       'color' => '#10b981'],
    ['label' => 'Avg NPS Score',  'value' => isset($stats['avg_nps']) ? (int) $stats['avg_nps'] : '--',      'icon' => 'bi-speedometer2',    'color' => '#3b82f6'],
    ['label' => 'Avg CSAT Score', 'value' => isset($stats['avg_csat']) ? number_format($stats['avg_csat'], 1) : '--', 'icon' => 'bi-star-half', 'color' => '#f59e0b'],
  ] as $kpi)
  <div class="col-6 col-md-3">
    <div class="ncv-card h-100">
      <div class="ncv-card-body" style="padding:1rem 1.25rem;">
        <div class="d-flex align-items-center gap-3">
          <div style="width:40px;height:40px;border-radius:.625rem;background:{{ $kpi['color'] }}18;color:{{ $kpi['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
            <i class="bi {{ $kpi['icon'] }}"></i>
          </div>
          <div>
            <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);line-height:1;">{{ $kpi['value'] }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem;">{{ $kpi['label'] }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Toolbar --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" action="{{ route('success.surveys.index') }}" class="d-flex gap-2 flex-wrap flex-grow-1">
        <div class="ncv-input-group" style="max-width:260px;flex:1;">
          <i class="bi bi-search ncv-input-icon"></i>
          <input type="text" name="search" value="{{ request('search') }}" class="ncv-input ncv-input-search" placeholder="Search surveys...">
        </div>
        <select name="type" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Types</option>
          @foreach($types as $t)
            <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
          @endforeach
        </select>
        <select name="status" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          @foreach($statuses as $st)
            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>{{ $st->label() }}</option>
          @endforeach
        </select>
        <button type="submit" class="ncv-btn ncv-btn-ghost"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search','type','status']))
          <a href="{{ route('success.surveys.index') }}" class="ncv-btn ncv-btn-ghost">Clear</a>
        @endif
      </form>
      <a href="{{ route('success.surveys.create') }}" class="ncv-btn ncv-btn-primary ms-auto">
        <i class="bi bi-plus-lg"></i> New Survey
      </a>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.875rem;">
        <thead>
          <tr style="border-bottom:1px solid var(--border-color);">
            <th style="padding:.75rem 1.25rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Survey #</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Name</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Type</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Status</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Responses</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Avg Score</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Created</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($surveys as $s)
          <tr style="border-bottom:1px solid var(--border-color);">
            <td style="padding:.75rem 1.25rem;">
              <code style="font-size:.8rem;color:var(--text-secondary);">{{ $s->survey_number }}</code>
            </td>
            <td style="padding:.75rem 1rem;">
              <a href="{{ route('success.surveys.show', $s) }}" style="font-weight:600;color:var(--ncv-blue-500);text-decoration:none;">
                {{ $s->name }}
              </a>
            </td>
            <td style="padding:.75rem 1rem;">
              <span class="ncv-badge ncv-badge-{{ $s->type->color() }}">{{ $s->type->label() }}</span>
            </td>
            <td style="padding:.75rem 1rem;">
              <span class="ncv-badge ncv-badge-{{ $s->status->color() }}">{{ $s->status->label() }}</span>
            </td>
            <td style="padding:.75rem 1rem;color:var(--text-secondary);">
              {{ $s->responses_count ?? $s->responses->count() }}
            </td>
            <td style="padding:.75rem 1rem;color:var(--text-secondary);font-weight:600;">
              {{ $s->avg_score !== null ? number_format($s->avg_score, 1) : '--' }}
            </td>
            <td style="padding:.75rem 1rem;color:var(--text-muted);font-size:.8rem;">
              {{ $s->created_at->format('M j, Y') }}
            </td>
            <td style="padding:.75rem 1rem;text-align:right;">
              <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('success.surveys.show', $s) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('success.surveys.edit', $s) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('success.surveys.destroy', $s) }}"
                      onsubmit="return confirm('Delete survey {{ $s->survey_number }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" style="padding:3rem;text-align:center;color:var(--text-muted);">
              <i class="bi bi-clipboard2-data" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
              No surveys found. <a href="{{ route('success.surveys.create') }}">Create your first survey</a>.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($surveys->hasPages())
    <div class="d-flex align-items-center justify-content-between px-4 py-3"
         style="border-top:1px solid var(--border-color);">
      <span style="color:var(--text-muted);font-size:.875rem;">
        Showing {{ $surveys->firstItem() }}--{{ $surveys->lastItem() }} of {{ $surveys->total() }}
      </span>
      {{ $surveys->links('pagination::bootstrap-5') }}
    </div>
    @endif
  </div>
</div>

@endsection
