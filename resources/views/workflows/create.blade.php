@extends('layouts.app')

@section('title', $workflow ? 'Edit Workflow' : 'New Workflow')
@section('page-title', $workflow ? 'Edit Workflow' : 'New Workflow')

@section('breadcrumb-items')
  <a href="{{ route('workflows.index') }}" style="color:inherit;text-decoration:none;">Workflows</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $workflow ? 'Edit' : 'New' }}</span>
@endsection

@section('content')
<form method="POST" action="{{ $workflow ? route('workflows.update', $workflow) : route('workflows.store') }}">
  @csrf
  @if($workflow) @method('PUT') @endif

  <div class="row g-3">
    {{-- Left column --}}
    <div class="col-12 col-xl-8">

      {{-- Basic Info --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-lightning-charge me-2" style="color:var(--ncv-blue-500);"></i>Workflow Details</h6>
        </div>
        <div class="ncv-card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="ncv-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="ncv-input @error('name') is-invalid @enderror"
                     value="{{ old('name', $workflow?->name) }}" placeholder="e.g. New Lead Welcome Email" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
              <label class="ncv-label">Status</label>
              <select name="is_active" class="ncv-select">
                <option value="1" {{ old('is_active', $workflow?->is_active ?? true) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $workflow?->is_active ?? true) ? '' : 'selected' }}>Inactive</option>
              </select>
            </div>
          </div>
          <div class="mb-0">
            <label class="ncv-label">Description</label>
            <textarea name="description" class="ncv-input" rows="2" placeholder="What does this workflow do?">{{ old('description', $workflow?->description) }}</textarea>
          </div>
        </div>
      </div>

      {{-- Trigger --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-broadcast me-2" style="color:#10b981;"></i>Trigger</h6>
        </div>
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="ncv-label">Event <span class="text-danger">*</span></label>
            <select name="trigger_event" id="trigger-select" class="ncv-select @error('trigger_event') is-invalid @enderror" required>
              <option value="">Select a trigger…</option>
              @foreach($triggers as $t)
                <option value="{{ $t->value }}"
                  {{ old('trigger_event', $workflow?->trigger_event?->value) === $t->value ? 'selected' : '' }}>
                  {{ $t->label() }} — {{ $t->description() }}
                </option>
              @endforeach
            </select>
            @error('trigger_event') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Trigger config: shown per trigger --}}
          <div id="config-discount" class="trigger-config" style="display:none;">
            <label class="ncv-label">Discount Threshold (%)</label>
            <input type="number" name="trigger_config[discount_threshold]" class="ncv-input"
                   style="max-width:160px;"
                   value="{{ old('trigger_config.discount_threshold', $workflow?->trigger_config['discount_threshold'] ?? 10) }}"
                   min="0" max="100" step="0.01" placeholder="10">
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem;">Trigger when quote discount exceeds this percentage.</div>
          </div>
        </div>
      </div>

      {{-- Conditions --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header d-flex align-items-center justify-content-between">
          <h6 class="ncv-card-title mb-0"><i class="bi bi-filter me-2" style="color:#f59e0b;"></i>Conditions <span style="font-weight:400;font-size:.8rem;color:var(--text-muted);">(optional — ALL must match)</span></h6>
          <button type="button" id="add-condition" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> Add Condition
          </button>
        </div>
        <div class="ncv-card-body p-0">
          <table class="table mb-0" style="font-size:.875rem;">
            <thead>
              <tr style="border-bottom:1px solid var(--border-color);">
                <th style="padding:.6rem 1.25rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Field</th>
                <th style="padding:.6rem .75rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Operator</th>
                <th style="padding:.6rem .75rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Value</th>
                <th style="width:40px;"></th>
              </tr>
            </thead>
            <tbody id="conditions-body">
              @foreach(old('conditions', $workflow?->conditions->toArray() ?? []) as $i => $cond)
              <tr class="condition-row">
                <td style="padding:.5rem 1.25rem;">
                  <input type="text" name="conditions[{{ $i }}][field]" class="ncv-input ncv-input-sm"
                         value="{{ $cond['field'] }}" placeholder="e.g. status">
                </td>
                <td style="padding:.5rem .75rem;">
                  <select name="conditions[{{ $i }}][operator]" class="ncv-select ncv-select-sm">
                    @foreach(['eq'=>'=','neq'=>'≠','gt'=>'>','lt'=>'<','gte'=>'≥','lte'=>'≤','contains'=>'contains'] as $op => $label)
                      <option value="{{ $op }}" {{ $cond['operator'] === $op ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </td>
                <td style="padding:.5rem .75rem;">
                  <input type="text" name="conditions[{{ $i }}][value]" class="ncv-input ncv-input-sm"
                         value="{{ $cond['value'] }}" placeholder="value">
                </td>
                <td style="padding:.5rem .75rem;">
                  <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm remove-row" style="color:#ef4444;"><i class="bi bi-x"></i></button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div id="conditions-empty" style="padding:1rem 1.25rem;color:var(--text-muted);font-size:.82rem;{{ count(old('conditions', $workflow?->conditions->toArray() ?? [])) ? 'display:none' : '' }}">
            No conditions — workflow fires for all matching events.
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header d-flex align-items-center justify-content-between">
          <h6 class="ncv-card-title mb-0"><i class="bi bi-gear me-2" style="color:#6366f1;"></i>Actions <span class="text-danger">*</span></h6>
          <button type="button" id="add-action" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> Add Action
          </button>
        </div>
        <div class="ncv-card-body p-0" id="actions-body">
          @foreach(old('actions', $workflow?->actions->toArray() ?? []) as $i => $act)
          <div class="action-row" style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Action {{ $i + 1 }}</span>
              <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm remove-row ms-auto" style="color:#ef4444;"><i class="bi bi-x"></i> Remove</button>
            </div>
            <div class="mb-2">
              <label class="ncv-label">Type</label>
              <select name="actions[{{ $i }}][action_type]" class="ncv-select action-type-select" data-index="{{ $i }}">
                <option value="">Select action…</option>
                @foreach(['send_email'=>'Send Email','assign_user'=>'Assign User','change_status'=>'Change Status','send_webhook'=>'Send Webhook'] as $type => $label)
                  <option value="{{ $type }}" {{ ($act['action_type'] ?? '') === $type ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="action-config" data-index="{{ $i }}">
              @include('workflows._action_config', ['i' => $i, 'act' => $act, 'users' => $users])
            </div>
          </div>
          @endforeach
          <div id="actions-empty" style="padding:1rem 1.25rem;color:var(--text-muted);font-size:.82rem;{{ count(old('actions', $workflow?->actions->toArray() ?? [])) ? 'display:none' : '' }}">
            Add at least one action.
          </div>
        </div>
        @error('actions') <div class="text-danger p-3" style="font-size:.82rem;">{{ $message }}</div> @enderror
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-check-lg"></i> {{ $workflow ? 'Save Changes' : 'Create Workflow' }}
        </button>
        <a href="{{ $workflow ? route('workflows.show', $workflow) : route('workflows.index') }}" class="ncv-btn ncv-btn-ghost">Cancel</a>
      </div>
    </div>

    {{-- Right column: tips --}}
    <div class="col-12 col-xl-4">
      <div class="ncv-card" style="position:sticky;top:1rem;">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>How It Works</h6>
        </div>
        <div class="ncv-card-body" style="font-size:.82rem;line-height:1.6;">
          <p class="mb-2"><strong>1. Choose a trigger</strong> — the event that starts this workflow.</p>
          <p class="mb-2"><strong>2. Add conditions</strong> (optional) — all conditions must pass for actions to run.</p>
          <p class="mb-3"><strong>3. Add actions</strong> — what happens when triggered.</p>
          <hr>
          <div style="font-weight:700;color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;">Field Examples</div>
          <div style="font-family:monospace;font-size:.78rem;line-height:2;">
            <div>status &nbsp;&nbsp;&nbsp;&nbsp;→ lead/ticket status</div>
            <div>discount_value → quote discount %</div>
            <div>amount &nbsp;&nbsp;&nbsp;&nbsp;→ opportunity value</div>
            <div>pipeline_stage_id → stage ID</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@push('scripts')
<script>
// ========================================================
// Condition rows
// ========================================================
let conditionCount = {{ count(old('conditions', $workflow?->conditions->toArray() ?? [])) }};

document.getElementById('add-condition').addEventListener('click', function () {
  const i = conditionCount++;
  const tbody = document.getElementById('conditions-body');
  const row = document.createElement('tr');
  row.className = 'condition-row';
  row.innerHTML = `
    <td style="padding:.5rem 1.25rem;">
      <input type="text" name="conditions[${i}][field]" class="ncv-input ncv-input-sm" placeholder="e.g. status">
    </td>
    <td style="padding:.5rem .75rem;">
      <select name="conditions[${i}][operator]" class="ncv-select ncv-select-sm">
        <option value="eq">=</option>
        <option value="neq">≠</option>
        <option value="gt">&gt;</option>
        <option value="lt">&lt;</option>
        <option value="gte">≥</option>
        <option value="lte">≤</option>
        <option value="contains">contains</option>
      </select>
    </td>
    <td style="padding:.5rem .75rem;">
      <input type="text" name="conditions[${i}][value]" class="ncv-input ncv-input-sm" placeholder="value">
    </td>
    <td style="padding:.5rem .75rem;">
      <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm remove-row" style="color:#ef4444;"><i class="bi bi-x"></i></button>
    </td>`;
  tbody.appendChild(row);
  updateConditionsEmpty();
});

// ========================================================
// Action rows
// ========================================================
let actionCount = {{ count(old('actions', $workflow?->actions->toArray() ?? [])) }};
const usersOptions = `@foreach($users as $u)<option value="{{ $u->id }}">{{ addslashes($u->name) }}</option>@endforeach`;

document.getElementById('add-action').addEventListener('click', function () {
  const i = actionCount++;
  const body = document.getElementById('actions-body');
  const emptyEl = document.getElementById('actions-empty');
  const div = document.createElement('div');
  div.className = 'action-row';
  div.style.cssText = 'padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);';
  div.innerHTML = `
    <div class="d-flex align-items-center gap-2 mb-2">
      <span style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Action ${i + 1}</span>
      <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm remove-row ms-auto" style="color:#ef4444;"><i class="bi bi-x"></i> Remove</button>
    </div>
    <div class="mb-2">
      <label class="ncv-label">Type</label>
      <select name="actions[${i}][action_type]" class="ncv-select action-type-select" data-index="${i}">
        <option value="">Select action…</option>
        <option value="send_email">Send Email</option>
        <option value="assign_user">Assign User</option>
        <option value="change_status">Change Status</option>
        <option value="send_webhook">Send Webhook</option>
      </select>
    </div>
    <div class="action-config" data-index="${i}"></div>`;
  body.insertBefore(div, emptyEl);
  updateActionsEmpty();
  div.querySelector('.action-type-select').addEventListener('change', function () {
    renderActionConfig(this, i);
  });
});

// ========================================================
// Action config panels (rendered dynamically)
// ========================================================
function renderActionConfig(selectEl, i) {
  const type = selectEl.value;
  const configDiv = selectEl.closest('.action-row').querySelector('.action-config');

  const emailHtml = `
    <div class="row g-2">
      <div class="col-12 col-md-6">
        <label class="ncv-label" style="font-size:.78rem;">To Email (leave blank for owner)</label>
        <input type="email" name="actions[${i}][action_config][to_email]" class="ncv-input ncv-input-sm" placeholder="recipient@example.com">
      </div>
      <div class="col-12 col-md-6">
        <label class="ncv-label" style="font-size:.78rem;">Subject</label>
        <input type="text" name="actions[${i}][action_config][subject]" class="ncv-input ncv-input-sm" placeholder="Workflow Notification">
      </div>
      <div class="col-12">
        <label class="ncv-label" style="font-size:.78rem;">Message (use {field_name} tokens)</label>
        <textarea name="actions[${i}][action_config][message]" class="ncv-input ncv-input-sm" rows="3" placeholder="Lead {name} has been updated..."></textarea>
      </div>
    </div>`;

  const assignHtml = `
    <div>
      <label class="ncv-label" style="font-size:.78rem;">Assign To User</label>
      <select name="actions[${i}][action_config][user_id]" class="ncv-select ncv-select-sm">
        <option value="">Select user…</option>
        ${usersOptions}
      </select>
    </div>`;

  const statusHtml = `
    <div>
      <label class="ncv-label" style="font-size:.78rem;">New Status Value</label>
      <input type="text" name="actions[${i}][action_config][status]" class="ncv-input ncv-input-sm" placeholder="e.g. qualified, closed_won">
    </div>`;

  const webhookHtml = `
    <div>
      <label class="ncv-label" style="font-size:.78rem;">Webhook URL</label>
      <input type="url" name="actions[${i}][action_config][url]" class="ncv-input ncv-input-sm" placeholder="https://hooks.example.com/crm">
    </div>`;

  const panels = { send_email: emailHtml, assign_user: assignHtml, change_status: statusHtml, send_webhook: webhookHtml };
  configDiv.innerHTML = panels[type] || '';
}

// Attach listeners to existing action type selects (edit mode)
document.querySelectorAll('.action-type-select').forEach(function (sel) {
  sel.addEventListener('change', function () {
    renderActionConfig(this, parseInt(this.dataset.index));
  });
});

// ========================================================
// Trigger config panels
// ========================================================
const triggerSelect = document.getElementById('trigger-select');

function updateTriggerConfig() {
  document.querySelectorAll('.trigger-config').forEach(el => el.style.display = 'none');
  if (triggerSelect.value === 'quote_discount_exceeded') {
    document.getElementById('config-discount').style.display = 'block';
  }
}

triggerSelect.addEventListener('change', updateTriggerConfig);
updateTriggerConfig();

// ========================================================
// Remove rows
// ========================================================
document.addEventListener('click', function (e) {
  if (e.target.closest('.remove-row')) {
    const row = e.target.closest('tr, .action-row');
    if (row) {
      row.remove();
      updateConditionsEmpty();
      updateActionsEmpty();
    }
  }
});

function updateConditionsEmpty() {
  const rows = document.querySelectorAll('#conditions-body .condition-row');
  document.getElementById('conditions-empty').style.display = rows.length ? 'none' : '';
}

function updateActionsEmpty() {
  const rows = document.querySelectorAll('#actions-body .action-row');
  document.getElementById('actions-empty').style.display = rows.length ? 'none' : '';
}
</script>
@endpush
