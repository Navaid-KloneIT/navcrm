@extends('layouts.app')

@section('title', 'Pipeline')
@section('page-title', 'Pipeline')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .pipeline-header {
    background: linear-gradient(135deg, #0d1f4e, #1e3a8f);
    border-radius: var(--card-radius);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    color: #fff;
  }
  .ncv-kanban-col { min-width: 240px; max-width: 240px; }
  .opp-value { font-size: .9rem; font-weight: 800; color: var(--text-primary); }
  .opp-prob  { font-size: .72rem; color: var(--text-muted); margin-top: 1px; }
  .stage-total { font-size: .75rem; font-weight: 700; color: var(--text-muted); }
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
</style>
@endpush

@section('content')

{{-- Pipeline Header --}}
<div class="pipeline-header">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h1 style="font-size:1.3rem;font-weight:800;margin:0;letter-spacing:-.03em;">Sales Pipeline</h1>
      <p style="font-size:.8rem;color:rgba(255,255,255,.65);margin:.25rem 0 0;">Manage and track your deals through each stage.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <div style="display:flex;gap:1.5rem;">
        <div style="text-align:center;">
          <div style="font-size:1.25rem;font-weight:800;color:#fff;">
            ${{ number_format($pipelineStats['total_value'] / 1000, 1) }}k
          </div>
          <div style="font-size:.68rem;color:rgba(255,255,255,.6);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Total Pipeline</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.25rem;font-weight:800;color:#fff;">
            ${{ number_format($pipelineStats['closing_month'] / 1000, 1) }}k
          </div>
          <div style="font-size:.68rem;color:rgba(255,255,255,.6);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Closing This Month</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.25rem;font-weight:800;color:#fff;">{{ $pipelineStats['total_count'] }}</div>
          <div style="font-size:.68rem;color:rgba(255,255,255,.6);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Open Deals</div>
        </div>
      </div>
      <a href="{{ route('opportunities.create') }}" class="ncv-btn ncv-btn-sm ms-3"
         style="background:#fff;color:#1d4ed8;border:none;font-weight:700;">
        <i class="bi bi-plus-lg"></i> Add Deal
      </a>
    </div>
  </div>
</div>

{{-- Filter bar + view toggle --}}
<form method="GET" action="{{ route('opportunities.index') }}" class="filter-bar mb-3" id="filterForm">
  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search deals…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:190px;">
  </div>

  <select name="stage" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Stages</option>
    @foreach($stages as $s)
      <option value="{{ $s->id }}" {{ request('stage') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
    @endforeach
  </select>

  <select name="owner_id" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Owners</option>
    @foreach($owners as $u)
      <option value="{{ $u->id }}" {{ request('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>

  <select name="account_id" class="ncv-select ncv-select-sm" style="width:150px;">
    <option value="">All Accounts</option>
    @foreach($accounts as $acc)
      <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
    @endforeach
  </select>

  <input type="number" name="amount_min" value="{{ request('amount_min') }}"
         placeholder="Min $" class="ncv-input ncv-input-sm" style="width:90px;">
  <input type="number" name="amount_max" value="{{ request('amount_max') }}"
         placeholder="Max $" class="ncv-input ncv-input-sm" style="width:90px;">

  <input type="date" name="close_from" value="{{ request('close_from') }}"
         class="ncv-input ncv-input-sm" title="Close date from">
  <input type="date" name="close_to" value="{{ request('close_to') }}"
         class="ncv-input ncv-input-sm" title="Close date to">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','stage','owner_id','account_id','amount_min','amount_max','close_from','close_to']))
    <a href="{{ route('opportunities.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif

  <div class="d-flex gap-2 ms-auto">
    <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="pipelineView('kanban')" id="btnKanban">
      <i class="bi bi-kanban"></i> Kanban
    </button>
    <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="pipelineView('list')" id="btnList">
      <i class="bi bi-table"></i> List
    </button>
  </div>
</form>

{{-- KANBAN BOARD --}}
<div id="pipeline-kanban">
  <div class="ncv-kanban">
    @php
      $stageColors = collect($stages)->pluck('color', 'id')->toArray();
    @endphp
    @foreach($stages as $stage)
    @php
      $stageDeals = $kanbanData[$stage->id] ?? collect();
      $stageTotal = $stageDeals->sum('amount');
    @endphp
    <div class="ncv-kanban-col">
      <div class="ncv-kanban-col-header">
        <div>
          <div class="ncv-kanban-col-title" style="color:{{ $stage->color ?? '#2563eb' }};">{{ $stage->name }}</div>
          <div class="stage-total">${{ number_format($stageTotal) }}</div>
        </div>
        <span class="ncv-kanban-count">{{ $stageDeals->count() }}</span>
      </div>

      @forelse($stageDeals as $deal)
      <div class="ncv-kanban-card"
           onclick="window.location='{{ route('opportunities.show', $deal) }}'">
        <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.5rem;">
          <div class="ncv-table-avatar"
               style="width:28px;height:28px;background:{{ $stage->color ?? '#2563eb' }}18;color:{{ $stage->color ?? '#2563eb' }};font-size:.65rem;border-radius:.4rem;flex-shrink:0;">
            {{ strtoupper(substr($deal->account?->name ?? $deal->name, 0, 2)) }}
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:.82rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $deal->name }}
            </div>
            <div style="font-size:.72rem;color:var(--text-muted);">{{ $deal->account?->name ?? '—' }}</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div>
            <div class="opp-value">${{ number_format($deal->amount ?? 0) }}</div>
            <div class="opp-prob">
              {{ $deal->probability ?? 0 }}%
              @if($deal->close_date) &middot; {{ $deal->close_date->format('M j') }} @endif
            </div>
          </div>
          @if($deal->owner)
          <div style="width:30px;height:30px;border-radius:50%;background:var(--ncv-blue-50);color:var(--text-muted);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:800;flex-shrink:0;"
               title="{{ $deal->owner->name }}">
            {{ strtoupper(substr($deal->owner->name, 0, 2)) }}
          </div>
          @endif
        </div>
        <div class="ncv-progress-bar mt-2" style="height:3px;">
          <div class="ncv-progress-fill" style="width:{{ $deal->probability ?? 0 }}%;background:{{ $stage->color ?? '#2563eb' }};"></div>
        </div>
      </div>
      @empty
      <p style="font-size:.78rem;color:var(--text-muted);text-align:center;padding:.75rem 0;">No deals</p>
      @endforelse

      <a href="{{ route('opportunities.create') }}"
         class="ncv-btn ncv-btn-ghost ncv-btn-sm w-100 mt-1"
         style="border:1.5px dashed var(--border-color);border-radius:.625rem;justify-content:center;font-size:.78rem;"
         onclick="event.stopPropagation()">
        <i class="bi bi-plus-lg"></i> Add Deal
      </a>
    </div>
    @endforeach
  </div>
</div>

{{-- LIST VIEW --}}
<div id="pipeline-list" style="display:none;">
  <div class="ncv-card">
    <div class="ncv-card-body p-0">
      @if($opportunities->isEmpty())
        <div class="text-center py-5" style="color:var(--text-muted);">
          <i class="bi bi-briefcase" style="font-size:2.5rem;opacity:.4;"></i>
          <p class="mt-3 mb-1 fw-medium">No opportunities found</p>
          <p class="small mb-3">Try adjusting your filters or add a new deal.</p>
          <a href="{{ route('opportunities.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> Add Deal
          </a>
        </div>
      @else
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Deal</th>
              <th>Account</th>
              <th>Stage</th>
              <th style="text-align:right;">Value</th>
              <th>Probability</th>
              <th>Close Date</th>
              <th>Owner</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($opportunities as $opp)
            <tr>
              <td>
                <a href="{{ route('opportunities.show', $opp) }}"
                   class="ncv-table-cell-primary text-decoration-none" style="color:inherit; font-size:.875rem;">
                  {{ $opp->name }}
                </a>
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $opp->account?->name ?? '—' }}</td>
              <td>
                @if($opp->stage)
                  <span class="ncv-badge" style="background:{{ $opp->stage->color ?? '#e2e8f0' }}22; color:{{ $opp->stage->color ?? '#64748b' }};">
                    {{ $opp->stage->name }}
                  </span>
                @else
                  <span style="color:var(--text-muted);">—</span>
                @endif
              </td>
              <td style="text-align:right; font-weight:700;">${{ number_format($opp->amount ?? 0) }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <div class="ncv-progress-bar" style="width:60px;">
                    <div class="ncv-progress-fill" style="width:{{ $opp->probability ?? 0 }}%;"></div>
                  </div>
                  <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">{{ $opp->probability ?? 0 }}%</span>
                </div>
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">
                {{ $opp->close_date?->format('M j, Y') ?? '—' }}
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $opp->owner?->name ?? '—' }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('opportunities.show', $opp) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
                  <a href="{{ route('opportunities.edit', $opp) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    @if($opportunities->hasPages())
    <div class="d-flex align-items-center justify-content-between px-3 py-2"
         style="border-top:1px solid var(--border-color); font-size:.82rem;">
      <span style="color:var(--text-muted);">
        Showing {{ $opportunities->firstItem() }}–{{ $opportunities->lastItem() }} of {{ $opportunities->total() }}
      </span>
      {{ $opportunities->links('pagination::bootstrap-5') }}
    </div>
    @endif
  </div>
</div>

@endsection

@push('scripts')
<script>
function pipelineView(v) {
  document.getElementById('pipeline-kanban').style.display = v === 'kanban' ? 'block' : 'none';
  document.getElementById('pipeline-list').style.display   = v === 'list'   ? 'block' : 'none';
  document.getElementById('btnKanban').style.background = v === 'kanban' ? 'var(--ncv-blue-50)'  : '';
  document.getElementById('btnKanban').style.color      = v === 'kanban' ? 'var(--ncv-blue-600)' : '';
  document.getElementById('btnList').style.background   = v === 'list'   ? 'var(--ncv-blue-50)'  : '';
  document.getElementById('btnList').style.color        = v === 'list'   ? 'var(--ncv-blue-600)' : '';
  localStorage.setItem('ncv_pipeline_view', v);
}
const sv = localStorage.getItem('ncv_pipeline_view') || 'kanban';
pipelineView(sv);
</script>
@endpush
