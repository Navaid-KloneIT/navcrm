@extends('layouts.app')

@section('title', 'Workload')
@section('page-title', 'Resource Workload')

@section('breadcrumb-items')
  <a href="{{ route('timesheets.index') }}" style="color:inherit;text-decoration:none;">Timesheets</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Workload</span>
@endsection

@section('content')

{{-- Month Filter --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
      <label class="ncv-label mb-0"><i class="bi bi-calendar3 me-1"></i> Month:</label>
      <input type="month" name="month" value="{{ $month }}" class="ncv-input" style="width:160px;" onchange="this.form.submit()">
      <span style="font-size:.85rem;color:var(--text-muted);">
        {{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }}
      </span>
    </form>
  </div>
</div>

{{-- Workload Table --}}
<div class="ncv-card">
  <div class="ncv-card-header">
    <h6 class="ncv-card-title"><i class="bi bi-people me-2" style="color:var(--ncv-blue-500);"></i>Team Workload — {{ $startDate->format('F Y') }}</h6>
  </div>
  <div class="ncv-card-body p-0">
    <div class="table-responsive">
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Team Member</th>
            <th>Active Projects</th>
            <th>Allocated Hrs</th>
            <th>Logged Hrs ({{ $startDate->format('M Y') }})</th>
            <th>Utilization</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
          @php
            $allocated = \App\Models\Project::join('project_members', 'projects.id', '=', 'project_members.project_id')
              ->where('project_members.user_id', $user->id)
              ->whereNotIn('projects.status', ['completed', 'cancelled'])
              ->sum('project_members.allocated_hours');

            $activeProjects = \App\Models\Project::join('project_members', 'projects.id', '=', 'project_members.project_id')
              ->where('project_members.user_id', $user->id)
              ->whereNotIn('projects.status', ['completed', 'cancelled'])
              ->select('projects.id', 'projects.name', 'projects.project_number')
              ->get();

            $logged = (float) ($loggedHours[$user->id] ?? 0);
            $utilization = $allocated > 0 ? round($logged / $allocated * 100) : null;
            $barColor = $utilization === null ? '#94a3b8' : ($utilization > 100 ? '#ef4444' : ($utilization >= 80 ? '#f59e0b' : '#10b981'));
          @endphp
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:.5rem;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0;">
                  {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                  <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);">{{ $user->name }}</div>
                  <div style="font-size:.72rem;color:var(--text-muted);">{{ $user->email }}</div>
                </div>
              </div>
            </td>
            <td>
              @if($activeProjects->isNotEmpty())
                @foreach($activeProjects as $proj)
                  <a href="{{ route('projects.show', $proj->id) }}" style="display:block;font-size:.78rem;color:var(--ncv-blue-600);text-decoration:none;">
                    {{ $proj->project_number }}
                  </a>
                @endforeach
              @else
                <span class="text-muted" style="font-size:.82rem;">None</span>
              @endif
            </td>
            <td>
              <strong>{{ $allocated > 0 ? number_format((float)$allocated,1).'h' : '—' }}</strong>
            </td>
            <td>
              <strong style="color:{{ $logged > 0 ? 'var(--text-primary)' : 'var(--text-muted)' }};">{{ $logged > 0 ? number_format($logged,1).'h' : '0h' }}</strong>
            </td>
            <td style="min-width:160px;">
              @if($utilization !== null)
              <div class="d-flex align-items-center gap-2">
                <div style="flex:1;height:8px;border-radius:4px;background:var(--border-color);overflow:hidden;">
                  <div style="width:{{ min($utilization, 100) }}%;height:100%;background:{{ $barColor }};border-radius:4px;"></div>
                </div>
                <span style="font-size:.78rem;font-weight:700;color:{{ $barColor }};min-width:40px;">{{ $utilization }}%</span>
              </div>
              @if($utilization > 100)
              <div style="font-size:.7rem;color:#ef4444;margin-top:.15rem;"><i class="bi bi-exclamation-triangle-fill"></i> Overbooked</div>
              @endif
              @else
              <span class="text-muted" style="font-size:.8rem;">No allocation</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">No active users found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Legend --}}
<div class="d-flex gap-3 mt-3" style="font-size:.78rem;color:var(--text-muted);">
  <div class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:50%;background:#10b981;display:inline-block;"></span> Under 80%</div>
  <div class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:50%;background:#f59e0b;display:inline-block;"></span> 80–100%</div>
  <div class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:50%;background:#ef4444;display:inline-block;"></span> Over 100% (Overbooked)</div>
</div>

@endsection
