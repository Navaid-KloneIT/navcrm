@extends('layouts.app')

@section('title', 'Calendar Events')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Calendar Events</h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">Schedule meetings, calls, demos and follow-ups.</p>
  </div>
  <a href="{{ route('activity.calendar.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> New Event
  </a>
</div>

{{-- Filters --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search events…" value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-2">
        <select name="event_type" class="form-select form-select-sm">
          <option value="">All Types</option>
          @foreach(\App\Enums\CalendarEventType::cases() as $t)
            <option value="{{ $t->value }}" {{ request('event_type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          @foreach(\App\Enums\CalendarEventStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <input type="date" name="from" class="form-control form-control-sm"
               value="{{ request('from') }}" placeholder="From date">
      </div>
      <div class="col-6 col-md-2">
        <input type="date" name="to" class="form-control form-control-sm"
               value="{{ request('to') }}" placeholder="To date">
      </div>
      <div class="col-12 col-md-1">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($events->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-calendar3" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No events found</p>
        <a href="{{ route('activity.calendar.create') }}" class="btn btn-primary btn-sm">Schedule Event</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle);border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Event</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Type</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Status</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Starts At</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Organizer</th>
              <th class="py-3 pe-4"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($events as $event)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <div class="fw-medium" style="color:var(--text-primary);">
                  <i class="{{ $event->event_type->icon() }} me-1" style="color:var(--text-muted);"></i>
                  {{ $event->title }}
                </div>
                @if($event->location)
                  <div style="color:var(--text-muted);font-size:.78rem;">
                    <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($event->location, 40) }}
                  </div>
                @endif
              </td>
              <td class="py-3">
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.72rem;">
                  {{ $event->event_type->label() }}
                </span>
              </td>
              <td class="py-3">
                <span class="badge bg-{{ $event->status->color() }}-subtle text-{{ $event->status->color() }} border border-{{ $event->status->color() }}-subtle"
                      style="font-size:.72rem;">{{ $event->status->label() }}</span>
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                @if($event->is_all_day)
                  {{ $event->starts_at->format('M j, Y') }} <span class="badge bg-info-subtle text-info" style="font-size:.68rem;">All Day</span>
                @else
                  {{ $event->starts_at->format('M j, Y g:i A') }}
                @endif
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $event->organizer?->name ?? '—' }}
              </td>
              <td class="py-3 pe-4">
                <a href="{{ route('activity.calendar.show', $event) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                <a href="{{ route('activity.calendar.edit', $event) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('activity.calendar.destroy', $event) }}" class="d-inline"
                      onsubmit="return confirm('Delete this event?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($events->hasPages())
      <div class="d-flex justify-content-center px-4 py-3" style="border-top:1px solid var(--border-color);">
        {{ $events->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
