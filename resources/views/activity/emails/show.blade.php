@extends('layouts.app')

@section('title', $email->subject)

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
      <i class="{{ $email->direction->icon() }}" style="font-size:1.3rem;color:var(--text-muted);"></i>
      <h1 class="h4 fw-semibold mb-0" style="color:var(--text-primary);">{{ $email->subject }}</h1>
      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $email->direction->label() }}</span>
      <i class="{{ $email->source->icon() }}" style="color:var(--text-muted);" title="{{ $email->source->label() }}"></i>
    </div>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      @if($email->sent_at)
        Sent {{ $email->sent_at->format('M j, Y \a\t g:i A') }}
      @else
        Logged {{ $email->created_at->format('M j, Y') }}
      @endif
      @if($email->user) by {{ $email->user->name }} @endif
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('activity.emails.edit', $email) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('activity.emails.destroy', $email) }}"
          onsubmit="return confirm('Delete this email log?')">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Delete</button>
    </form>
  </div>
</div>

<div class="row g-4">

  <div class="col-12 col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Body</h6>
      </div>
      <div class="ncv-card-body">
        @if($email->body)
          <p style="white-space:pre-wrap;color:var(--text-secondary);line-height:1.7;font-size:.875rem;">{{ $email->body }}</p>
        @else
          <p style="color:var(--text-muted);font-style:italic;">No body recorded.</p>
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
              <td><i class="{{ $email->direction->icon() }} me-1"></i>{{ $email->direction->label() }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Source</td>
              <td><i class="{{ $email->source->icon() }} me-1"></i>{{ $email->source->label() }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">From</td>
              <td>{{ $email->from_email ?? '—' }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">To</td>
              <td>{{ $email->to_email ?? '—' }}</td>
            </tr>
            @if($email->cc && count($email->cc))
            <tr>
              <td style="color:var(--text-muted);">CC</td>
              <td style="font-size:.8rem;">{{ implode(', ', $email->cc) }}</td>
            </tr>
            @endif
            <tr>
              <td style="color:var(--text-muted);">Sent</td>
              <td>{{ $email->sent_at ? $email->sent_at->format('M j, Y g:i A') : '—' }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Opened</td>
              <td>
                @if($email->opened_at)
                  <span class="text-success fw-medium" style="font-size:.8rem;">
                    <i class="bi bi-check-circle me-1"></i>{{ $email->opened_at->format('M j, Y g:i A') }}
                  </span>
                @else
                  <span style="color:var(--text-muted);">Not tracked</span>
                @endif
              </td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Clicked</td>
              <td>
                @if($email->clicked_at)
                  <span class="text-primary fw-medium" style="font-size:.8rem;">
                    <i class="bi bi-check-circle me-1"></i>{{ $email->clicked_at->format('M j, Y g:i A') }}
                  </span>
                @else
                  <span style="color:var(--text-muted);">Not tracked</span>
                @endif
              </td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Logged By</td>
              <td>{{ $email->user?->name ?? '—' }}</td>
            </tr>
            @if($email->emailable)
            <tr>
              <td style="color:var(--text-muted);">Linked To</td>
              <td>
                <span style="font-size:.78rem;color:var(--text-muted);">{{ class_basename($email->emailable_type) }}</span><br>
                <strong>{{ $email->emailable->full_name ?? $email->emailable->name ?? '—' }}</strong>
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
