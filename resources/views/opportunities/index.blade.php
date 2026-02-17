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
  .ncv-kanban-card .priority-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .opp-value { font-size: .9rem; font-weight: 800; color: var(--text-primary); }
  .opp-prob  { font-size: .72rem; color: var(--text-muted); margin-top: 1px; }
  .stage-total { font-size: .75rem; font-weight: 700; color: var(--text-muted); }
</style>
@endpush

@section('content')

{{-- Pipeline Header --}}
<div class="pipeline-header">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h1 style="font-size:1.3rem;font-weight:800;margin:0;letter-spacing:-.03em;">Sales Pipeline</h1>
      <p style="font-size:.8rem;color:rgba(255,255,255,.65);margin:.25rem 0 0;">Drag deals between stages to update their status.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <div style="display:flex;gap:1.5rem;">
        @foreach([['label'=>'Total Pipeline','value'=>'$1.2M'],['label'=>'Closing This Month','value'=>'$248k'],['label'=>'Win Rate','value'=>'38%']] as $kpi)
        <div style="text-align:center;">
          <div style="font-size:1.25rem;font-weight:800;color:#fff;">{{ $kpi['value'] }}</div>
          <div style="font-size:.68rem;color:rgba(255,255,255,.6);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">{{ $kpi['label'] }}</div>
        </div>
        @endforeach
      </div>
      <a href="{{ route('opportunities.create') }}" class="ncv-btn ncv-btn-sm ms-3"
         style="background:#fff;color:#1d4ed8;border:none;font-weight:700;">
        <i class="bi bi-plus-lg"></i> Add Deal
      </a>
    </div>
  </div>
</div>

{{-- Filters bar --}}
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
    <input type="text" placeholder="Search deals…" class="ncv-input" style="padding-left:2.375rem;width:220px;height:36px;" />
  </div>
  <select class="ncv-select" style="width:140px;height:36px;font-size:.82rem;">
    <option>All Owners</option>
    <option>You</option>
    <option>John Smith</option>
    <option>Emma Williams</option>
  </select>
  <select class="ncv-select" style="width:140px;height:36px;font-size:.82rem;">
    <option>All Products</option>
    <option>Enterprise</option>
    <option>Pro</option>
    <option>Starter</option>
  </select>
  <div class="d-flex gap-2 ms-auto">
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="pipelineView('kanban')" id="btnKanban"
            style="background:var(--ncv-blue-50);color:var(--ncv-blue-600);">
      <i class="bi bi-kanban"></i> Kanban
    </button>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="pipelineView('list')" id="btnList">
      <i class="bi bi-table"></i> List
    </button>
  </div>
</div>

