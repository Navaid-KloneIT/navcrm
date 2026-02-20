@extends('layouts.app')

@section('title', $workflow->name)
@section('page-title', $workflow->name)

@section('breadcrumb-items')
  <a href="{{ route('workflows.index') }}" style="color:inherit;text-decoration:none;">Workflows</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ Str::limit($workflow->name, 40) }}</span>
@endsection

@section('content')
<div class="row g-3">
  {{-- Left: workflow details --}}
  <div class="col-12 col-xl-8">

    {{-- Header card --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-body">
        <div class="d-flex align-items-start gap-3">
          <div style="width:44px;height:44px;border-radius:.625rem;background:#6366f118;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
            <i class="bi bi-lightning-charge"></i>
          </div>
          <div class="flex-grow-1">
            <div style="font-size:1.1rem;font-weight:700;color:var(--text-primary);">{{ $workflow->name }}</div>
            @if($workflow->description)
              <div style="font-size:.85rem;color:var(--text-muted);margin-top:.2rem;">{{ $workflow->description }}</div>
            @endif
            <div class="d-flex gap-2 mt-2 flex-wrap">
              <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.72rem;font-weight:600;">
                <i class="bi bi-broadcast me-1"></i>{{ $workflow->trigger_event?->label() ?? $workflow->trigger_event }}
              </span>
              @if($workflow->is_active)
                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.72rem;">Active</span>
              @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem;">Inactive</span>
              @endif
              <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem;">
                {{ $workflow->conditions->count() }} condition{{ $workflow->conditions->count() === 1 ? '' : 's' }}
              </span>
              <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.72rem;">
                {{ $workflow->actions->count() }} action{{ $workflow->actions->count() === 1 ? '' : 's' }}
              </span>
            </div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('workflows.edit', $workflow) }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
              <i class="bi bi-pencil"></i> Edit
            </a>
            <form method="POST" action="{{ route('workflows.toggle', $workflow) }}" style="display:inline;">
              @csrf @method('PATCH')
              <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm"
                      style="color:{{ $workflow->is_active ? '#ef4444' : '#10b981' }};">
                <i class="bi {{ $workflow->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                {{ $workflow->is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- Trigger config --}}
    @if($workflow->trigger_config)
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-gear me-2" style="color:#10b981;"></i>Trigger Configuration</h6>
      </div>
      <div class="ncv-card-body" style="font-size:.875rem;">
        @foreach($workflow->trigger_config as $key => $val)
          <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $val }}</div>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Conditions --}}
    @if($workflow->conditions->count())
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-filter me-2" style="color:#f59e0b;"></i>Conditions (AND)</h6>
      </div>
      <div class="ncv-card-body p-0">
        <table class="table mb-0" style="font-size:.875rem;">
          <thead>
            <tr style="border-bottom:1px solid var(--border-color);">
              <th style="padding:.6rem 1.25rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Field</th>
              <th style="padding:.6rem 1rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Operator</th>
              <th style="padding:.6rem 1rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Value</th>
            </tr>
          </thead>
          <tbody>
            @foreach($workflow->conditions as $cond)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td style="padding:.6rem 1.25rem;font-family:monospace;">{{ $cond->field }}</td>
              <td style="padding:.6rem 1rem;">{{ $cond->operatorLabel() }}</td>
              <td style="padding:.6rem 1rem;font-family:monospace;">{{ $cond->value }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-play-circle me-2" style="color:#6366f1;"></i>Actions</h6>
      </div>
      <div class="ncv-card-body p-0">
        @foreach($workflow->actions as $i => $act)
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-color);">
          <div class="d-flex align-items-center gap-2 mb-1">
            <span style="width:22px;height:22px;border-radius:50%;background:#6366f118;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0;">{{ $i + 1 }}</span>
            <span style="font-weight:600;font-size:.875rem;">{{ $act->actionTypeLabel() }}</span>
          </div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-left:30px;">
            @foreach($act->action_config as $k => $v)
              @if($v)
                <div><strong>{{ ucfirst(str_replace('_', ' ', $k)) }}:</strong>
                  @if(strlen((string)$v) > 100) {{ Str::limit($v, 100) }} @else {{ $v }} @endif
                </div>
              @endif
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Run History --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-clock-history me-2" style="color:var(--ncv-blue-500);"></i>Recent Runs</h6>
      </div>
      <div class="ncv-card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:.82rem;">
            <thead>
              <tr style="border-bottom:1px solid var(--border-color);">
                <th style="padding:.6rem 1.25rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">When</th>
                <th style="padding:.6rem 1rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Entity</th>
                <th style="padding:.6rem 1rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Status</th>
                <th style="padding:.6rem 1rem;font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">Log</th>
              </tr>
            </thead>
            <tbody>
              @forelse($runs as $run)
              <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:.6rem 1.25rem;white-space:nowrap;">
                  {{ $run->triggered_at?->diffForHumans() }}
                  <div style="font-size:.72rem;color:var(--text-muted);">{{ $run->triggered_at?->format('M j, Y H:i') }}</div>
                </td>
                <td style="padding:.6rem 1rem;">
                  <div style="font-family:monospace;font-size:.75rem;">{{ class_basename($run->trigger_entity_type) }} #{{ $run->trigger_entity_id }}</div>
                </td>
                <td style="padding:.6rem 1rem;">
                  <span class="badge bg-{{ $run->statusColor() }} bg-opacity-15 text-{{ $run->statusColor() }}" style="font-size:.72rem;">{{ ucfirst($run->status) }}</span>
                </td>
                <td style="padding:.6rem 1rem;">
                  @if($run->error_message)
                    <span style="color:#ef4444;font-size:.75rem;">{{ Str::limit($run->error_message, 60) }}</span>
                  @elseif($run->actions_log)
                    <span style="color:var(--text-muted);font-size:.75rem;">{{ count($run->actions_log) }} action(s) logged</span>
                  @else
                    <span style="color:var(--text-muted);">—</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" style="padding:2rem;text-align:center;color:var(--text-muted);">No runs yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    @if($runs->hasPages())
      <div class="mt-3">{{ $runs->links() }}</div>
    @endif

  </div>

  {{-- Right sidebar --}}
  <div class="col-12 col-xl-4">
    <div class="ncv-card" style="position:sticky;top:1rem;">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6>
      </div>
      <div class="ncv-card-body" style="font-size:.875rem;">
        <div class="mb-2"><strong style="color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Created By</strong>
          <div>{{ $workflow->creator?->name ?? '—' }}</div>
        </div>
        <div class="mb-2"><strong style="color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Created</strong>
          <div>{{ $workflow->created_at->format('M j, Y') }}</div>
        </div>
        <div class="mb-2"><strong style="color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Last Updated</strong>
          <div>{{ $workflow->updated_at->diffForHumans() }}</div>
        </div>
        <div class="mb-2"><strong style="color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Total Runs</strong>
          <div>{{ $runs->total() }}</div>
        </div>
        <hr>
        <form method="POST" action="{{ route('workflows.destroy', $workflow) }}" onsubmit="return confirm('Delete this workflow?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-ghost w-100" style="color:#ef4444;">
            <i class="bi bi-trash"></i> Delete Workflow
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
