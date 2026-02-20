@extends('layouts.app')

@section('title', 'Approvals')
@section('page-title', 'Quote Approvals')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Approvals</span>
@endsection

@section('content')

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="font-size:.875rem;">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="font-size:.875rem;">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="ncv-card">
  <div class="ncv-card-header d-flex align-items-center justify-content-between">
    <h6 class="ncv-card-title mb-0">
      <i class="bi bi-check-circle me-2" style="color:#f59e0b;"></i>Quotes Pending Approval
    </h6>
    <span class="badge bg-warning text-dark" style="font-size:.75rem;">{{ $quotes->total() }} pending</span>
  </div>

  <div class="ncv-card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.875rem;">
        <thead>
          <tr style="border-bottom:1px solid var(--border-color);">
            <th style="padding:.75rem 1.25rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Quote</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Account</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Discount</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Prepared By</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Total</th>
            <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($quotes as $quote)
          <tr style="border-bottom:1px solid var(--border-color);">
            <td style="padding:.75rem 1.25rem;">
              <a href="{{ route('quotes.show', $quote) }}" style="font-weight:600;color:var(--ncv-blue-500);text-decoration:none;">
                {{ $quote->quote_number }}
              </a>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $quote->created_at->format('M j, Y') }}</div>
            </td>
            <td style="padding:.75rem 1rem;">{{ $quote->account?->name ?? '—' }}</td>
            <td style="padding:.75rem 1rem;">
              @if($quote->discount_value)
                <span style="font-weight:600;color:#ef4444;">
                  {{ $quote->discount_value }}%
                  @if($quote->discount_type === 'fixed') (fixed) @endif
                </span>
              @else
                —
              @endif
            </td>
            <td style="padding:.75rem 1rem;">{{ $quote->preparedBy?->name ?? '—' }}</td>
            <td style="padding:.75rem 1rem;font-weight:600;">{{ number_format($quote->total, 2) }}</td>
            <td style="padding:.75rem 1rem;">
              <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- Approve button --}}
                <form method="POST" action="{{ route('approvals.approve', $quote) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="ncv-btn ncv-btn-sm"
                          style="background:#10b981;color:#fff;border:none;border-radius:.375rem;padding:.3rem .75rem;font-size:.78rem;font-weight:600;cursor:pointer;">
                    <i class="bi bi-check-lg"></i> Approve
                  </button>
                </form>

                {{-- Reject button (opens inline form) --}}
                <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm"
                        style="color:#ef4444;border:1px solid #ef4444;"
                        onclick="toggleReject({{ $quote->id }})">
                  <i class="bi bi-x-lg"></i> Reject
                </button>
              </div>

              {{-- Rejection reason form (hidden by default) --}}
              <div id="reject-form-{{ $quote->id }}" style="display:none;margin-top:.75rem;">
                <form method="POST" action="{{ route('approvals.reject', $quote) }}">
                  @csrf
                  <div class="mb-2">
                    <textarea name="rejection_reason" class="ncv-input" rows="2"
                              placeholder="Reason for rejection…" required
                              style="font-size:.82rem;"></textarea>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" class="ncv-btn ncv-btn-sm"
                            style="background:#ef4444;color:#fff;border:none;border-radius:.375rem;padding:.3rem .75rem;font-size:.78rem;font-weight:600;cursor:pointer;">
                      Confirm Rejection
                    </button>
                    <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm"
                            onclick="toggleReject({{ $quote->id }})">Cancel</button>
                  </div>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="padding:3rem;text-align:center;color:var(--text-muted);">
              <i class="bi bi-check-circle" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
              No quotes pending approval.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($quotes->hasPages())
  <div class="mt-3">{{ $quotes->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
function toggleReject(id) {
  const el = document.getElementById('reject-form-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