{{-- KANBAN BOARD --}}
<div id="pipeline-kanban">
<div class="ncv-kanban">

  @php
  $stages = [
    ['name'=>'Prospecting',  'color'=>'#64748b', 'pct'=>20, 'total'=>'$186k', 'deals'=>[
      ['id'=>1,'title'=>'Cyberdyne Platform','account'=>'Cyberdyne Sys.','value'=>'$62,000','prob'=>20,'close'=>'Mar 30','owner'=>'JS','priority'=>'medium','color'=>'#8b5cf6'],
      ['id'=>2,'title'=>'Nordic IoT Suite',  'account'=>'Nordic Ent.',   'value'=>'$28,000','prob'=>15,'close'=>'Apr 15','owner'=>'EW','priority'=>'low',   'color'=>'#0891b2'],
    ]],
    ['name'=>'Qualification','color'=>'#2563eb', 'pct'=>30, 'total'=>'$234k', 'deals'=>[
      ['id'=>3,'title'=>'Globex BI Module', 'account'=>'Globex Inc',    'value'=>'$31,500','prob'=>35,'close'=>'Mar 08','owner'=>'You','priority'=>'high',  'color'=>'#f59e0b'],
      ['id'=>4,'title'=>'Initech CRM',      'account'=>'Initech LLC',   'value'=>'$18,750','prob'=>30,'close'=>'Mar 20','owner'=>'JS', 'priority'=>'medium','color'=>'#8b5cf6'],
      ['id'=>5,'title'=>'SolveX Analytics', 'account'=>'SolveX Corp',   'value'=>'$22,000','prob'=>40,'close'=>'Mar 15','owner'=>'EW', 'priority'=>'low',  'color'=>'#10b981'],
    ]],
    ['name'=>'Proposal',     'color'=>'#7c3aed', 'pct'=>55, 'total'=>'$148k', 'deals'=>[
      ['id'=>6,'title'=>'Acme Add-On Seats','account'=>'Acme Corp',     'value'=>'$12,500','prob'=>60,'close'=>'Feb 28','owner'=>'You','priority'=>'high',  'color'=>'#2563eb'],
      ['id'=>7,'title'=>'Umbrella BI Suite','account'=>'Umbrella Corp', 'value'=>'$85,000','prob'=>65,'close'=>'Jan 31','owner'=>'You','priority'=>'high',  'color'=>'#ef4444'],
    ]],
    ['name'=>'Negotiation',  'color'=>'#d97706', 'pct'=>75, 'total'=>'$127k', 'deals'=>[
      ['id'=>8,'title'=>'TechStart Expand.','account'=>'TechStart Inc', 'value'=>'$42,000','prob'=>90,'close'=>'Feb 15','owner'=>'JS', 'priority'=>'high',  'color'=>'#10b981'],
      ['id'=>9,'title'=>'Wayne API Lic.',   'account'=>'Wayne Ent.',    'value'=>'$38,000','prob'=>80,'close'=>'Feb 20','owner'=>'EW', 'priority'=>'high',  'color'=>'#0891b2'],
    ]],
    ['name'=>'Closed Won',   'color'=>'#059669', 'pct'=>100,'total'=>'$85k',  'deals'=>[
      ['id'=>10,'title'=>'Acme Enterprise', 'account'=>'Acme Corp',     'value'=>'$85,000','prob'=>100,'close'=>'Jan 15','owner'=>'You','priority'=>'high', 'color'=>'#2563eb'],
    ]],
    ['name'=>'Closed Lost',  'color'=>'#dc2626', 'pct'=>0,  'total'=>'$55k',  'deals'=>[
      ['id'=>11,'title'=>'Initech 2025 Bid','account'=>'Initech LLC',   'value'=>'$55,000','prob'=>0,  'close'=>'Dec 31','owner'=>'JS', 'priority'=>'low',  'color'=>'#8b5cf6'],
    ]],
  ];
  $priorityColors = ['high'=>'#ef4444','medium'=>'#f59e0b','low'=>'#94a3b8'];
  @endphp

  @foreach($stages as $stage)
  <div class="ncv-kanban-col" ondragover="event.preventDefault()" ondrop="dropDeal(event, '{{ $stage['name'] }}')">
    <div class="ncv-kanban-col-header">
      <div>
        <div class="ncv-kanban-col-title" style="color:{{ $stage['color'] }};">{{ $stage['name'] }}</div>
        <div class="stage-total">{{ $stage['total'] }}</div>
      </div>
      <span class="ncv-kanban-count">{{ count($stage['deals']) }}</span>
    </div>

    @foreach($stage['deals'] as $deal)
    <div class="ncv-kanban-card" draggable="true"
         ondragstart="dragDeal(event, {{ $deal['id'] }})"
         onclick="window.location='{{ route('opportunities.show', $deal['id']) }}'">
      <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.5rem;">
        <div class="ncv-table-avatar" style="width:28px;height:28px;background:{{ $deal['color'] }}18;color:{{ $deal['color'] }};font-size:.65rem;border-radius:.4rem;flex-shrink:0;">
          {{ strtoupper(substr($deal['account'], 0, 2)) }}
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-weight:700;font-size:.82rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $deal['title'] }}</div>
          <div style="font-size:.72rem;color:var(--text-muted);">{{ $deal['account'] }}</div>
        </div>
        <span class="priority-dot" style="background:{{ $priorityColors[$deal['priority']] }};margin-top:4px;" title="{{ ucfirst($deal['priority']) }} priority"></span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <div class="opp-value">{{ $deal['value'] }}</div>
          <div class="opp-prob">{{ $deal['prob'] }}% · {{ $deal['close'] }}</div>
        </div>
        <div style="width:30px;height:30px;border-radius:50%;background:var(--ncv-blue-50);color:var(--text-muted);display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:800;flex-shrink:0;">
          {{ $deal['owner'] }}
        </div>
      </div>
      <div class="ncv-progress-bar mt-2" style="height:3px;">
        <div class="ncv-progress-fill" style="width:{{ $deal['prob'] }}%;background:{{ $stage['color'] }};"></div>
      </div>
    </div>
    @endforeach

    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm w-100 mt-1"
            style="border:1.5px dashed var(--border-color);border-radius:.625rem;justify-content:center;font-size:.78rem;"
            onclick="event.stopPropagation(); window.location='{{ route('opportunities.create') }}'">
      <i class="bi bi-plus-lg"></i> Add Deal
    </button>
  </div>
  @endforeach

