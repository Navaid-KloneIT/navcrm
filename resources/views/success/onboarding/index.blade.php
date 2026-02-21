@extends('layouts.app')

@section('title', 'Onboarding Pipelines')
@section('page-title', 'Onboarding Pipelines')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Onboarding Pipelines</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-3">
  @foreach([
    ['label'=>'Total Pipelines',  'value'=>$stats['total'],       'icon'=>'bi-clipboard-check',      'color'=>'#6366f1'],
    ['label'=>'In Progress',      'value'=>$stats['in_progress'], 'icon'=>'bi-play-circle',          'color'=>'#3b82f6'],
    ['label'=>'Completed',        'value'=>$stats['completed'],   'icon'=>'bi-check-circle',         'color'=>'#10b981'],
    ['label'=>'Overdue',          'value'=>$stats['overdue'],     'icon'=>'bi-exclamation-circle',   'color'=>'#ef4444'],
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
          <input type="text" name="search" value="{{ request('search') }}" class="ncv-input ncv-input-search" placeholder="Search pipelines...">
        </div>
        <select name="status" class="ncv-select" style="width:150px;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          @foreach($statuses as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
        <select name="assigned_to" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Assignees</option>
          @foreach($assignees as $a)
            <option value="{{ $a->id }}" {{ request('assigned_to') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
          @endforeach
        </select>
        <select name="account_id" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Accounts</option>
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
          @endforeach
        </select>
        @if(request()->hasAny(['search','status','assigned_to','account_id']))
          <a href="{{ route('success.onboarding.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
        @endif
      </form>
      <a href="{{ route('success.onboarding.create') }}" class="ncv-btn ncv-btn-primary ms-auto">
        <i class="bi bi-plus-lg"></i> New Pipeline
      </a>
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
            <th>Pipeline #</th>
            <th>Name</th>
            <th>Account</th>
            <th>Assignee</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Due Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($pipelines as $p)
          <tr>
            <td><span class="ncv-badge ncv-badge-secondary" style="font-family:monospace;">{{ $p->pipeline_number }}</span></td>
            <td>
              <a href="{{ route('success.onboarding.show', $p) }}" style="font-weight:600;color:var(--text-primary);text-decoration:none;">
                {{ $p->name }}
              </a>
            </td>
            <td>{{ $p->account?->name ?? '—' }}</td>
            <td>{{ $p->assignee?->name ?? '—' }}</td>
            <td>
              <span class="ncv-badge ncv-badge-{{ $p->status->color() }}">{{ $p->status->label() }}</span>
            </td>
            <td style="min-width:120px;">
              @php $pct = $p->progress; @endphp
              <div style="display:flex;align-items:center;gap:.5rem;">
                <div style="flex:1;height:6px;border-radius:3px;background:var(--border-color);overflow:hidden;">
                  <div style="width:{{ $pct }}%;height:100%;background:{{ $pct === 100 ? '#10b981' : '#3b82f6' }};border-radius:3px;"></div>
                </div>
                <span style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;">{{ $pct }}%</span>
              </div>
            </td>
            <td>
              @if($p->due_date)
                <span class="{{ $p->due_date->isPast() && ! in_array($p->status->value, ['completed','cancelled']) ? 'text-danger fw-bold' : '' }}">
                  {{ $p->due_date->format('M d, Y') }}
                </span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('success.onboarding.show', $p) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-eye"></i></a>
              <a href="{{ route('success.onboarding.edit', $p) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-pencil"></i></a>
              <form method="POST" action="{{ route('success.onboarding.destroy', $p) }}" style="display:inline;" onsubmit="return confirm('Delete this pipeline?')">
                @csrf @method('DELETE')
                <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">
              <i class="bi bi-clipboard-check" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
              No onboarding pipelines found. <a href="{{ route('success.onboarding.create') }}">Create your first pipeline</a>.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($pipelines->hasPages())
  <div class="ncv-card-body" style="border-top:1px solid var(--border-color);padding:.75rem 1.25rem;">
    {{ $pipelines->links() }}
  </div>
  @endif
</div>

@endsection
