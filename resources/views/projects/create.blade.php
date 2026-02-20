@extends('layouts.app')

@section('title', $project ? 'Edit Project' : 'New Project')
@section('page-title', $project ? 'Edit Project' : 'New Project')

@section('breadcrumb-items')
  <a href="{{ route('projects.index') }}" style="color:inherit;text-decoration:none;">Projects</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $project ? 'Edit' : 'New Project' }}</span>
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-xl-9">

    <form method="POST" action="{{ $project ? route('projects.update', $project) : route('projects.store') }}">
      @csrf
      @if($project) @method('PUT') @endif

      <div class="row g-3">

        {{-- Main Details --}}
        <div class="col-12 col-lg-8">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-kanban me-2" style="color:var(--ncv-blue-500);"></i>Project Details</h6>
            </div>
            <div class="ncv-card-body">

              <div class="mb-3">
                <label class="ncv-label">Project Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="ncv-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $project?->name) }}" placeholder="e.g. Website Redesign" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-3">
                <label class="ncv-label">Description</label>
                <textarea name="description" class="ncv-input" rows="4" placeholder="Project scope and objectives…">{{ old('description', $project?->description) }}</textarea>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="ncv-label">Start Date</label>
                  <input type="date" name="start_date" class="ncv-input"
                         value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                  <label class="ncv-label">Due Date</label>
                  <input type="date" name="due_date" class="ncv-input @error('due_date') is-invalid @enderror"
                         value="{{ old('due_date', $project?->due_date?->format('Y-m-d')) }}">
                  @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

            </div>
          </div>
        </div>

        {{-- Sidebar Options --}}
        <div class="col-12 col-lg-4">

          {{-- Status & Manager --}}
          <div class="ncv-card mb-3">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-sliders me-2" style="color:var(--ncv-blue-500);"></i>Settings</h6>
            </div>
            <div class="ncv-card-body">

              <div class="mb-3">
                <label class="ncv-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="ncv-select @error('status') is-invalid @enderror" required>
                  @foreach(\App\Enums\ProjectStatus::cases() as $s)
                    <option value="{{ $s->value }}" {{ old('status', $project?->status->value ?? 'planning') === $s->value ? 'selected' : '' }}>
                      {{ $s->label() }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="ncv-label">Project Manager</label>
                <select name="manager_id" class="ncv-select">
                  <option value="">Assign manager…</option>
                  @foreach($managers as $m)
                    <option value="{{ $m->id }}" {{ old('manager_id', $project?->manager_id) == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="row g-2">
                <div class="col-8">
                  <label class="ncv-label">Budget</label>
                  <input type="number" name="budget" class="ncv-input" step="0.01" min="0"
                         value="{{ old('budget', $project?->budget) }}" placeholder="0.00">
                </div>
                <div class="col-4">
                  <label class="ncv-label">Currency</label>
                  <input type="text" name="currency" class="ncv-input" maxlength="3"
                         value="{{ old('currency', $project?->currency ?? 'USD') }}" style="text-transform:uppercase;">
                </div>
              </div>

            </div>
          </div>

          {{-- Linked Records --}}
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-link-45deg me-2" style="color:var(--ncv-blue-500);"></i>Linked Records</h6>
            </div>
            <div class="ncv-card-body">

              <div class="mb-3">
                <label class="ncv-label">Opportunity</label>
                <select name="opportunity_id" class="ncv-select">
                  <option value="">Link to opportunity…</option>
                  @foreach($opportunities as $opp)
                    <option value="{{ $opp->id }}" {{ old('opportunity_id', $project?->opportunity_id) == $opp->id ? 'selected' : '' }}>{{ $opp->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="ncv-label">Account</label>
                <select name="account_id" class="ncv-select">
                  <option value="">Link to account…</option>
                  @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('account_id', $project?->account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-0">
                <label class="ncv-label">Contact</label>
                <select name="contact_id" class="ncv-select">
                  <option value="">Link to contact…</option>
                  @foreach($contacts as $c)
                    <option value="{{ $c->id }}" {{ old('contact_id', $project?->contact_id) == $c->id ? 'selected' : '' }}>
                      {{ $c->first_name }} {{ $c->last_name }}
                    </option>
                  @endforeach
                </select>
              </div>

            </div>
          </div>

        </div>
      </div>

      {{-- Form Actions --}}
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-check-lg"></i> {{ $project ? 'Save Changes' : 'Create Project' }}
        </button>
        <a href="{{ $project ? route('projects.show', $project) : route('projects.index') }}" class="ncv-btn ncv-btn-ghost">
          Cancel
        </a>
        @if($project)
        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="ms-auto" onsubmit="return confirm('Delete this project?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-ghost" style="color:#ef4444;"><i class="bi bi-trash"></i> Delete</button>
        </form>
        @endif
      </div>

    </form>
  </div>
</div>
@endsection
