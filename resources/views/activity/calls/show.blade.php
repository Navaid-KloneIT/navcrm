@extends('layouts.app')

@section('title', 'Call Log')

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
      <i class="{{ $call->direction->icon() }}" style="font-size:1.3rem;color:var(--text-muted);"></i>
      <h1 class="h4 fw-semibold mb-0" style="color:var(--text-primary);">
        {{ $call->direction->label() }} Call
        @if($call->phone_number) — {{ $call->phone_number }} @endif
      </h1>
      <span class="badge bg-{{ $call->status->color() }}-subtle text-{{ $call->status->color() }} border border-{{ $call->status->color() }}-subtle">
        {{ $call->status->label() }}
      </span>
    </div>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ $call->called_at->format('l, M j, Y \a\t g:i A') }}
      @if($call->duration)
        · {{ $call->formatted_duration }}
      @endif
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('activity.calls.edit', $call) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('activity.calls.destroy', $call) }}"
          onsubmit="return confirm('Delete this call log?')">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Delete</button>
    </form>
  </div>
</div>

<div class="row g-4">

  <div class="col-12 col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Notes</h6></div>
      <div class="ncv-card-body">
        @if($call->notes)
          <p style="white-space:pre-wrap;color:var(--text-secondary);line-height:1.7;">{{ $call->notes }}</p>
        @else
          <p style="color:var(--text-muted);font-style:italic;">No notes recorded.</p>
        @endif
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Details</h6></div>
      <div class="ncv-card-body p-0">
        <table class="table table-sm mb-0" style="font-size:.875rem;">
          <tbody>
            <tr>
              <td style="color:var(--text-muted);width:40%;">Direction</td>
              <td><i class="{{ $call->direction->icon() }} me-1"></i>{{ $call->direction->label() }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Outcome</td>
              <td><span class="badge bg-{{ $call->status->color() }}-subtle text-{{ $call->status->color() }}">{{ $call->status->label() }}</span></td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Phone</td>
              <td>{{ $call->phone_number ?? '—' }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Duration</td>
              <td>{{ $call->formatted_duration }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Called At</td>
              <td>{{ $call->called_at->format('M j, Y g:i A') }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Logged By</td>
              <td>{{ $call->user?->name ?? '—' }}</td>
            </tr>
            @if($call->recording_url)
            <tr>
              <td style="color:var(--text-muted);">Recording</td>
              <td><a href="{{ $call->recording_url }}" target="_blank" rel="noopener" style="font-size:.8rem;">
                <i class="bi bi-play-circle me-1"></i>Listen
              </a></td>
            </tr>
            @endif
            @if($call->loggable)
            <tr>
              <td style="color:var(--text-muted);">Linked To</td>
              <td>
                <span style="font-size:.78rem;color:var(--text-muted);">{{ class_basename($call->loggable_type) }}</span><br>
                <strong>{{ $call->loggable->full_name ?? $call->loggable->name ?? '—' }}</strong>
              </td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@endsection
