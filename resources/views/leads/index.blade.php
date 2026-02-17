@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'Leads')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .score-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 52px; height: 24px;
    border-radius: 9999px;
    font-size: .7rem; font-weight: 800;
    letter-spacing: .03em;
  }
  .score-hot    { background: #fee2e2; color: #b91c1c; }
  .score-warm   { background: #fef3c7; color: #92400e; }
  .score-cold   { background: #dbeafe; color: #1e40af; }
</style>
@endpush

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Leads</h1>
    <p class="ncv-page-subtitle">Track and convert potential customers.</p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-upload"></i> Import</button>
    <a href="{{ route('leads.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Lead
    </a>
  </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total Leads',  'value'=>'127', 'color'=>'var(--ncv-blue-600)', 'change'=>'+12 this week'],
    ['label'=>'Hot Leads',    'value'=>'34',  'color'=>'#ef4444',             'change'=>'Needs action'],
    ['label'=>'Qualified',    'value'=>'58',  'color'=>'#10b981',             'change'=>'48% rate'],
    ['label'=>'Converted',    'value'=>'29',  'color'=>'#8b5cf6',             'change'=>'This month'],
  ] as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.25rem;">{{ $stat['label'] }}</div>
      <div style="font-size:1.6rem;font-weight:800;color:{{ $stat['color'] }};letter-spacing:-.03em;">{{ $stat['value'] }}</div>
      <div style="font-size:.72rem;color:var(--text-muted);margin-top:.125rem;">{{ $stat['change'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- View toggle --}}
<div class="d-flex align-items-center gap-2 mb-3">
  <div style="display:flex;border:1.5px solid var(--border-color);border-radius:.625rem;overflow:hidden;">
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" id="btnTable"
            style="border-radius:0;border:none;background:var(--ncv-blue-50);color:var(--ncv-blue-600);gap:.4rem;"
            onclick="leadView('table')">
      <i class="bi bi-table"></i> Table
    </button>
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" id="btnKanban"
            style="border-radius:0;border:none;border-left:1.5px solid var(--border-color);gap:.4rem;"
            onclick="leadView('kanban')">
      <i class="bi bi-kanban"></i> Kanban
    </button>
  </div>

  <div style="position:relative;flex:1;max-width:320px;margin-left:auto;">
    <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
    <input type="text" placeholder="Search leads…" class="ncv-input" style="padding-left:2.375rem;height:38px;" />
  </div>
  <select class="ncv-select" style="width:150px;height:38px;font-size:.82rem;">
    <option>All Statuses</option>
    <option>New</option>
    <option>Contacted</option>
    <option>Qualified</option>
    <option>Recycled</option>
  </select>
  <select class="ncv-select" style="width:130px;height:38px;font-size:.82rem;">
    <option>All Scores</option>
    <option>🔥 Hot</option>
    <option>🌡️ Warm</option>
    <option>❄️ Cold</option>
  </select>
</div>

{{-- TABLE VIEW --}}
<div id="leads-table">
  <div class="ncv-table-wrapper">
    <table class="ncv-table">
      <thead>
        <tr>
          <th class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></th>
          <th>Lead</th>
          <th>Company</th>
          <th>Source</th>
          <th>Score</th>
          <th>Status</th>
          <th>Assigned To</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach([
          ['id'=>1,'name'=>'Alex Turner',    'initials'=>'AT','email'=>'alex@cloudco.io',    'company'=>'CloudCo Inc',       'source'=>'Web Form',      'score'=>'Hot',  'status'=>'Contacted',  'owner'=>'You',       'created'=>'Today',      'color'=>'#ef4444'],
          ['id'=>2,'name'=>'Priya Mehta',    'initials'=>'PM','email'=>'priya@nexgen.in',     'company'=>'NexGen Solutions',  'source'=>'LinkedIn',      'score'=>'Hot',  'status'=>'New',        'owner'=>'John S.',   'created'=>'Today',      'color'=>'#f59e0b'],
          ['id'=>3,'name'=>'Carlos Ruiz',    'initials'=>'CR','email'=>'c.ruiz@ruiztech.mx',  'company'=>'Ruiz Technology',   'source'=>'Referral',      'score'=>'Warm', 'status'=>'Qualified',  'owner'=>'You',       'created'=>'Yesterday',  'color'=>'#10b981'],
          ['id'=>4,'name'=>'Hannah Mills',   'initials'=>'HM','email'=>'h.mills@dataflow.com','company'=>'DataFlow Ltd',      'source'=>'Trade Show',    'score'=>'Warm', 'status'=>'Contacted',  'owner'=>'Emma W.',   'created'=>'Feb 15',     'color'=>'#2563eb'],
          ['id'=>5,'name'=>'Tom Bradley',    'initials'=>'TB','email'=>'tom@breachco.net',    'company'=>'BreachCo',          'source'=>'Cold Email',    'score'=>'Cold', 'status'=>'New',        'owner'=>'John S.',   'created'=>'Feb 14',     'color'=>'#8b5cf6'],
          ['id'=>6,'name'=>'Yuki Tanaka',    'initials'=>'YT','email'=>'yuki.t@solveX.jp',   'company'=>'SolveX Corp',       'source'=>'Web Form',      'score'=>'Hot',  'status'=>'Qualified',  'owner'=>'You',       'created'=>'Feb 13',     'color'=>'#06b6d4'],
          ['id'=>7,'name'=>'Dana Brown',     'initials'=>'DB','email'=>'dana@mintlabs.co',    'company'=>'Mint Labs',         'source'=>'Google Ads',    'score'=>'Warm', 'status'=>'Recycled',   'owner'=>'Emma W.',   'created'=>'Feb 10',     'color'=>'#94a3b8'],
          ['id'=>8,'name'=>'Sven Larsen',    'initials'=>'SL','email'=>'sven@nordic-ent.dk',  'company'=>'Nordic Enterprises','source'=>'Partnership',   'score'=>'Cold', 'status'=>'New',        'owner'=>'John S.',   'created'=>'Feb 8',      'color'=>'#0891b2'],
        ] as $lead)
        <tr>
          <td class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></td>
          <td>
            <div class="ncv-table-name">
              <div class="ncv-table-avatar" style="background:{{ $lead['color'] }}18;color:{{ $lead['color'] }};">{{ $lead['initials'] }}</div>
              <div>
                <a href="{{ route('leads.show', $lead['id']) }}" class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;">{{ $lead['name'] }}</a>
                <div class="ncv-table-cell-sub">{{ $lead['email'] }}</div>
              </div>
            </div>
          </td>
          <td style="font-size:.83rem;color:var(--text-secondary);">{{ $lead['company'] }}</td>
          <td>
            <span class="ncv-badge ncv-badge-muted" style="font-size:.7rem;">
              {{ $lead['source'] }}
            </span>
          </td>
          <td>
            <span class="score-badge score-{{ strtolower($lead['score']) }}">
              {{ $lead['score'] == 'Hot' ? '🔥' : ($lead['score'] == 'Warm' ? '🌡️' : '❄️') }}
              {{ $lead['score'] }}
            </span>
          </td>
          <td>
            @php
              $statusColors = ['New'=>'primary','Contacted'=>'cyan','Qualified'=>'success','Converted'=>'purple','Recycled'=>'muted'];
              $sc = $statusColors[$lead['status']] ?? 'muted';
            @endphp
            <span class="ncv-badge ncv-badge-{{ $sc }}"><span class="dot"></span>{{ $lead['status'] }}</span>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $lead['owner'] }}</td>
          <td style="font-size:.775rem;color:var(--text-muted);">{{ $lead['created'] }}</td>
          <td>
            <div class="d-flex gap-1">
              <a href="{{ route('leads.show', $lead['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
              <a href="{{ route('leads.edit', $lead['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
              <button class="ncv-btn ncv-btn-sm" style="font-size:.72rem;padding:.3rem .625rem;background:#d1fae5;color:#065f46;border:none;border-radius:.5rem;" title="Convert to Deal">
                <i class="bi bi-arrow-right-circle"></i> Convert
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- KANBAN VIEW --}}
<div id="leads-kanban" style="display:none;">
  <div class="ncv-kanban" style="padding-bottom:1.5rem;">
    @foreach([
      ['stage'=>'New',       'count'=>34, 'color'=>'#2563eb', 'leads'=>[['name'=>'Alex Turner','company'=>'CloudCo','score'=>'Hot','email'=>'alex@cloudco.io'],['name'=>'Priya Mehta','company'=>'NexGen','score'=>'Hot','email'=>'priya@nexgen.in'],['name'=>'Tom Bradley','company'=>'BreachCo','score'=>'Cold','email'=>'tom@breach.co']]],
      ['stage'=>'Contacted', 'count'=>28, 'color'=>'#06b6d4', 'leads'=>[['name'=>'Alex Turner','company'=>'CloudCo','score'=>'Hot','email'=>'alex@cloudco.io'],['name'=>'Hannah Mills','company'=>'DataFlow','score'=>'Warm','email'=>'h@dataflow.com']]],
      ['stage'=>'Qualified', 'count'=>22, 'color'=>'#10b981', 'leads'=>[['name'=>'Carlos Ruiz','company'=>'Ruiz Tech','score'=>'Warm','email'=>'c.ruiz@ruiztech.mx'],['name'=>'Yuki Tanaka','company'=>'SolveX','score'=>'Hot','email'=>'yuki@solvex.jp']]],
      ['stage'=>'Converted', 'count'=>29, 'color'=>'#8b5cf6', 'leads'=>[['name'=>'Sophia Brown','company'=>'TechPeak','score'=>'Hot','email'=>'sophia@techpeak.com']]],
      ['stage'=>'Recycled',  'count'=>14, 'color'=>'#94a3b8', 'leads'=>[['name'=>'Dana Brown','company'=>'Mint Labs','score'=>'Warm','email'=>'dana@mintlabs.co']]],
    ] as $col)
    <div class="ncv-kanban-col">
      <div class="ncv-kanban-col-header">
        <span class="ncv-kanban-col-title" style="color:{{ $col['color'] }};">{{ $col['stage'] }}</span>
        <span class="ncv-kanban-count">{{ $col['count'] }}</span>
      </div>
      @foreach($col['leads'] as $lead)
      <div class="ncv-kanban-card" draggable="true">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;">
          <div style="font-weight:700;font-size:.875rem;color:var(--text-primary);">{{ $lead['name'] }}</div>
          <span class="score-badge score-{{ strtolower($lead['score']) }}" style="font-size:.62rem;width:44px;height:20px;">
            {{ $lead['score'] == 'Hot' ? '🔥' : ($lead['score'] == 'Warm' ? '🌡️' : '❄️') }}
          </span>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem;"><i class="bi bi-building"></i> {{ $lead['company'] }}</div>
        <div style="font-size:.75rem;color:var(--ncv-blue-600);"><i class="bi bi-envelope"></i> {{ $lead['email'] }}</div>
        <div style="display:flex;gap:.375rem;margin-top:.625rem;">
          <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="font-size:.7rem;padding:.25rem .5rem;flex:1;justify-content:center;">
            <i class="bi bi-telephone"></i> Call
          </button>
          <button class="ncv-btn ncv-btn-sm" style="font-size:.7rem;padding:.25rem .5rem;flex:1;justify-content:center;background:#d1fae5;color:#065f46;border:none;border-radius:.5rem;">
            <i class="bi bi-arrow-right-circle"></i> Convert
          </button>
        </div>
      </div>
      @endforeach
      <button class="ncv-btn ncv-btn-ghost ncv-btn-sm w-100 mt-1" style="border:1.5px dashed var(--border-color);border-radius:.625rem;justify-content:center;">
        <i class="bi bi-plus-lg"></i> Add Lead
      </button>
    </div>
    @endforeach
  </div>
