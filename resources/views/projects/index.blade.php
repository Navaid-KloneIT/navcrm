@extends('layouts.app')

@section('title', 'Projects')
@section('page-title', 'Projects')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Projects</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-3">
  @foreach([
    ['label'=>'Total Projects',    'value'=>$stats['total'],     'icon'=>'bi-kanban',        'color'=>'#6366f1'],
    ['label'=>'Active',            'value'=>$stats['active'],    'icon'=>'bi-play-circle',   'color'=>'#10b981'],
    ['label'=>'Completed',         'value'=>$stats['completed'], 'icon'=>'bi-check-circle',  'color'=>'#3b82f6'],
    ['label'=>'Overdue',           'value'=>$stats['overdue'],   'icon'=>'bi-exclamation-circle', 'color'=>'#ef4444'],
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
      <form method="GET" class="d-flex gap-2 flex-wrap flex-grow-1">
        <div class="ncv-input-group" style="max-width:260px;flex:1;">
          <i class="bi bi-search ncv-input-icon"></i>
          <input type="text" name="search" value="{{ request('search') }}" class="ncv-input ncv-input-search" placeholder="Search projects…">
        </div>
        <select name="status" class="ncv-select" style="width:140px;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          @foreach($statuses as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
        <select name="manager_id" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Managers</option>
          @foreach($managers as $m)
            <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
          @endforeach
        </select>
        @if(request()->hasAny(['search','status','manager_id']))
          <a href="{{ route('projects.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
        @endif
      </form>

      {{-- View Toggle --}}
      <div class="d-flex gap-1" style="border:1px solid var(--border-color);border-radius:.5rem;padding:3px;">
        <button id="btn-table"  onclick="switchView('table')"  class="ncv-btn ncv-btn-sm" style="padding:.25rem .6rem;border-radius:.35rem;">
          <i class="bi bi-list-ul"></i>
        </button>
        <button id="btn-kanban" onclick="switchView('kanban')" class="ncv-btn ncv-btn-sm" style="padding:.25rem .6rem;border-radius:.35rem;">
          <i class="bi bi-kanban"></i>
        </button>
      </div>

      <a href="{{ route('projects.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
        <i class="bi bi-plus-lg"></i> New Project
      </a>
    </div>
  </div>
</div>

{{-- TABLE VIEW --}}
<div id="view-table">
  <div class="ncv-card">
    <div class="ncv-card-body p-0">
      <div class="table-responsive">
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Project #</th>
              <th>Name</th>
              <th>Account</th>
              <th>Manager</th>
              <th>Status</th>
              <th>Due Date</th>
              <th>Progress</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($projects as $project)
            <tr>
              <td><span class="ncv-badge ncv-badge-secondary" style="font-family:monospace;">{{ $project->project_number }}</span></td>
              <td>
                <a href="{{ route('projects.show', $project) }}" style="font-weight:600;color:var(--text-primary);text-decoration:none;">
                  {{ $project->name }}
                </a>
                @if($project->is_from_opportunity)
                  <span class="ncv-badge ncv-badge-primary ms-1" style="font-size:.6rem;">From Deal</span>
                @endif
              </td>
              <td>{{ $project->account?->name ?? '—' }}</td>
              <td>{{ $project->manager?->name ?? '—' }}</td>
              <td>
                <span class="ncv-badge ncv-badge-{{ $project->status->color() }}">{{ $project->status->label() }}</span>
              </td>
              <td>
                @if($project->due_date)
                  <span class="{{ $project->due_date->isPast() && $project->status->value !== 'completed' ? 'text-danger fw-bold' : '' }}">
                    {{ $project->due_date->format('M d, Y') }}
                  </span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td style="min-width:120px;">
                @php $pct = $project->milestone_progress; @endphp
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <div style="flex:1;height:6px;border-radius:3px;background:var(--border-color);overflow:hidden;">
                    <div style="width:{{ $pct }}%;height:100%;background:{{ $pct===100?'#10b981':'#3b82f6' }};border-radius:3px;"></div>
                  </div>
                  <span style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;">{{ $pct }}%</span>
                </div>
              </td>
              <td class="text-end">
                <a href="{{ route('projects.show', $project) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-eye"></i></a>
                <a href="{{ route('projects.edit', $project) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" style="display:inline;" onsubmit="return confirm('Delete this project?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No projects found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($projects->hasPages())
    <div class="ncv-card-body" style="border-top:1px solid var(--border-color);padding:.75rem 1.25rem;">
      {{ $projects->links() }}
    </div>
    @endif
  </div>
</div>

{{-- KANBAN VIEW --}}
<div id="view-kanban" style="display:none;">
  <div class="d-flex gap-3 overflow-auto pb-2">
    @foreach($kanbanData as $statusValue => $cards)
    @php
      $statusEnum = \App\Enums\ProjectStatus::from($statusValue);
      $colColors  = ['planning'=>'#6366f1','active'=>'#3b82f6','on_hold'=>'#f59e0b','completed'=>'#10b981'];
      $colColor   = $colColors[$statusValue] ?? '#94a3b8';
    @endphp
    <div style="min-width:280px;flex:1;">
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;padding:.5rem .75rem;border-radius:.5rem;background:{{ $colColor }}14;border:1px solid {{ $colColor }}30;">
        <div style="width:10px;height:10px;border-radius:50%;background:{{ $colColor }};"></div>
        <span style="font-weight:700;font-size:.8rem;color:var(--text-primary);">{{ $statusEnum->label() }}</span>
        <span class="ncv-badge" style="margin-left:auto;background:{{ $colColor }}20;color:{{ $colColor }};font-size:.68rem;">{{ $cards->count() }}</span>
      </div>

      @foreach($cards as $project)
      <a href="{{ route('projects.show', $project) }}" style="text-decoration:none;display:block;margin-bottom:.625rem;">
        <div class="ncv-card" style="border-left:3px solid {{ $colColor }};transition:transform .15s;">
          <div class="ncv-card-body" style="padding:.875rem 1rem;">
            <div style="font-size:.7rem;color:var(--text-muted);font-family:monospace;margin-bottom:.25rem;">{{ $project->project_number }}</div>
            <div style="font-weight:700;font-size:.875rem;color:var(--text-primary);margin-bottom:.375rem;">{{ $project->name }}</div>
            @if($project->account)
            <div style="font-size:.78rem;color:var(--text-secondary);margin-bottom:.5rem;"><i class="bi bi-building" style="font-size:.7rem;"></i> {{ $project->account->name }}</div>
            @endif
            @php $pct = $project->milestone_progress; @endphp
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
              <div style="flex:1;height:5px;border-radius:3px;background:var(--border-color);overflow:hidden;">
                <div style="width:{{ $pct }}%;height:100%;background:{{ $pct===100?'#10b981':'#3b82f6' }};border-radius:3px;"></div>
              </div>
              <span style="font-size:.7rem;color:var(--text-muted);">{{ $pct }}%</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              @if($project->due_date)
              <span style="font-size:.72rem;color:{{ $project->due_date->isPast() && $statusValue !== 'completed' ? '#ef4444' : 'var(--text-muted)' }};">
                <i class="bi bi-calendar3" style="font-size:.65rem;"></i> {{ $project->due_date->format('M d') }}
              </span>
              @endif
              @if($project->manager)
              <div style="width:24px;height:24px;border-radius:50%;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;">
                {{ strtoupper(substr($project->manager->name, 0, 2)) }}
              </div>
              @endif
            </div>
          </div>
        </div>
      </a>
      @endforeach

      @if($cards->isEmpty())
      <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.8rem;border:1px dashed var(--border-color);border-radius:.5rem;">No projects</div>
      @endif
    </div>
    @endforeach
  </div>
</div>

@endsection

@push('scripts')
<script>
const savedView = localStorage.getItem('projects_view') || 'table';
switchView(savedView);

function switchView(v) {
  document.getElementById('view-table').style.display  = v === 'table'  ? '' : 'none';
  document.getElementById('view-kanban').style.display = v === 'kanban' ? '' : 'none';
  document.getElementById('btn-table').style.background  = v === 'table'  ? 'var(--ncv-blue-100)' : '';
  document.getElementById('btn-kanban').style.background = v === 'kanban' ? 'var(--ncv-blue-100)' : '';
  localStorage.setItem('projects_view', v);
}
</script>
@endpush