</div>
</div>

{{-- LIST VIEW --}}
<div id="pipeline-list" style="display:none;">
  <div class="ncv-table-wrapper">
    <table class="ncv-table">
      <thead>
        <tr>
          <th class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></th>
          <th>Deal</th>
          <th>Account</th>
          <th>Stage</th>
          <th>Value</th>
          <th>Probability</th>
          <th>Close Date</th>
          <th>Owner</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach([
          ['id'=>8,'title'=>'TechStart Expansion','account'=>'TechStart Inc','stage'=>'Negotiation','sc'=>'success','value'=>'$42,000','prob'=>90,'close'=>'Feb 15','owner'=>'John S.'],
          ['id'=>7,'title'=>'Umbrella BI Suite',  'account'=>'Umbrella Corp', 'stage'=>'Proposal',   'sc'=>'warning','value'=>'$85,000','prob'=>65,'close'=>'Jan 31','owner'=>'You'],
          ['id'=>6,'title'=>'Acme Add-On Seats',  'account'=>'Acme Corp',     'stage'=>'Proposal',   'sc'=>'warning','value'=>'$12,500','prob'=>60,'close'=>'Feb 28','owner'=>'You'],
          ['id'=>3,'title'=>'Globex BI Module',   'account'=>'Globex Inc',    'stage'=>'Qualification','sc'=>'primary','value'=>'$31,500','prob'=>35,'close'=>'Mar 08','owner'=>'You'],
        ] as $deal)
        <tr>
          <td class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></td>
          <td>
            <a href="{{ route('opportunities.show', $deal['id']) }}" class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;font-size:.875rem;">{{ $deal['title'] }}</a>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $deal['account'] }}</td>
          <td><span class="ncv-badge ncv-badge-{{ $deal['sc'] }}"><span class="dot"></span>{{ $deal['stage'] }}</span></td>
          <td style="font-weight:700;">{{ $deal['value'] }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:.5rem;">
              <div class="ncv-progress-bar" style="width:70px;"><div class="ncv-progress-fill" style="width:{{ $deal['prob'] }}%;"></div></div>
              <span style="font-size:.75rem;font-weight:600;color:var(--text-muted);">{{ $deal['prob'] }}%</span>
            </div>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $deal['close'] }}</td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $deal['owner'] }}</td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('opportunities.show', $deal['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
              <a href="{{ route('opportunities.edit', $deal['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

@push('scripts')
<script>
  let draggedId = null;

  function dragDeal(e, id) {
    draggedId = id;
    e.dataTransfer.effectAllowed = 'move';
  }

  function dropDeal(e, stageName) {
    e.preventDefault();
    if (draggedId) {
      window.showToast('Stage Updated', `Deal moved to "${stageName}".`, 'success');
      draggedId = null;
    }
  }

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