</div>

{{-- Pagination (table only) --}}
<div id="leads-pagination" class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Showing <strong style="color:var(--text-primary);">1–8</strong> of <strong style="color:var(--text-primary);">127</strong> leads</p>
  <nav class="ncv-pagination">
    <a href="#" class="ncv-page-btn" disabled><i class="bi bi-chevron-left" style="font-size:.75rem;"></i></a>
    <a href="#" class="ncv-page-btn active">1</a>
    <a href="#" class="ncv-page-btn">2</a>
    <a href="#" class="ncv-page-btn">3</a>
    <a href="#" class="ncv-page-btn"><i class="bi bi-chevron-right" style="font-size:.75rem;"></i></a>
  </nav>
</div>

@endsection

@push('scripts')
<script>
  function leadView(v) {
    document.getElementById('leads-table').style.display   = v === 'table'  ? 'block' : 'none';
    document.getElementById('leads-kanban').style.display  = v === 'kanban' ? 'block' : 'none';
    document.getElementById('leads-pagination').style.display = v === 'table' ? 'flex' : 'none';

    const btnT = document.getElementById('btnTable');
    const btnK = document.getElementById('btnKanban');
    btnT.style.background = v === 'table'  ? 'var(--ncv-blue-50)'  : '';
    btnT.style.color      = v === 'table'  ? 'var(--ncv-blue-600)' : '';
    btnK.style.background = v === 'kanban' ? 'var(--ncv-blue-50)'  : '';
    btnK.style.color      = v === 'kanban' ? 'var(--ncv-blue-600)' : '';

    localStorage.setItem('ncv_leads_view', v);
  }

  const sv = localStorage.getItem('ncv_leads_view') || 'table';
  leadView(sv);
</script>
@endpush
