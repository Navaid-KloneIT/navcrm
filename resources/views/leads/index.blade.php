@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'Leads')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .score-hot    { background:#fef2f2; color:#ef4444; }
  .score-warm   { background:#fff7ed; color:#f59e0b; }
  .score-cold   { background:#eff6ff; color:#2563eb; }
  .status-new       { background:#f0fdf4; color:#10b981; }
  .status-contacted { background:#eff6ff; color:#2563eb; }
  .status-qualified { background:#f5f3ff; color:#7c3aed; }
  .status-converted { background:#f0fdf4; color:#059669; }
  .status-recycled  { background:#f1f5f9; color:#64748b; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Leads</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">{{ $leads->total() }} lead{{ $leads->total() !== 1 ? 's' : '' }} found</p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="toggleLeadView('table')" id="btnLeadTable">
      <i class="bi bi-table"></i>
    </button>
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="toggleLeadView('kanban')" id="btnLeadKanban">
      <i class="bi bi-kanban"></i>
    </button>
    <a href="{{ route('leads.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Lead
    </a>
  </div>
</div>

{{-- Status count chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  @php
    $statusLabels = ['new'=>'New','contacted'=>'Contacted','qualified'=>'Qualified','converted'=>'Converted','recycled'=>'Recycled'];
  @endphp
  @foreach($statusLabels as $key => $label)
  <a href="{{ route('leads.index', array_merge(request()->except('status','page'), ['status' => $key])) }}"
     class="ncv-chip {{ request('status') === $key ? 'active' : '' }}"
     style="font-size:.78rem;">
    {{ $label }}
    <span class="ms-1" style="font-weight:700;">{{ $statusCounts[$key] ?? 0 }}</span>
  </a>
  @endforeach
  @if(request('status'))
    <a href="{{ route('leads.index', request()->except('status','page')) }}" class="ncv-chip" style="font-size:.78rem;">All</a>
  @endif
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('leads.index') }}" class="filter-bar mb-3">
  {{-- Preserve status from chips if set --}}
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search name, email, company…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:210px;">
  </div>

  <select name="score" class="ncv-select ncv-select-sm" style="width:120px;">
    <option value="">All Scores</option>
    @foreach(\App\Enums\LeadScore::cases() as $s)
      <option value="{{ $s->value }}" {{ request('score') === $s->value ? 'selected' : '' }}>
        {{ ucfirst($s->value) }}
      </option>
    @endforeach
  </select>

  <select name="source" class="ncv-select ncv-select-sm" style="width:130px;">
    <option value="">All Sources</option>
    @foreach($sources as $src)
      <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
    @endforeach
  </select>

  <select name="owner_id" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Owners</option>
    @foreach($owners as $u)
      <option value="{{ $u->id }}" {{ request('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>

  <input type="text" name="tag" value="{{ request('tag') }}"
         placeholder="Tag…" class="ncv-input ncv-input-sm" style="width:100px;">

  <input type="date" name="date_from" value="{{ request('date_from') }}"
         class="ncv-input ncv-input-sm" title="Created from">
  <input type="date" name="date_to" value="{{ request('date_to') }}"
         class="ncv-input ncv-input-sm" title="Created to">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','score','source','owner_id','tag','date_from','date_to','status']))
    <a href="{{ route('leads.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- TABLE VIEW --}}
<div id="lead-table">
  <div class="ncv-card">
    <div class="ncv-card-body p-0">
      @if($leads->isEmpty())
        <div class="text-center py-5" style="color:var(--text-muted);">
          <i class="bi bi-person-lines-fill" style="font-size:2.5rem;opacity:.4;"></i>
          <p class="mt-3 mb-1 fw-medium">No leads found</p>
          <p class="small mb-3">Try adjusting your filters or add a new lead.</p>
          <a href="{{ route('leads.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> New Lead
          </a>
        </div>
      @else
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Company</th>
              <th>Status</th>
              <th>Score</th>
              <th>Source</th>
              <th>Owner</th>
              <th>Created</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($leads as $lead)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="ncv-table-avatar">
                    {{ strtoupper(substr($lead->first_name, 0, 1) . substr($lead->last_name, 0, 1)) }}
                  </div>
                  <div>
                    <a href="{{ route('leads.show', $lead) }}"
                       class="ncv-table-cell-primary text-decoration-none" style="color:inherit;">
                      {{ $lead->full_name }}
                    </a>
                    @if($lead->email)
                      <div style="font-size:.75rem; color:var(--text-muted);">{{ $lead->email }}</div>
                    @endif
                  </div>
                </div>
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $lead->company_name ?? '—' }}</td>
              <td>
                <span class="ncv-badge status-{{ $lead->status->value }}">
                  {{ $lead->status->label() }}
                </span>
              </td>
              <td>
                @php
                  $scoreClasses = ['hot'=>'score-hot','warm'=>'score-warm','cold'=>'score-cold'];
                  $scoreIcons   = ['hot'=>'🔥','warm'=>'🌡️','cold'=>'❄️'];
                @endphp
                <span class="ncv-badge {{ $scoreClasses[$lead->score->value] ?? '' }}">
                  {{ $scoreIcons[$lead->score->value] ?? '' }} {{ ucfirst($lead->score->value) }}
                </span>
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $lead->source ? ucfirst($lead->source) : '—' }}</td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $lead->owner?->name ?? '—' }}</td>
              <td style="font-size:.78rem; color:var(--text-muted);">{{ $lead->created_at->format('M j, Y') }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('leads.show', $lead) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                    <i class="bi bi-eye" style="font-size:.8rem;"></i>
                  </a>
                  <a href="{{ route('leads.edit', $lead) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
                    <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                  </a>
                  <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                        onsubmit="return confirm('Delete {{ addslashes($lead->full_name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm text-danger" title="Delete">
                      <i class="bi bi-trash" style="font-size:.8rem;"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    @if($leads->hasPages())
    <div class="d-flex align-items-center justify-content-between px-3 py-2"
         style="border-top:1px solid var(--border-color); font-size:.82rem;">
      <span style="color:var(--text-muted);">
        Showing {{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ $leads->total() }}
      </span>
      {{ $leads->links('pagination::bootstrap-5') }}
    </div>
    @endif
  </div>
</div>

{{-- KANBAN VIEW --}}
<div id="lead-kanban" style="display:none;">
  <div class="ncv-kanban">
    @php
      $statusDefs = [
        'new'       => ['label'=>'New',       'color'=>'#10b981'],
        'contacted' => ['label'=>'Contacted',  'color'=>'#2563eb'],
        'qualified' => ['label'=>'Qualified',  'color'=>'#7c3aed'],
        'converted' => ['label'=>'Converted',  'color'=>'#059669'],
        'recycled'  => ['label'=>'Recycled',   'color'=>'#64748b'],
      ];
      $scoreIcons = ['hot'=>'🔥','warm'=>'🌡️','cold'=>'❄️'];
    @endphp
    @foreach($statusDefs as $statusVal => $def)
    @php $col = $kanbanLeads->get($statusVal, collect()); @endphp
    <div class="ncv-kanban-col">
      <div class="ncv-kanban-col-header">
        <div>
          <div class="ncv-kanban-col-title" style="color:{{ $def['color'] }};">{{ $def['label'] }}</div>
        </div>
        <span class="ncv-kanban-count">{{ $col->count() }}</span>
      </div>

      @forelse($col as $lead)
      <div class="ncv-kanban-card" onclick="window.location='{{ route('leads.show', $lead) }}'">
        <div style="font-weight:700; font-size:.82rem; color:var(--text-primary); margin-bottom:.25rem;">
          {{ $lead->full_name }}
        </div>
        @if($lead->company_name)
          <div style="font-size:.75rem; color:var(--text-muted); margin-bottom:.4rem;">
            <i class="bi bi-building me-1"></i>{{ $lead->company_name }}
          </div>
        @endif
        <div class="d-flex align-items-center justify-content-between" style="font-size:.75rem; color:var(--text-muted);">
          <span>{{ $scoreIcons[$lead->score->value] ?? '' }} {{ ucfirst($lead->score->value) }}</span>
          <span>{{ $lead->owner?->name ?? '—' }}</span>
        </div>
      </div>
      @empty
      <p style="font-size:.78rem; color:var(--text-muted); text-align:center; padding:.75rem 0;">No leads</p>
      @endforelse

      <a href="{{ route('leads.create') }}"
         class="ncv-btn ncv-btn-ghost ncv-btn-sm w-100 mt-1"
         style="border:1.5px dashed var(--border-color); border-radius:.625rem; justify-content:center; font-size:.78rem;">
        <i class="bi bi-plus-lg"></i> Add Lead
      </a>
    </div>
    @endforeach
  </div>
</div>

@endsection

@push('scripts')
<script>
function toggleLeadView(v) {
  document.getElementById('lead-table').style.display  = v === 'table'  ? 'block' : 'none';
  document.getElementById('lead-kanban').style.display = v === 'kanban' ? 'block' : 'none';
  document.getElementById('btnLeadTable').style.background  = v === 'table'  ? 'var(--ncv-blue-50)' : '';
  document.getElementById('btnLeadTable').style.color       = v === 'table'  ? 'var(--ncv-blue-600)' : '';
  document.getElementById('btnLeadKanban').style.background = v === 'kanban' ? 'var(--ncv-blue-50)' : '';
  document.getElementById('btnLeadKanban').style.color      = v === 'kanban' ? 'var(--ncv-blue-600)' : '';
  localStorage.setItem('ncv_leads_view', v);
}
const slv = localStorage.getItem('ncv_leads_view') || 'table';
toggleLeadView(slv);
</script>
@endpush
