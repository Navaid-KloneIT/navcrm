@extends('portal.layout')

@section('title', 'My Dashboard')

@section('content')

<div class="mb-4">
  <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
    Welcome back, {{ $contact->first_name }}!
  </h1>
  <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
    Track your support requests and find answers in our knowledge base.
  </p>
</div>

{{-- Quick Actions --}}
<div class="row g-3 mb-5">
  <div class="col-6 col-md-3">
    <a href="{{ route('portal.tickets.create') }}" class="ncv-card text-decoration-none d-block p-4 text-center h-100"
       style="transition:.15s;">
      <i class="bi bi-plus-circle d-block mb-2" style="font-size:1.75rem;color:#3b82f6;"></i>
      <div class="fw-semibold" style="color:var(--text-primary);font-size:.9rem;">New Ticket</div>
      <div style="color:var(--text-muted);font-size:.78rem;">Submit a request</div>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="{{ route('portal.tickets.index') }}" class="ncv-card text-decoration-none d-block p-4 text-center h-100">
      <i class="bi bi-ticket-perforated d-block mb-2" style="font-size:1.75rem;color:#6366f1;"></i>
      <div class="fw-semibold" style="color:var(--text-primary);font-size:.9rem;">My Tickets</div>
      <div style="color:var(--text-muted);font-size:.78rem;">Track your cases</div>
    </a>
  </div>
</div>

<div class="row g-4">

  {{-- Recent Tickets --}}
  <div class="col-12 col-md-7">
    <div class="ncv-card">
      <div class="ncv-card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">Recent Tickets</h6>
        <a href="{{ route('portal.tickets.index') }}" style="font-size:.8rem;color:var(--accent-blue);text-decoration:none;">
          View all <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      <div class="ncv-card-body p-0">
        @forelse($tickets as $ticket)
        <a href="{{ route('portal.tickets.show', $ticket) }}"
           class="d-flex align-items-center justify-content-between px-4 py-3 text-decoration-none"
           style="border-bottom:1px solid var(--border-color); color:inherit;">
          <div>
            <div class="fw-medium" style="color:var(--accent-blue);font-size:.875rem;">{{ $ticket->ticket_number }}</div>
            <div style="color:var(--text-secondary);font-size:.8rem;">{{ Str::limit($ticket->subject, 50) }}</div>
          </div>
          <span class="badge bg-{{ $ticket->status->color() }}-subtle text-{{ $ticket->status->color() }} border border-{{ $ticket->status->color() }}-subtle"
                style="font-size:.72rem;flex-shrink:0;">
            {{ $ticket->status->label() }}
          </span>
        </a>
        @empty
          <div class="text-center py-5" style="color:var(--text-muted);">
            <i class="bi bi-ticket-perforated" style="font-size:2rem;opacity:.4;"></i>
            <p class="mt-2 mb-2 small">No tickets yet.</p>
            <a href="{{ route('portal.tickets.create') }}" class="btn btn-primary btn-sm">Submit a Ticket</a>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Knowledge Base --}}
  <div class="col-12 col-md-5">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Knowledge Base</h6>
      </div>
      <div class="ncv-card-body p-0">
        @forelse($articles as $article)
        <div class="px-4 py-3" style="border-bottom:1px solid var(--border-color);">
          <div class="fw-medium" style="font-size:.875rem;color:var(--text-primary);">{{ $article->title }}</div>
          @if($article->category)
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle mt-1" style="font-size:.68rem;">{{ $article->category }}</span>
          @endif
        </div>
        @empty
          <div class="text-center py-4" style="color:var(--text-muted);">
            <i class="bi bi-book" style="font-size:1.5rem;opacity:.4;"></i>
            <p class="mt-2 mb-0 small">No articles available yet.</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

</div>

@endsection
