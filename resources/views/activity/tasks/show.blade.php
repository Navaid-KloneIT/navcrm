@extends('layouts.app')

@section('title', $task->title)

@section('content')

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="d-flex align-items-start justify-content-between mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="h4 fw-semibold mb-0" style="color:var(--text-primary);">{{ $task->title }}</h1>
      <span class="badge bg-{{ $task->priority->color() }}-subtle text-{{ $task->priority->color() }} border border-{{ $task->priority->color() }}-subtle">
        {{ $task->priority->label() }}
      </span>
      <span class="badge bg-{{ $task->status->color() }}-subtle text-{{ $task->status->color() }} border border-{{ $task->status->color() }}-subtle">
        {{ $task->status->label() }}
      </span>
      @if($task->isOverdue())
        <span class="badge bg-danger text-white">Overdue</span>
      @elseif($task->isDueToday())
        <span class="badge bg-warning text-dark">Due Today</span>
      @endif
    </div>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      Created {{ $task->created_at->format('M j, Y') }}
      @if($task->creator) by {{ $task->creator->name }} @endif
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('activity.tasks.edit', $task) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('activity.tasks.destroy', $task) }}"
          onsubmit="return confirm('Delete this task?')">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Delete</button>
    </form>
  </div>
</div>

<div class="row g-4">

  {{-- Main --}}
  <div class="col-12 col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Description</h6></div>
      <div class="ncv-card-body">
        @if($task->description)
          <p style="white-space:pre-wrap;color:var(--text-secondary);line-height:1.7;">{{ $task->description }}</p>
        @else
          <p style="color:var(--text-muted);font-style:italic;">No description provided.</p>
        @endif
      </div>
    </div>

    @if($task->is_recurring)
    <div class="ncv-card mt-4">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold"><i class="bi bi-arrow-repeat me-2"></i>Recurrence</h6></div>
      <div class="ncv-card-body">
        <p class="mb-1">
          Repeats every
          <strong>{{ $task->recurrence_interval }}
          {{ $task->recurrence_type?->label() ?? '—' }}</strong>
        </p>
        @if($task->recurrence_ends_at)
          <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
            Ends on {{ $task->recurrence_ends_at->format('M j, Y') }}
          </p>
        @endif
      </div>
    </div>
    @endif
  </div>

  {{-- Sidebar --}}
  <div class="col-12 col-lg-4">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Details</h6></div>
      <div class="ncv-card-body p-0">
        <table class="table table-sm mb-0" style="font-size:.875rem;">
          <tbody>
            <tr>
              <td style="color:var(--text-muted);width:40%;">Priority</td>
              <td><span class="badge bg-{{ $task->priority->color() }}-subtle text-{{ $task->priority->color() }}">{{ $task->priority->label() }}</span></td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Status</td>
              <td><span class="badge bg-{{ $task->status->color() }}-subtle text-{{ $task->status->color() }}">{{ $task->status->label() }}</span></td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Due Date</td>
              <td>{{ $task->due_date ? $task->due_date->format('M j, Y') : '—' }}
                @if($task->due_time) at {{ $task->due_time }} @endif
              </td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Assigned To</td>
              <td>{{ $task->assignee?->name ?? '—' }}</td>
            </tr>
            @if($task->completed_at)
            <tr>
              <td style="color:var(--text-muted);">Completed</td>
              <td>{{ $task->completed_at->format('M j, Y g:i A') }}</td>
            </tr>
            @endif
            @if($task->taskable)
            <tr>
              <td style="color:var(--text-muted);">Linked To</td>
              <td>
                <span style="font-size:.8rem;color:var(--text-muted);">{{ class_basename($task->taskable_type) }}</span><br>
                <strong>{{ $task->taskable->full_name ?? $task->taskable->name ?? '—' }}</strong>
              </td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>

    {{-- Quick Status Change --}}
    <div class="ncv-card mt-4">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Change Status</h6></div>
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('activity.tasks.update', $task) }}">
          @csrf @method('PUT')
          <div class="d-grid gap-2">
            @foreach(\App\Enums\TaskStatus::cases() as $s)
              @if($s !== $task->status)
                <button type="submit" name="status" value="{{ $s->value }}"
                        class="btn btn-sm btn-outline-{{ $s->color() }}">
                  {{ $s->label() }}
                </button>
              @endif
            @endforeach
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

@endsection
