@extends('layouts.app')

@section('title', $project->name)
@section('page-title', 'Project Details')

@section('breadcrumb-items')
  <a href="{{ route('projects.index') }}" style="color:inherit;text-decoration:none;">Projects</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $project->project_number }}</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<style>
  .gantt-container { overflow-x: auto; }
  .gantt .bar-label { fill: #fff !important; font-size: 12px; }
  .gantt .bar { rx: 4; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="ncv-card mb-3" style="background:linear-gradient(135deg,#0d1f4e,#1e3a8f,#2563eb);border:none;color:#fff;overflow:hidden;position:relative;">
  <div style="position:absolute;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.04);top:-80px;right:-60px;"></div>
  <div class="ncv-card-body" style="padding:1.75rem;position:relative;z-index:1;">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.6);font-family:monospace;margin-bottom:.35rem;">{{ $project->project_number }}</div>
        <h1 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .5rem;">{{ $project->name }}</h1>
        <div style="display:flex;gap:1.25rem;font-size:.875rem;color:rgba(255,255,255,.75);flex-wrap:wrap;">
          @if($project->account)
          <span><i class="bi bi-building"></i> {{ $project->account->name }}</span>
          @endif
          @if($project->due_date)
          <span><i class="bi bi-calendar3"></i> Due: <strong style="color:#fff;">{{ $project->due_date->format('M d, Y') }}</strong></span>
          @endif
          @if($project->manager)
          <span><i class="bi bi-person"></i> Manager: {{ $project->manager->name }}</span>
          @endif
          <span class="ncv-badge ncv-badge-{{ $project->status->color() }}">{{ $project->status->label() }}</span>
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('projects.edit', $project) }}" class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
          <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('timesheets.create', ['project_id' => $project->id]) }}" class="ncv-btn ncv-btn-sm" style="background:#10b981;color:#fff;border:none;">
          <i class="bi bi-clock"></i> Log Time
        </a>
      </div>
    </div>

    {{-- Milestone Progress Bar --}}
    @php $pct = $project->milestone_progress; @endphp
    <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.1);">
      <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;font-size:.78rem;color:rgba(255,255,255,.75);">
        <span>Milestone Progress</span>
        <span>{{ $pct }}%</span>
      </div>
      <div style="height:8px;background:rgba(255,255,255,.15);border-radius:4px;overflow:hidden;">
        <div style="width:{{ $pct }}%;height:100%;background:{{ $pct===100?'#10b981':'#60a5fa' }};border-radius:4px;transition:width .4s;"></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  {{-- Left: Details + Members --}}
  <div class="col-12 col-lg-4">

    {{-- Project Details --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6>
      </div>
      <div class="ncv-card-body">
        @php
          $rows = [
            ['l'=>'Status',     'v'=>$project->status->label(), 'badge'=>$project->status->color()],
            ['l'=>'Start Date', 'v'=>$project->start_date?->format('M d, Y') ?? '—'],
            ['l'=>'Due Date',   'v'=>$project->due_date?->format('M d, Y') ?? '—'],
            ['l'=>'Budget',     'v'=>$project->budget ? number_format($project->budget,2).' '.$project->currency : '—'],
            ['l'=>'Account',    'v'=>$project->account?->name ?? '—'],
            ['l'=>'Contact',    'v'=>$project->contact ? $project->contact->first_name.' '.$project->contact->last_name : '—'],
          ];
          if($project->opportunity)
            $rows[] = ['l'=>'From Deal', 'v'=>$project->opportunity->name, 'link'=>route('opportunities.show', $project->opportunity)];
        @endphp
        @foreach($rows as $row)
        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.55rem 0;border-bottom:1px solid var(--border-color);font-size:.85rem;">
          <span style="min-width:85px;font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;padding-top:2px;">{{ $row['l'] }}</span>
          @if(isset($row['badge']))
            <span class="ncv-badge ncv-badge-{{ $row['badge'] }}">{{ $row['v'] }}</span>
          @elseif(isset($row['link']))
            <a href="{{ $row['link'] }}" style="color:var(--ncv-blue-600);font-weight:600;text-decoration:none;">{{ $row['v'] }}</a>
          @else
            <span style="color:var(--text-secondary);">{{ $row['v'] }}</span>
          @endif
        </div>
        @endforeach
      </div>
    </div>

    {{-- Time Summary --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-clock me-2" style="color:var(--ncv-blue-500);"></i>Time Summary</h6>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['label'=>'Total Hours',       'value'=>number_format($totalHours,1),       'color'=>'#3b82f6'],
          ['label'=>'Billable Hours',    'value'=>number_format($billableHours,1),    'color'=>'#10b981'],
          ['label'=>'Non-Billable Hrs',  'value'=>number_format($nonBillableHours,1), 'color'=>'#6366f1'],
        ] as $t)
        <div style="display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--border-color);">
          <span style="font-size:.82rem;color:var(--text-secondary);">{{ $t['label'] }}</span>
          <span style="font-weight:700;color:{{ $t['color'] }};">{{ $t['value'] }}h</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Team Members --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-people me-2" style="color:var(--ncv-blue-500);"></i>Team Members</h6>
        <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" data-bs-toggle="collapse" data-bs-target="#addMemberForm">
          <i class="bi bi-plus-lg"></i>
        </button>
      </div>
      <div class="collapse" id="addMemberForm">
        <div class="ncv-card-body" style="border-bottom:1px solid var(--border-color);">
          <form method="POST" action="{{ route('projects.members.add', $project) }}">
            @csrf
            <div class="mb-2">
              <select name="user_id" class="ncv-select" required>
                <option value="">Select user…</option>
                @foreach($allUsers as $u)
                  <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-7">
                <input type="text" name="role" class="ncv-input" placeholder="Role (e.g. Developer)">
              </div>
              <div class="col-5">
                <input type="number" name="allocated_hours" class="ncv-input" placeholder="Hrs" step="0.5" min="0">
              </div>
            </div>
            <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm w-100">Add Member</button>
          </form>
        </div>
      </div>
      <div class="ncv-card-body">
        @forelse($project->members as $member)
        <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.625rem;">
          <div style="width:34px;height:34px;border-radius:.5rem;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0;">
            {{ strtoupper(substr($member->name, 0, 2)) }}
          </div>
          <div style="flex:1;">
            <div style="font-size:.83rem;font-weight:600;color:var(--text-primary);">{{ $member->name }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);">
              {{ $member->pivot->role ?? 'Member' }}
              @if($member->pivot->allocated_hours)
                · {{ $member->pivot->allocated_hours }}h allocated
              @endif
            </div>
          </div>
          <form method="POST" action="{{ route('projects.members.remove', [$project, $member]) }}">
            @csrf @method('DELETE')
            <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-x-lg" style="font-size:.7rem;"></i></button>
          </form>
        </div>
        @empty
        <p class="text-muted" style="font-size:.85rem;margin:0;">No team members yet.</p>
        @endforelse
      </div>
    </div>

  </div>

  {{-- Right: Tabs (Milestones / Gantt / Timesheets) --}}
  <div class="col-12 col-lg-8">

    <div class="ncv-card">
      {{-- Tab Nav --}}
      <div class="ncv-card-header" style="padding-bottom:0;">
        <nav class="d-flex gap-1" style="border-bottom:none;">
          <button onclick="showTab('milestones')" id="tab-milestones" class="ncv-btn ncv-btn-ghost ncv-btn-sm tab-btn active-tab" style="border-radius:.35rem .35rem 0 0;border-bottom:2px solid var(--ncv-blue-600);">
            <i class="bi bi-flag"></i> Milestones
          </button>
          <button onclick="showTab('gantt')" id="tab-gantt" class="ncv-btn ncv-btn-ghost ncv-btn-sm tab-btn" style="border-radius:.35rem .35rem 0 0;">
            <i class="bi bi-bar-chart-steps"></i> Gantt
          </button>
          <button onclick="showTab('timesheets')" id="tab-timesheets" class="ncv-btn ncv-btn-ghost ncv-btn-sm tab-btn" style="border-radius:.35rem .35rem 0 0;">
            <i class="bi bi-clock-history"></i> Timesheets
          </button>
        </nav>
      </div>

      {{-- MILESTONES TAB --}}
      <div id="pane-milestones" class="tab-pane">
        <div class="ncv-card-body" style="border-bottom:1px solid var(--border-color);padding:.875rem 1.25rem;">
          <button class="ncv-btn ncv-btn-outline ncv-btn-sm" data-bs-toggle="collapse" data-bs-target="#addMilestoneForm">
            <i class="bi bi-plus-lg"></i> Add Milestone
          </button>
          <div class="collapse mt-3" id="addMilestoneForm">
            <form method="POST" action="{{ route('projects.milestones.store', $project) }}">
              @csrf
              <div class="row g-2">
                <div class="col-md-6">
                  <input type="text" name="title" class="ncv-input" placeholder="Milestone title" required>
                </div>
                <div class="col-md-3">
                  <input type="date" name="due_date" class="ncv-input" required>
                </div>
                <div class="col-md-3">
                  <select name="status" class="ncv-select">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
                <div class="col-12">
                  <textarea name="description" class="ncv-input" rows="2" placeholder="Description (optional)…"></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Save Milestone</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="ncv-card-body p-0">
          @forelse($project->milestones as $ms)
          <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid var(--border-color);">
            <div style="width:32px;height:32px;border-radius:50%;background:{{ $ms->status->color() === 'success' ? '#d1fae5' : ($ms->status->color() === 'primary' ? '#dbeafe' : '#f1f5f9') }};color:{{ $ms->status->color() === 'success' ? '#059669' : ($ms->status->color() === 'primary' ? '#2563eb' : '#64748b') }};display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0;">
              <i class="bi {{ $ms->status->value === 'completed' ? 'bi-check-lg' : ($ms->status->value === 'in_progress' ? 'bi-play-fill' : 'bi-circle') }}"></i>
            </div>
            <div style="flex:1;">
              <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);">{{ $ms->title }}</div>
              @if($ms->description)
              <div style="font-size:.78rem;color:var(--text-muted);">{{ $ms->description }}</div>
              @endif
            </div>
            <div style="text-align:right;flex-shrink:0;">
              <div style="font-size:.75rem;{{ $ms->due_date->isPast() && $ms->status->value !== 'completed' ? 'color:#ef4444;font-weight:700;' : 'color:var(--text-muted);' }}">
                {{ $ms->due_date->format('M d, Y') }}
              </div>
              <span class="ncv-badge ncv-badge-{{ $ms->status->color() }}" style="font-size:.65rem;">{{ $ms->status->label() }}</span>
            </div>
            <div class="d-flex gap-1">
              <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="editMilestone({{ $ms->id }}, '{{ addslashes($ms->title) }}', '{{ $ms->due_date->format('Y-m-d') }}', '{{ $ms->status->value }}', '{{ addslashes($ms->description ?? '') }}')">
                <i class="bi bi-pencil" style="font-size:.75rem;"></i>
              </button>
              <form method="POST" action="{{ route('projects.milestones.destroy', [$project, $ms]) }}" onsubmit="return confirm('Delete milestone?')">
                @csrf @method('DELETE')
                <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash" style="font-size:.75rem;"></i></button>
              </form>
            </div>
          </div>
          @empty
          <div class="text-center py-4 text-muted">No milestones yet. Add one above.</div>
          @endforelse
        </div>
      </div>

      {{-- GANTT TAB --}}
      <div id="pane-gantt" class="tab-pane" style="display:none;">
        <div class="ncv-card-body">
          @if($project->milestones->count() > 0)
          <div class="gantt-container">
            <svg id="gantt"></svg>
          </div>
          @else
          <div class="text-center py-4 text-muted">Add milestones to view the Gantt chart.</div>
          @endif
        </div>
      </div>

      {{-- TIMESHEETS TAB --}}
      <div id="pane-timesheets" class="tab-pane" style="display:none;">
        <div class="ncv-card-body" style="border-bottom:1px solid var(--border-color);">
          <a href="{{ route('timesheets.create', ['project_id' => $project->id]) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> Log Time
          </a>
        </div>
        <div class="ncv-card-body p-0">
          <div class="table-responsive">
            <table class="ncv-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>User</th>
                  <th>Hours</th>
                  <th>Billable</th>
                  <th>Description</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($project->timesheets->sortByDesc('date') as $ts)
                <tr>
                  <td style="white-space:nowrap;">{{ $ts->date->format('M d, Y') }}</td>
                  <td>{{ $ts->user?->name ?? '—' }}</td>
                  <td><strong>{{ $ts->hours }}h</strong></td>
                  <td>
                    @if($ts->is_billable)
                      <span class="ncv-badge ncv-badge-success" style="font-size:.65rem;">Billable</span>
                    @else
                      <span class="ncv-badge ncv-badge-secondary" style="font-size:.65rem;">Non-Bill.</span>
                    @endif
                  </td>
                  <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ts->description ?? '—' }}</td>
                  <td class="text-end">
                    <a href="{{ route('timesheets.edit', $ts) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm"><i class="bi bi-pencil" style="font-size:.75rem;"></i></a>
                    <form method="POST" action="{{ route('timesheets.destroy', $ts) }}" style="display:inline;" onsubmit="return confirm('Delete entry?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash" style="font-size:.75rem;"></i></button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-3 text-muted">No time entries yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

