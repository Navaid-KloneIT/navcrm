@extends('layouts.app')

@section('title', isset($task) ? 'Edit Task' : 'New Task')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
      {{ isset($task) ? 'Edit Task' : 'New Task' }}
    </h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ isset($task) ? 'Update task details.' : 'Create a new task or to-do item.' }}
    </p>
  </div>
  <a href="{{ route('activity.tasks.index') }}" class="btn btn-ghost btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

@if($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST"
      action="{{ isset($task) ? route('activity.tasks.update', $task) : route('activity.tasks.store') }}">
  @csrf
  @if(isset($task)) @method('PUT') @endif

  <div class="row g-4">

    {{-- Main Fields --}}
    <div class="col-12 col-lg-8">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Task Details</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $task->title ?? '') }}" required placeholder="Task title…">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Description</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                      rows="4" placeholder="Additional notes or details…">{{ old('description', $task->description ?? '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="row g-3">
            <div class="col-6">
              <label class="form-label fw-medium">Due Date</label>
              <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                     value="{{ old('due_date', isset($task->due_date) ? $task->due_date->format('Y-m-d') : '') }}">
              @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6">
              <label class="form-label fw-medium">Due Time</label>
              <input type="time" name="due_time" class="form-control @error('due_time') is-invalid @enderror"
                     value="{{ old('due_time', $task->due_time ?? '') }}">
              @error('due_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

        </div>
      </div>

      {{-- Recurrence --}}
      <div class="ncv-card mt-4">
        <div class="ncv-card-header d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-semibold">Recurrence</h6>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="isRecurring" name="is_recurring"
                   value="1" {{ old('is_recurring', $task->is_recurring ?? false) ? 'checked' : '' }}
                   onchange="document.getElementById('recurFields').classList.toggle('d-none', !this.checked)">
            <label class="form-check-label small" for="isRecurring">Recurring Task</label>
          </div>
        </div>
        <div class="ncv-card-body" id="recurFields"
             class="{{ old('is_recurring', $task->is_recurring ?? false) ? '' : 'd-none' }}">

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-medium">Repeat Every</label>
              <div class="input-group">
                <input type="number" name="recurrence_interval" class="form-control"
                       min="1" max="99" value="{{ old('recurrence_interval', $task->recurrence_interval ?? 1) }}">
                <select name="recurrence_type" class="form-select">
                  @foreach(\App\Enums\TaskRecurrence::cases() as $r)
                    <option value="{{ $r->value }}"
                      {{ old('recurrence_type', $task->recurrence_type?->value ?? '') === $r->value ? 'selected' : '' }}>
                      {{ $r->label() }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">Ends On</label>
              <input type="date" name="recurrence_ends_at" class="form-control"
                     value="{{ old('recurrence_ends_at', isset($task->recurrence_ends_at) ? $task->recurrence_ends_at->format('Y-m-d') : '') }}">
            </div>
          </div>

        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-12 col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Properties</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Priority <span class="text-danger">*</span></label>
            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
              @foreach(\App\Enums\TaskPriority::cases() as $p)
                <option value="{{ $p->value }}" {{ old('priority', $task->priority?->value ?? 'medium') === $p->value ? 'selected' : '' }}>
                  {{ $p->label() }}
                </option>
              @endforeach
            </select>
            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
              @foreach(\App\Enums\TaskStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ old('status', $task->status?->value ?? 'pending') === $s->value ? 'selected' : '' }}>
                  {{ $s->label() }}
                </option>
              @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Assigned To</label>
            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
              <option value="">Unassigned</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to ?? '') == $user->id ? 'selected' : '' }}>
                  {{ $user->name }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <div class="ncv-card mt-4">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Related Record</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Relate To</label>
            <select id="taskableType" name="taskable_type" class="form-select"
                    onchange="updateTaskableSelect(this.value)">
              <option value="">— None —</option>
              <option value="App\Models\Contact"     {{ old('taskable_type', $task->taskable_type ?? '') === 'App\Models\Contact'     ? 'selected' : '' }}>Contact</option>
              <option value="App\Models\Account"     {{ old('taskable_type', $task->taskable_type ?? '') === 'App\Models\Account'     ? 'selected' : '' }}>Account</option>
              <option value="App\Models\Opportunity" {{ old('taskable_type', $task->taskable_type ?? '') === 'App\Models\Opportunity' ? 'selected' : '' }}>Opportunity</option>
            </select>
          </div>

          <div id="taskableIdWrapper">
            <div id="contactSelect" class="taskable-select d-none">
              <label class="form-label fw-medium">Contact</label>
              <select name="taskable_id" class="form-select">
                <option value="">Select contact…</option>
                @foreach($contacts as $c)
                  <option value="{{ $c->id }}" {{ old('taskable_id', $task->taskable_id ?? '') == $c->id && old('taskable_type', $task->taskable_type ?? '') === 'App\Models\Contact' ? 'selected' : '' }}>
                    {{ $c->first_name }} {{ $c->last_name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div id="accountSelect" class="taskable-select d-none">
              <label class="form-label fw-medium">Account</label>
              <select name="taskable_id" class="form-select">
                <option value="">Select account…</option>
                @foreach($accounts as $a)
                  <option value="{{ $a->id }}" {{ old('taskable_id', $task->taskable_id ?? '') == $a->id && old('taskable_type', $task->taskable_type ?? '') === 'App\Models\Account' ? 'selected' : '' }}>
                    {{ $a->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div id="opportunitySelect" class="taskable-select d-none">
              <label class="form-label fw-medium">Opportunity</label>
              <select name="taskable_id" class="form-select">
                <option value="">Select opportunity…</option>
                @foreach($opportunities as $o)
                  <option value="{{ $o->id }}" {{ old('taskable_id', $task->taskable_id ?? '') == $o->id && old('taskable_type', $task->taskable_type ?? '') === 'App\Models\Opportunity' ? 'selected' : '' }}>
                    {{ $o->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

        </div>
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          {{ isset($task) ? 'Update Task' : 'Create Task' }}
        </button>
        <a href="{{ route('activity.tasks.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </div>

  </div>
</form>

@push('scripts')
<script>
function updateTaskableSelect(type) {
  document.querySelectorAll('.taskable-select').forEach(el => el.classList.add('d-none'));
  if (type === 'App\\Models\\Contact')     document.getElementById('contactSelect').classList.remove('d-none');
  if (type === 'App\\Models\\Account')     document.getElementById('accountSelect').classList.remove('d-none');
  if (type === 'App\\Models\\Opportunity') document.getElementById('opportunitySelect').classList.remove('d-none');
}
// Init on load
updateTaskableSelect(document.getElementById('taskableType').value);

// Recurrence toggle init
const isRecurring = document.getElementById('isRecurring');
document.getElementById('recurFields').classList.toggle('d-none', !isRecurring.checked);
</script>
@endpush

@endsection
