@extends('layouts.app')

@section('title', 'Email Logs')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Email Logs</h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">Track email interactions via BCC dropbox, Gmail, Outlook or manual entry.</p>
  </div>
  <a href="{{ route('activity.emails.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Log Email
  </a>
</div>

{{-- Filters --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-3">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search subject or email…" value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-2">
        <select name="direction" class="form-select form-select-sm">
          <option value="">All Directions</option>
          @foreach(\App\Enums\EmailDirection::cases() as $d)
            <option value="{{ $d->value }}" {{ request('direction') === $d->value ? 'selected' : '' }}>{{ $d->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="source" class="form-select form-select-sm">
          <option value="">All Sources</option>
          @foreach(\App\Enums\EmailSource::cases() as $s)
            <option value="{{ $s->value }}" {{ request('source') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
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
    @if($emails->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-envelope-x" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No email logs found</p>
        <a href="{{ route('activity.emails.create') }}" class="btn btn-primary btn-sm">Log an Email</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle);border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Subject</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Direction</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Source</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Engagement</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Sent</th>
              <th class="py-3 pe-4"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($emails as $email)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <div class="fw-medium" style="color:var(--text-primary);">{{ Str::limit($email->subject, 55) }}</div>
                <div style="color:var(--text-muted);font-size:.78rem;">
                  @if($email->direction->value === 'outbound')
                    To: {{ $email->to_email ?? '—' }}
                  @else
                    From: {{ $email->from_email ?? '—' }}
                  @endif
                </div>
              </td>
              <td class="py-3" style="font-size:.8rem;color:var(--text-muted);">
                <i class="{{ $email->direction->icon() }} me-1"></i>{{ $email->direction->label() }}
              </td>
              <td class="py-3">
                <i class="{{ $email->source->icon() }} me-1" style="color:var(--text-muted);"></i>
                <span style="font-size:.8rem;color:var(--text-muted);">{{ $email->source->label() }}</span>
              </td>
              <td class="py-3">
                @if($email->isOpened())
                  <span class="badge bg-success-subtle text-success border border-success-subtle me-1" style="font-size:.68rem;">
                    <i class="bi bi-eye me-1"></i>Opened
                  </span>
                @endif
                @if($email->isClicked())
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.68rem;">
                    <i class="bi bi-cursor me-1"></i>Clicked
                  </span>
                @endif
                @if(!$email->isOpened() && !$email->isClicked())
                  <span style="color:var(--text-muted);font-size:.78rem;">—</span>
                @endif
              </td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">
                {{ $email->sent_at ? $email->sent_at->format('M j, Y g:i A') : '—' }}
              </td>
              <td class="py-3 pe-4">
                <a href="{{ route('activity.emails.show', $email) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                <a href="{{ route('activity.emails.edit', $email) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('activity.emails.destroy', $email) }}" class="d-inline"
                      onsubmit="return confirm('Delete this email log?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-ghost btn-sm text-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($emails->hasPages())
      <div class="d-flex justify-content-center px-4 py-3" style="border-top:1px solid var(--border-color);">
        {{ $emails->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
