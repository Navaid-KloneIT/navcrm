@extends('layouts.app')

@section('title', $event->title)

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
      <i class="{{ $event->event_type->icon() }}" style="font-size:1.3rem;color:var(--text-muted);"></i>
      <h1 class="h4 fw-semibold mb-0" style="color:var(--text-primary);">{{ $event->title }}</h1>
      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $event->event_type->label() }}</span>
      <span class="badge bg-{{ $event->status->color() }}-subtle text-{{ $event->status->color() }} border border-{{ $event->status->color() }}-subtle">{{ $event->status->label() }}</span>
    </div>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ $event->starts_at->format('l, M j, Y') }}
      @if(! $event->is_all_day)
        at {{ $event->starts_at->format('g:i A') }} – {{ $event->ends_at->format('g:i A') }}
        <span style="color:var(--text-muted);">({{ $event->duration_minutes }} min)</span>
      @else
        <span class="badge bg-info-subtle text-info ms-1">All Day</span>
      @endif
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('activity.calendar.edit', $event) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('activity.calendar.destroy', $event) }}"
          onsubmit="return confirm('Delete this event?')">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Delete</button>
    </form>
  </div>
</div>

<div class="row g-4">

  <div class="col-12 col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Description</h6></div>
      <div class="ncv-card-body">
        @if($event->description)
          <p style="white-space:pre-wrap;color:var(--text-secondary);line-height:1.7;">{{ $event->description }}</p>
        @else
          <p style="color:var(--text-muted);font-style:italic;">No description provided.</p>
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
              <td style="color:var(--text-muted);width:40%;">Start</td>
              <td>{{ $event->starts_at->format('M j, Y g:i A') }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">End</td>
              <td>{{ $event->ends_at->format('M j, Y g:i A') }}</td>
            </tr>
            <tr>
              <td style="color:var(--text-muted);">Organizer</td>
              <td>{{ $event->organizer?->name ?? '—' }}</td>
            </tr>
            @if($event->location)
            <tr>
              <td style="color:var(--text-muted);">Location</td>
              <td>{{ $event->location }}</td>
            </tr>
            @endif
            @if($event->meeting_link)
            <tr>
              <td style="color:var(--text-muted);">Meeting Link</td>
              <td><a href="{{ $event->meeting_link }}" target="_blank" rel="noopener" style="font-size:.8rem;">
                <i class="bi bi-camera-video me-1"></i>Join Meeting
              </a></td>
            </tr>
            @endif
            @if($event->invite_url)
            <tr>
              <td style="color:var(--text-muted);">Invite URL</td>
              <td><a href="{{ $event->invite_url }}" target="_blank" rel="noopener" style="font-size:.8rem;">
                <i class="bi bi-calendar-plus me-1"></i>Book a slot
              </a></td>
            </tr>
            @endif
            @if($event->external_calendar_source)
            <tr>
              <td style="color:var(--text-muted);">Synced From</td>
              <td style="text-transform:capitalize;">{{ $event->external_calendar_source }}</td>
            </tr>
            @endif
            @if($event->eventable)
            <tr>
              <td style="color:var(--text-muted);">Linked To</td>
              <td>
                <span style="font-size:.78rem;color:var(--text-muted);">{{ class_basename($event->eventable_type) }}</span><br>
                <strong>{{ $event->eventable->full_name ?? $event->eventable->name ?? '—' }}</strong>
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
