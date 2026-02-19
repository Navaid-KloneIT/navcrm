@extends('layouts.app')

@section('title', isset($event) ? 'Edit Event' : 'New Event')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
      {{ isset($event) ? 'Edit Event' : 'New Event' }}
    </h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ isset($event) ? 'Update calendar event details.' : 'Schedule a meeting, call, demo or follow-up.' }}
    </p>
  </div>
  <a href="{{ route('activity.calendar.index') }}" class="btn btn-ghost btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

@if($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST"
      action="{{ isset($event) ? route('activity.calendar.update', $event) : route('activity.calendar.store') }}">
  @csrf
  @if(isset($event)) @method('PUT') @endif

  <div class="row g-4">

    {{-- Main --}}
    <div class="col-12 col-lg-8">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Event Details</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $event->title ?? '') }}" required placeholder="Event title…">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Description</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="Agenda, notes, or context…">{{ old('description', $event->description ?? '') }}</textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Starts At <span class="text-danger">*</span></label>
              <input type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror"
                     value="{{ old('starts_at', isset($event->starts_at) ? $event->starts_at->format('Y-m-d\TH:i') : '') }}" required>
              @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Ends At <span class="text-danger">*</span></label>
              <input type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror"
                     value="{{ old('ends_at', isset($event->ends_at) ? $event->ends_at->format('Y-m-d\TH:i') : '') }}" required>
              @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_all_day" id="isAllDay" value="1"
                   {{ old('is_all_day', $event->is_all_day ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="isAllDay">All-day event</label>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Location</label>
            <input type="text" name="location" class="form-control"
                   value="{{ old('location', $event->location ?? '') }}"
                   placeholder="Office, conference room, or city…">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Meeting Link</label>
              <input type="url" name="meeting_link" class="form-control @error('meeting_link') is-invalid @enderror"
                     value="{{ old('meeting_link', $event->meeting_link ?? '') }}"
                     placeholder="https://meet.google.com/…">
              @error('meeting_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Invite / Booking URL</label>
              <input type="url" name="invite_url" class="form-control @error('invite_url') is-invalid @enderror"
                     value="{{ old('invite_url', $event->invite_url ?? '') }}"
                     placeholder="https://calendly.com/…">
              @error('invite_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

        </div>
      </div>

      {{-- External Calendar --}}
      <div class="ncv-card mt-4">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">External Calendar Sync</h6></div>
        <div class="ncv-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Source</label>
              <select name="external_calendar_source" class="form-select">
                <option value="">— None —</option>
                <option value="google"  {{ old('external_calendar_source', $event->external_calendar_source ?? '') === 'google'  ? 'selected' : '' }}>Google Calendar</option>
                <option value="outlook" {{ old('external_calendar_source', $event->external_calendar_source ?? '') === 'outlook' ? 'selected' : '' }}>Outlook / Office 365</option>
                <option value="ical"    {{ old('external_calendar_source', $event->external_calendar_source ?? '') === 'ical'    ? 'selected' : '' }}>iCal</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">External Calendar ID</label>
              <input type="text" name="external_calendar_id" class="form-control"
                     value="{{ old('external_calendar_id', $event->external_calendar_id ?? '') }}"
                     placeholder="Event ID from external system">
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-12 col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Properties</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Event Type <span class="text-danger">*</span></label>
            <select name="event_type" class="form-select" required>
              @foreach(\App\Enums\CalendarEventType::cases() as $t)
                <option value="{{ $t->value }}" {{ old('event_type', $event->event_type?->value ?? 'meeting') === $t->value ? 'selected' : '' }}>
                  {{ $t->label() }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select" required>
              @foreach(\App\Enums\CalendarEventStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ old('status', $event->status?->value ?? 'scheduled') === $s->value ? 'selected' : '' }}>
                  {{ $s->label() }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Organizer</label>
            <select name="organizer_id" class="form-select">
              <option value="">— Current User —</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('organizer_id', $event->organizer_id ?? '') == $user->id ? 'selected' : '' }}>
                  {{ $user->name }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <div class="ncv-card mt-4">
        <div class="ncv-card-header"><h6 class="mb-0 fw-semibold">Related Record</h6></div>
        <div class="ncv-card-body">

          <div class="mb-3">
            <label class="form-label fw-medium">Relate To</label>
            <select id="eventableType" name="eventable_type" class="form-select"
                    onchange="updateEventableSelect(this.value)">
              <option value="">— None —</option>
              <option value="App\Models\Contact"     {{ old('eventable_type', $event->eventable_type ?? '') === 'App\Models\Contact'     ? 'selected' : '' }}>Contact</option>
              <option value="App\Models\Account"     {{ old('eventable_type', $event->eventable_type ?? '') === 'App\Models\Account'     ? 'selected' : '' }}>Account</option>
              <option value="App\Models\Opportunity" {{ old('eventable_type', $event->eventable_type ?? '') === 'App\Models\Opportunity' ? 'selected' : '' }}>Opportunity</option>
            </select>
          </div>

          <div id="contactEventable" class="eventable-select d-none">
            <label class="form-label fw-medium">Contact</label>
            <select name="eventable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($contacts as $c)
                <option value="{{ $c->id }}" {{ old('eventable_id', $event->eventable_id ?? '') == $c->id && old('eventable_type', $event->eventable_type ?? '') === 'App\Models\Contact' ? 'selected' : '' }}>
                  {{ $c->first_name }} {{ $c->last_name }}
                </option>
              @endforeach
            </select>
          </div>
          <div id="accountEventable" class="eventable-select d-none">
            <label class="form-label fw-medium">Account</label>
            <select name="eventable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($accounts as $a)
                <option value="{{ $a->id }}" {{ old('eventable_id', $event->eventable_id ?? '') == $a->id && old('eventable_type', $event->eventable_type ?? '') === 'App\Models\Account' ? 'selected' : '' }}>
                  {{ $a->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div id="opportunityEventable" class="eventable-select d-none">
            <label class="form-label fw-medium">Opportunity</label>
            <select name="eventable_id" class="form-select">
              <option value="">Select…</option>
              @foreach($opportunities as $o)
                <option value="{{ $o->id }}" {{ old('eventable_id', $event->eventable_id ?? '') == $o->id && old('eventable_type', $event->eventable_type ?? '') === 'App\Models\Opportunity' ? 'selected' : '' }}>
                  {{ $o->name }}
                </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <div class="d-grid gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          {{ isset($event) ? 'Update Event' : 'Schedule Event' }}
        </button>
        <a href="{{ route('activity.calendar.index') }}" class="btn btn-ghost">Cancel</a>
      </div>
    </div>

  </div>
</form>

@push('scripts')
<script>
function updateEventableSelect(type) {
  document.querySelectorAll('.eventable-select').forEach(el => el.classList.add('d-none'));
  if (type === 'App\\Models\\Contact')     document.getElementById('contactEventable').classList.remove('d-none');
  if (type === 'App\\Models\\Account')     document.getElementById('accountEventable').classList.remove('d-none');
  if (type === 'App\\Models\\Opportunity') document.getElementById('opportunityEventable').classList.remove('d-none');
}
updateEventableSelect(document.getElementById('eventableType').value);
</script>
@endpush

@endsection
