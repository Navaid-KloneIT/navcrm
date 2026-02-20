@extends('layouts.app')

@section('title', 'Timesheets')
@section('page-title', 'Timesheets')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Timesheets</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-3">
  @foreach([
    ['label'=>'Total Hours',      'value'=>number_format($stats['total_hours'],1).'h',        'icon'=>'bi-clock',           'color'=>'#6366f1'],
    ['label'=>'Billable Hours',   'value'=>number_format($stats['billable_hours'],1).'h',     'icon'=>'bi-currency-dollar', 'color'=>'#10b981'],
    ['label'=>'Non-Billable Hrs', 'value'=>number_format($stats['non_billable_hours'],1).'h', 'icon'=>'bi-clock-history',   'color'=>'#f59e0b'],
  ] as $kpi)
  <div class="col-md-4">
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
      <form method="GET" class="d-flex gap-2 flex-wrap flex-grow-1" id="filterForm">
        <select name="project_id" class="ncv-select" style="width:200px;" onchange="this.form.submit()">
          <option value="">All Projects</option>
          @foreach($projects as $p)
            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->project_number }} — {{ $p->name }}</option>
          @endforeach
        </select>
        <select name="user_id" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Users</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
          @endforeach
        </select>
        <select name="is_billable" class="ncv-select" style="width:140px;" onchange="this.form.submit()">
          <option value="">Billable & Non</option>
          <option value="1" {{ request('is_billable') === '1' ? 'selected' : '' }}>Billable Only</option>
          <option value="0" {{ request('is_billable') === '0' ? 'selected' : '' }}>Non-Billable</option>
        </select>
        <div class="d-flex gap-1" style="align-items:center;">
          <input type="date" name="date_from" class="ncv-input" style="width:140px;" value="{{ request('date_from') }}" placeholder="From">
          <span style="color:var(--text-muted);font-size:.85rem;">–</span>
          <input type="date" name="date_to"   class="ncv-input" style="width:140px;" value="{{ request('date_to') }}" placeholder="To">
          <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">Filter</button>
        </div>
        @if(request()->hasAny(['project_id','user_id','is_billable','date_from','date_to']))
          <a href="{{ route('timesheets.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
        @endif
      </form>

      <div class="d-flex gap-2">
        <a href="{{ route('timesheets.workload') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
          <i class="bi bi-people"></i> Workload
        </a>
        <a href="{{ route('timesheets.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> Log Time
        </a>
      </div>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    <div class="table-responsive">
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Project</th>
            <th>User</th>
            <th>Hours</th>
            <th>Billable</th>
            <th>Rate</th>
            <th>Description</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($timesheets as $ts)
          <tr>
            <td style="white-space:nowrap;">{{ $ts->date->format('M d, Y') }}</td>
            <td>
              @if($ts->project)
              <a href="{{ route('projects.show', $ts->project) }}" style="font-weight:600;color:var(--text-primary);text-decoration:none;">
                <span style="font-size:.72rem;color:var(--text-muted);font-family:monospace;">{{ $ts->project->project_number }}</span><br>
                {{ $ts->project->name }}
              </a>
              @else
              <span class="text-muted">—</span>
              @endif
            </td>
            <td>{{ $ts->user?->name ?? '—' }}</td>
            <td><strong>{{ $ts->hours }}h</strong></td>
            <td>
              @if($ts->is_billable)
                <span class="ncv-badge ncv-badge-success" style="font-size:.65rem;">Billable</span>
              @else
                <span class="ncv-badge ncv-badge-secondary" style="font-size:.65rem;">Non-Bill.</span>
              @endif
            </td>
            <td>{{ $ts->billable_rate ? '$'.number_format($ts->billable_rate,2).'/h' : '—' }}</td>
            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ts->description ?? '—' }}</td>
            <td class="text-end">
              <a href="{{ route('timesheets.show', $ts) }}"  class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-eye"></i></a>
              <a href="{{ route('timesheets.edit', $ts) }}"  class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('timesheets.destroy', $ts) }}" style="display:inline;" onsubmit="return confirm('Delete entry?')">
                @csrf @method('DELETE')
                <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">No time entries found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($timesheets->hasPages())
  <div class="ncv-card-body" style="border-top:1px solid var(--border-color);padding:.75rem 1.25rem;">
    {{ $timesheets->links() }}
  </div>
  @endif
</div>

@endsection
