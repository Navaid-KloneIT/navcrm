@extends('layouts.app')

@section('title', 'Tasks')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Tasks</h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">Manage your to-do list and track due items.</p>
  </div>
  <a href="{{ route('activity.tasks.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> New Task
  </a>
</div>

{{-- Status Summary --}}
<div class="row g-3 mb-4">
  @php
    $statCards = [
      ['label' => 'Pending',     'key' => 'pending',     'color' => '#6366f1'],
      ['label' => 'In Progress', 'key' => 'in_progress', 'color' => '#3b82f6'],
      ['label' => 'Completed',   'key' => 'completed',   'color' => '#10b981'],
      ['label' => 'Cancelled',   'key' => 'cancelled',   'color' => '#ef4444'],
    ];
  @endphp
  @foreach($statCards as $card)
  <div class="col-6 col-md-3">
    <div class="ncv-card p-3 text-center">
      <div class="fw-bold" style="font-size:1.5rem;color:{{ $card['color'] }};">
        {{ $statusCounts[$card['key']] ?? 0 }}
      </div>
      <div style="font-size:.8rem;color:var(--text-muted);">{{ $card['label'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search tasks…" value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          @foreach(\App\Enums\TaskStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="priority" class="form-select form-select-sm">
          <option value="">All Priorities</option>
          @foreach(\App\Enums\TaskPriority::cases() as $p)
            <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="due" class="form-select form-select-sm">
          <option value="">Any Due Date</option>
          <option value="today"    {{ request('due') === 'today'    ? 'selected' : '' }}>Due Today</option>
          <option value="overdue"  {{ request('due') === 'overdue'  ? 'selected' : '' }}>Overdue</option>
          <option value="upcoming" {{ request('due') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="assigned_to" class="form-select form-select-sm">
          <option value="">All Assignees</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-1">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($tasks->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-check2-square" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No tasks found</p>
        <p class="small mb-3">Create your first task to get started.</p>
        <a href="{{ route('activity.tasks.create') }}" class="btn btn-primary btn-sm">New Task</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle);border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Task</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Priority</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Status</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Due Date</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Assignee</th>
              <th class="py-3 pe-4"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($tasks as $task)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <div class="fw-medium" style="color:var(--text-primary);">
                  {{ $task->title }}
                  @if($task->is_recurring)
                    <i class="bi bi-arrow-repeat ms-1" style="color:var(--text-muted);font-size:.75rem;" title="Recurring"></i>
                  @endif
                </div>
                @if($task->taskable)
                  <div style="color:var(--text-muted);font-size:.78rem;">
                    <i class="bi bi-link-45deg"></i>
                    {{ class_basename($task->taskable_type) }}:
                    {{ $task->taskable->full_name ?? $task->taskable->name ?? '—' }}
                  </div>
                @endif
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $task->priority->color() }}-subtle text-{{ $task->priority->color() }} border border-{{ $task->priority->color() }}-subtle"
                      style="font-size:.72rem;">{{ $task->priority->label() }}</span>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $task->status->color() }}-subtle text-{{ $task->status->color() }} border border-{{ $task->status->color() }}-subtle"
                      style="font-size:.72rem;">{{ $task->status->label() }}</span>
              </td>
              <td class="py-3" style="font-size:.8rem;">
                @if($task->due_date)
                  @if($task->isOverdue())
                    <span class="text-danger fw-medium">
                      <i class="bi bi-exclamation-circle me-1"></i>{{ $task->due_date->format('M j, Y') }}
                    </span>
                  @elseif($task->isDueToday())
                    <span class="text-warning fw-medium">
                      <i class="bi bi-clock me-1"></i>Today
                    </span>
                  @else
                    <span style="color:var(--text-muted);">{{ $task->due_date->format('M j, Y') }}</span>
                  @endif
                @else
                  <span style="color:var(--text-muted);">—</span>
                @endif
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $task->assignee?->name ?? '—' }}
              </td>
              <td class="py-3 pe-4">
                <a href="{{ route('activity.tasks.show', $task) }}" class="btn btn-ghost btn-sm" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('activity.tasks.edit', $task) }}" class="btn btn-ghost btn-sm" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="{{ route('activity.tasks.destroy', $task) }}" class="d-inline"
                      onsubmit="return confirm('Delete this task?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-ghost btn-sm text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($tasks->hasPages())
      <div class="d-flex justify-content-center px-4 py-3" style="border-top:1px solid var(--border-color);">
        {{ $tasks->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
