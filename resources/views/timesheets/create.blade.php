@extends('layouts.app')

@section('title', $timesheet ? 'Edit Time Entry' : 'Log Time')
@section('page-title', $timesheet ? 'Edit Time Entry' : 'Log Time')

@section('breadcrumb-items')
  <a href="{{ route('timesheets.index') }}" style="color:inherit;text-decoration:none;">Timesheets</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $timesheet ? 'Edit' : 'Log Time' }}</span>
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-xl-6">

    <form method="POST" action="{{ $timesheet ? route('timesheets.update', $timesheet) : route('timesheets.store') }}">
      @csrf
      @if($timesheet) @method('PUT') @endif

      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-clock me-2" style="color:var(--ncv-blue-500);"></i>Time Entry</h6>
        </div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="ncv-label">Project <span class="text-danger">*</span></label>
            <select name="project_id" class="ncv-select @error('project_id') is-invalid @enderror" required>
              <option value="">Select project…</option>
              @foreach($projects as $p)
                <option value="{{ $p->id }}"
                  {{ old('project_id', $timesheet?->project_id ?? ($preselect ?? null)) == $p->id ? 'selected' : '' }}>
                  {{ $p->project_number }} — {{ $p->name }}
                </option>
              @endforeach
            </select>
            @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="ncv-label">User <span class="text-danger">*</span></label>
            <select name="user_id" class="ncv-select @error('user_id') is-invalid @enderror" required>
              <option value="">Select user…</option>
              @foreach($users as $u)
                <option value="{{ $u->id }}" {{ old('user_id', $timesheet?->user_id ?? auth()->id()) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
              @endforeach
            </select>
            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="ncv-label">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="ncv-input @error('date') is-invalid @enderror"
                     value="{{ old('date', $timesheet?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
              @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="ncv-label">Hours <span class="text-danger">*</span></label>
              <input type="number" name="hours" class="ncv-input @error('hours') is-invalid @enderror"
                     step="0.25" min="0.25" max="24"
                     value="{{ old('hours', $timesheet?->hours) }}" placeholder="e.g. 2.5" required>
              @error('hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="ncv-label">Description</label>
            <textarea name="description" class="ncv-input" rows="3" placeholder="What did you work on?">{{ old('description', $timesheet?->description) }}</textarea>
          </div>

          <div class="row g-3 mb-0">
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2">
                <input type="hidden" name="is_billable" value="0">
                <input type="checkbox" name="is_billable" id="is_billable" value="1" class="form-check-input"
                       {{ old('is_billable', $timesheet ? ($timesheet->is_billable ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
                <label for="is_billable" class="ncv-label mb-0">Billable</label>
              </div>
            </div>
            <div class="col-md-6">
              <label class="ncv-label">Billable Rate (per hour)</label>
              <div class="ncv-input-group">
                <span class="ncv-input-icon" style="font-size:.85rem;color:var(--text-muted);">$</span>
                <input type="number" name="billable_rate" class="ncv-input ncv-input-search"
                       step="0.01" min="0"
                       value="{{ old('billable_rate', $timesheet?->billable_rate) }}" placeholder="0.00">
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-check-lg"></i> {{ $timesheet ? 'Save Changes' : 'Log Time' }}
        </button>
        <a href="{{ $timesheet ? route('timesheets.show', $timesheet) : route('timesheets.index') }}" class="ncv-btn ncv-btn-ghost">Cancel</a>
        @if($timesheet)
        <form method="POST" action="{{ route('timesheets.destroy', $timesheet) }}" class="ms-auto" onsubmit="return confirm('Delete this entry?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-ghost" style="color:#ef4444;"><i class="bi bi-trash"></i> Delete</button>
        </form>
        @endif
      </div>

    </form>
  </div>
</div>
@endsection
