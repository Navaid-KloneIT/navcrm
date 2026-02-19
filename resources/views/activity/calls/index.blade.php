@extends('layouts.app')

@section('title', 'Call Logs')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Call Logs</h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">Track inbound and outbound calls.</p>
  </div>
  <a href="{{ route('activity.calls.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Log Call
  </a>
</div>

{{-- Filters --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search phone or notes…" value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-2">
        <select name="direction" class="form-select form-select-sm">
          <option value="">All Directions</option>
          @foreach(\App\Enums\CallDirection::cases() as $d)
            <option value="{{ $d->value }}" {{ request('direction') === $d->value ? 'selected' : '' }}>{{ $d->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          @foreach(\App\Enums\CallStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="user_id" class="form-select form-select-sm">
          <option value="">All Users</option>
          @foreach($users as $user)
            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($calls->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-telephone-x" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No call logs found</p>
        <a href="{{ route('activity.calls.create') }}" class="btn btn-primary btn-sm">Log a Call</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle);border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Call</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Direction</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Status</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Duration</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Called At</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Logged By</th>
              <th class="py-3 pe-4"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($calls as $call)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <div class="fw-medium" style="color:var(--text-primary);">
                  {{ $call->phone_number ?? 'Unknown number' }}
                </div>
                @if($call->loggable)
                  <div style="color:var(--text-muted);font-size:.78rem;">
                    <i class="bi bi-link-45deg"></i>
                    {{ class_basename($call->loggable_type) }}:
                    {{ $call->loggable->full_name ?? $call->loggable->name ?? '—' }}
                  </div>
                @endif
              </td>
              <td class="py-3">
                <i class="{{ $call->direction->icon() }} me-1" style="color:var(--text-muted);"></i>
                <span style="font-size:.8rem;color:var(--text-muted);">{{ $call->direction->label() }}</span>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $call->status->color() }}-subtle text-{{ $call->status->color() }} border border-{{ $call->status->color() }}-subtle"
                      style="font-size:.72rem;">{{ $call->status->label() }}</span>
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $call->formatted_duration }}
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $call->called_at->format('M j, Y g:i A') }}
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $call->user?->name ?? '—' }}
              </td>
              <td class="py-3 pe-4">
                <a href="{{ route('activity.calls.show', $call) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                <a href="{{ route('activity.calls.edit', $call) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('activity.calls.destroy', $call) }}" class="d-inline"
                      onsubmit="return confirm('Delete this call log?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($calls->hasPages())
      <div class="d-flex justify-content-center px-4 py-3" style="border-top:1px solid var(--border-color);">
        {{ $calls->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
