@extends('layouts.app')

@section('title', 'Workflows')
@section('page-title', 'Workflows')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Workflows</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-3">
  @foreach([
    ['label'=>'Total Workflows', 'value'=>$stats['total'],      'icon'=>'bi-lightning-charge', 'color'=>'#6366f1'],
    ['label'=>'Active',          'value'=>$stats['active'],     'icon'=>'bi-toggle-on',        'color'=>'#10b981'],
    ['label'=>'Runs Today',      'value'=>$stats['runs_today'], 'icon'=>'bi-play-circle',      'color'=>'#3b82f6'],
  ] as $kpi)
  <div class="col-6 col-md-4">
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
          <input type="text" name="search" value="{{ request('search') }}" class="ncv-input ncv-input-search" placeholder="Search workflows…">
        </div>
        <select name="trigger" class="ncv-select" style="width:220px;" onchange="this.form.submit()">
          <option value="">All Triggers</option>
          @foreach($triggers as $t)
            <option value="{{ $t->value }}" {{ request('trigger') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
          @endforeach
        </select>
        <select name="active" class="ncv-select" style="width:130px;" onchange="this.form.submit()">
          <option value="">All Status</option>
          <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
          <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="ncv-btn ncv-btn-ghost"><i class="bi bi-funnel"></i> Filter</button>
        @if(request()->hasAny(['search','trigger','active']))
          <a href="{{ route('workflows.index') }}" class="ncv-btn ncv-btn-ghost">Clear</a>
        @endif
      </form>
      <a href="{{ route('workflows.create') }}" class="ncv-btn ncv-btn-primary ms-auto">
        <i class="bi bi-plus-lg"></i> New Workflow
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
            <th style="padding:.75rem 1.25rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Name</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Trigger</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Runs</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Status</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($workflows as $wf)
          <tr style="border-bottom:1px solid var(--border-color);">
            <td style="padding:.75rem 1.25rem;">
              <a href="{{ route('workflows.show', $wf) }}" style="font-weight:600;color:var(--ncv-blue-500);text-decoration:none;">
                {{ $wf->name }}
              </a>
              @if($wf->description)
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:.1rem;">{{ Str::limit($wf->description, 60) }}</div>
              @endif
            </td>
            <td style="padding:.75rem 1rem;">
              <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.72rem;font-weight:600;">
                <i class="bi bi-lightning-charge me-1"></i>{{ $wf->trigger_event?->label() ?? $wf->trigger_event }}
              </span>
            </td>
            <td style="padding:.75rem 1rem;color:var(--text-muted);">
              {{ $wf->actions->count() }} action{{ $wf->actions->count() === 1 ? '' : 's' }}
            </td>
            <td style="padding:.75rem 1rem;color:var(--text-muted);">
              {{ $wf->runs->count() }}
            </td>
            <td style="padding:.75rem 1rem;">
              <form method="POST" action="{{ route('workflows.toggle', $wf) }}" style="display:inline;">
                @csrf @method('PATCH')
                <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm"
                        style="color:{{ $wf->is_active ? '#10b981' : '#94a3b8' }};">
                  <i class="bi {{ $wf->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}" style="font-size:1.1rem;"></i>
                  {{ $wf->is_active ? 'Active' : 'Inactive' }}
                </button>
              </form>
            </td>
            <td style="padding:.75rem 1rem;text-align:right;">
              <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('workflows.edit', $wf) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('workflows.destroy', $wf) }}" onsubmit="return confirm('Delete this workflow?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="padding:3rem;text-align:center;color:var(--text-muted);">
              <i class="bi bi-lightning-charge" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
              No workflows found. <a href="{{ route('workflows.create') }}">Create your first workflow</a>.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($workflows->hasPages())
  <div class="mt-3">{{ $workflows->links() }}</div>
@endif

@endsection
