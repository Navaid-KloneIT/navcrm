@extends('layouts.app')

@section('title', $pipeline ? 'Edit Pipeline' : 'New Pipeline')
@section('page-title', $pipeline ? 'Edit Pipeline' : 'New Pipeline')

@section('breadcrumb-items')
  <a href="{{ route('success.onboarding.index') }}" style="color:inherit;text-decoration:none;">Onboarding Pipelines</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $pipeline ? 'Edit' : 'New Pipeline' }}</span>
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-xl-9">

    <form method="POST" action="{{ $pipeline ? route('success.onboarding.update', $pipeline) : route('success.onboarding.store') }}">
      @csrf
      @if($pipeline) @method('PUT') @endif

      <div class="row g-3">

        {{-- Main Details --}}
        <div class="col-12 col-lg-8">

          {{-- Pipeline Details --}}
          <div class="ncv-card mb-3">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-clipboard-check me-2" style="color:var(--ncv-blue-500);"></i>Pipeline Details</h6>
            </div>
            <div class="ncv-card-body">

              <div class="mb-3">
                <label class="ncv-label">Pipeline Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="ncv-input @error('name') is-invalid @enderror"
                       value="{{ old('name', $pipeline?->name) }}" placeholder="e.g. Enterprise Onboarding" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-0">
                <label class="ncv-label">Description</label>
                <textarea name="description" class="ncv-input" rows="4" placeholder="Onboarding scope and objectives...">{{ old('description', $pipeline?->description) }}</textarea>
              </div>

            </div>
          </div>

          {{-- Steps --}}
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-list-check me-2" style="color:var(--ncv-blue-500);"></i>Steps</h6>
              <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="addStep()">
                <i class="bi bi-plus-lg"></i> Add Step
              </button>
            </div>
            <div class="ncv-card-body" id="stepsContainer">

              @php
                $existingSteps = old('steps', $pipeline?->steps?->map(fn($s) => [
                  'title'       => $s->title,
                  'description' => $s->description,
                  'due_date'    => $s->due_date?->format('Y-m-d'),
                ])->toArray() ?? []);
              @endphp

              @forelse($existingSteps as $i => $step)
              <div class="step-row" id="step{{ $i }}" style="padding:.75rem;border:1px solid var(--border-color);border-radius:.625rem;margin-bottom:.625rem;">
                <div class="d-flex align-items-start gap-2">
                  <div style="flex:1;">
                    <div class="row g-2">
                      <div class="col-12">
                        <input type="text" name="steps[{{ $i }}][title]" class="ncv-input" placeholder="Step title *" value="{{ $step['title'] ?? '' }}" required>
                      </div>
                      <div class="col-md-8">
                        <input type="text" name="steps[{{ $i }}][description]" class="ncv-input" placeholder="Description (optional)" value="{{ $step['description'] ?? '' }}">
                      </div>
                      <div class="col-md-4">
                        <input type="date" name="steps[{{ $i }}][due_date]" class="ncv-input" value="{{ $step['due_date'] ?? '' }}">
                      </div>
                    </div>
                  </div>
                  <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="removeStep({{ $i }})" style="color:#ef4444;margin-top:2px;">
                    <i class="bi bi-x-lg" style="font-size:.8rem;"></i>
                  </button>
                </div>
              </div>
              @empty
              <p class="text-muted text-center" style="font-size:.85rem;margin:0;" id="noStepsMsg">No steps added yet. Click "Add Step" to begin.</p>
              @endforelse

            </div>
          </div>

        </div>

        {{-- Sidebar Options --}}
        <div class="col-12 col-lg-4">

          {{-- Settings --}}
          <div class="ncv-card mb-3">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-sliders me-2" style="color:var(--ncv-blue-500);"></i>Settings</h6>
            </div>
            <div class="ncv-card-body">

              <div class="mb-3">
                <label class="ncv-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="ncv-select @error('status') is-invalid @enderror" required>
                  @foreach(\App\Enums\OnboardingStatus::cases() as $s)
                    <option value="{{ $s->value }}" {{ old('status', $pipeline?->status->value ?? 'not_started') === $s->value ? 'selected' : '' }}>
                      {{ $s->label() }}
                    </option>
                  @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-3">
                <label class="ncv-label">Assignee</label>
                <select name="assigned_to" class="ncv-select">
                  <option value="">Assign to...</option>
                  @foreach($assignees as $a)
                    <option value="{{ $a->id }}" {{ old('assigned_to', $pipeline?->assigned_to) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-0">
                <label class="ncv-label">Due Date</label>
                <input type="date" name="due_date" class="ncv-input @error('due_date') is-invalid @enderror"
                       value="{{ old('due_date', $pipeline?->due_date?->format('Y-m-d')) }}">
                @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <label class="ncv-label">Account <span class="text-danger">*</span></label>
                <select name="account_id" class="ncv-select @error('account_id') is-invalid @enderror" required>
                  <option value="">Select account...</option>
                  @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('account_id', $pipeline?->account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                  @endforeach
                </select>
                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="mb-0">
                <label class="ncv-label">Contact</label>
                <select name="contact_id" class="ncv-select">
                  <option value="">Link to contact...</option>
                  @foreach($contacts as $c)
                    <option value="{{ $c->id }}" {{ old('contact_id', $pipeline?->contact_id) == $c->id ? 'selected' : '' }}>
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
          <i class="bi bi-check-lg"></i> {{ $pipeline ? 'Save Changes' : 'Create Pipeline' }}
        </button>
        <a href="{{ $pipeline ? route('success.onboarding.show', $pipeline) : route('success.onboarding.index') }}" class="ncv-btn ncv-btn-ghost">
          Cancel
        </a>
        @if($pipeline)
        <form method="POST" action="{{ route('success.onboarding.destroy', $pipeline) }}" class="ms-auto" onsubmit="return confirm('Delete this pipeline?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-ghost" style="color:#ef4444;"><i class="bi bi-trash"></i> Delete</button>
        </form>
        @endif
      </div>

    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let stepCount = {{ count($existingSteps) }};

  function addStep() {
    const noMsg = document.getElementById('noStepsMsg');
    if (noMsg) noMsg.remove();

    const idx = stepCount++;
    const row = document.createElement('div');
    row.className = 'step-row';
    row.id = 'step' + idx;
    row.style.cssText = 'padding:.75rem;border:1px solid var(--border-color);border-radius:.625rem;margin-bottom:.625rem;';
    row.innerHTML = `
      <div class="d-flex align-items-start gap-2">
        <div style="flex:1;">
          <div class="row g-2">
            <div class="col-12">
              <input type="text" name="steps[${idx}][title]" class="ncv-input" placeholder="Step title *" required>
            </div>
            <div class="col-md-8">
              <input type="text" name="steps[${idx}][description]" class="ncv-input" placeholder="Description (optional)">
            </div>
            <div class="col-md-4">
              <input type="date" name="steps[${idx}][due_date]" class="ncv-input">
            </div>
          </div>
        </div>
        <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="removeStep(${idx})" style="color:#ef4444;margin-top:2px;">
          <i class="bi bi-x-lg" style="font-size:.8rem;"></i>
        </button>
      </div>`;
    document.getElementById('stepsContainer').appendChild(row);
  }

  function removeStep(idx) {
    const el = document.getElementById('step' + idx);
    if (el) el.remove();

    // Show empty message if no steps remain
    if (document.querySelectorAll('.step-row').length === 0) {
      const msg = document.createElement('p');
      msg.className = 'text-muted text-center';
      msg.style.cssText = 'font-size:.85rem;margin:0;';
      msg.id = 'noStepsMsg';
      msg.textContent = 'No steps added yet. Click "Add Step" to begin.';
      document.getElementById('stepsContainer').appendChild(msg);
    }
  }
</script>
@endpush