{{-- Edit Milestone Modal --}}
<div class="modal fade" id="editMilestoneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Milestone</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editMilestoneForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="ncv-label">Title</label>
            <input type="text" name="title" id="ms-title" class="ncv-input" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="ncv-label">Due Date</label>
              <input type="date" name="due_date" id="ms-due" class="ncv-input" required>
            </div>
            <div class="col-6">
              <label class="ncv-label">Status</label>
              <select name="status" id="ms-status" class="ncv-select">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>
          <div class="mb-0">
            <label class="ncv-label">Description</label>
            <textarea name="description" id="ms-desc" class="ncv-input" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="ncv-btn ncv-btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="ncv-btn ncv-btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.umd.js"></script>
<script>
// Tab switching
function showTab(name) {
  document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(b => {
    b.style.borderBottom = 'none';
    b.style.fontWeight   = '';
  });
  document.getElementById('pane-' + name).style.display = '';
  const btn = document.getElementById('tab-' + name);
  btn.style.borderBottom = '2px solid var(--ncv-blue-600)';
  btn.style.fontWeight   = '700';

  if (name === 'gantt') initGantt();
}

// Gantt
let ganttInited = false;
function initGantt() {
  if (ganttInited) return;
  ganttInited = true;
  @if($project->milestones->count() > 0)
  const tasks = [
    @foreach($project->milestones as $i => $ms)
    {
      id: 'ms{{ $ms->id }}',
      name: '{{ addslashes($ms->title) }}',
      start: '{{ $ms->due_date->copy()->subDays(7)->format('Y-m-d') }}',
      end:   '{{ $ms->due_date->format('Y-m-d') }}',
      progress: {{ $ms->status->value === 'completed' ? 100 : ($ms->status->value === 'in_progress' ? 50 : 0) }},
      dependencies: {{ $i > 0 ? '"ms'.$project->milestones->get($i - 1)->id.'"' : '""' }},
    },
    @endforeach
  ];
  try {
    new Gantt('#gantt', tasks, { view_mode: 'Week', date_format: 'YYYY-MM-DD', bar_height: 30, padding: 18 });
  } catch(e) { console.error('Gantt error:', e); }
  @endif
}

// Edit milestone modal
function editMilestone(id, title, due, status, desc) {
  document.getElementById('editMilestoneForm').action =
    '{{ url('/projects/'.$project->id.'/milestones') }}/' + id;
  document.getElementById('ms-title').value  = title;
  document.getElementById('ms-due').value    = due;
  document.getElementById('ms-status').value = status;
  document.getElementById('ms-desc').value   = desc;
  new bootstrap.Modal(document.getElementById('editMilestoneModal')).show();
}
</script>
@endpush
