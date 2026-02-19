@extends('layouts.app')

@section('title', 'Expense — ' . Str::limit($expense->description, 30))
@section('page-title', 'Expense Detail')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('finance.expenses.index') }}" class="ncv-breadcrumb-item">Expenses</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Action bar --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-2">
    <span class="ncv-badge" style="background:#f1f5f9;color:#475569;font-size:.8rem;">
      {{ $expense->category->label() }}
    </span>
    <span class="ncv-badge bg-{{ $expense->status->color() }}-subtle text-{{ $expense->status->color() }}" style="font-size:.8rem;">
      {{ $expense->status->label() }}
    </span>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    @if($expense->status->value === 'pending')
      <a href="{{ route('finance.expenses.edit', $expense) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-pencil me-1"></i>Edit
      </a>
      <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}" class="d-inline">
        @csrf
        <button type="submit" class="ncv-btn ncv-btn-sm" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;">
          <i class="bi bi-check-circle me-1"></i>Approve
        </button>
      </form>
      <form method="POST" action="{{ route('finance.expenses.reject', $expense) }}" class="d-inline">
        @csrf
        <button type="submit" class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;">
          <i class="bi bi-x-circle me-1"></i>Reject
        </button>
      </form>
    @endif
  </div>
</div>

<div class="row g-3">
  {{-- Main details --}}
  <div class="col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Expense Details</span></div>
      <div class="ncv-card-body">

        <div class="mb-3">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Description</div>
          <div style="font-size:.95rem;font-weight:600;">{{ $expense->description }}</div>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Amount</div>
            <div style="font-size:1.2rem;font-weight:800;">{{ $expense->currency }} ${{ number_format($expense->amount, 2) }}</div>
          </div>
          <div class="col-md-4">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Category</div>
            <div>{{ $expense->category->label() }}</div>
          </div>
          <div class="col-md-4">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Date</div>
            <div>{{ $expense->expense_date->format('M j, Y') }}</div>
          </div>
        </div>

        @if($expense->notes)
        <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Notes</div>
          <p style="font-size:.88rem;color:var(--text-muted);margin:0;">{{ $expense->notes }}</p>
        </div>
        @endif

        @if($expense->receipt_url)
        <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Receipt</div>
          <a href="{{ $expense->receipt_url }}" target="_blank" rel="noopener"
             class="ncv-btn ncv-btn-outline ncv-btn-sm">
            <i class="bi bi-paperclip me-1"></i>View Receipt
          </a>
        </div>
        @endif

      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div class="col-lg-4">
    <div class="ncv-card mb-3">
      <div class="ncv-card-header"><span class="ncv-card-title">Summary</span></div>
      <div class="ncv-card-body">
        <table class="w-100" style="font-size:.82rem;">
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Submitted By</td>
            <td style="text-align:right;font-weight:600;">{{ $expense->user?->name ?? '—' }}</td>
          </tr>
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Status</td>
            <td style="text-align:right;">
              <span class="ncv-badge bg-{{ $expense->status->color() }}-subtle text-{{ $expense->status->color() }}">
                {{ $expense->status->label() }}
              </span>
            </td>
          </tr>
          @if($expense->approvedBy)
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">{{ $expense->status->value === 'approved' ? 'Approved By' : 'Rejected By' }}</td>
            <td style="text-align:right;font-weight:600;">{{ $expense->approvedBy->name }}</td>
          </tr>
          @if($expense->approved_at)
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">{{ $expense->status->value === 'approved' ? 'Approved At' : 'Rejected At' }}</td>
            <td style="text-align:right;">{{ $expense->approved_at->format('M j, Y H:i') }}</td>
          </tr>
          @endif
          @endif
          @if($expense->opportunity)
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Opportunity</td>
            <td style="text-align:right;">
              <a href="{{ route('opportunities.show', $expense->opportunity) }}" style="color:var(--accent-blue);text-decoration:none;">
                {{ Str::limit($expense->opportunity->name, 20) }}
              </a>
            </td>
          </tr>
          @endif
          @if($expense->account)
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Account</td>
            <td style="text-align:right;">{{ $expense->account->name }}</td>
          </tr>
          @endif
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Created</td>
            <td style="text-align:right;">{{ $expense->created_at->format('M j, Y') }}</td>
          </tr>
        </table>
      </div>
    </div>

    @if($expense->status->value === 'pending')
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Actions</span></div>
      <div class="ncv-card-body d-flex flex-column gap-2">
        <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}">
          @csrf
          <button type="submit" class="ncv-btn w-100" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;">
            <i class="bi bi-check-circle me-1"></i>Approve Expense
          </button>
        </form>
        <form method="POST" action="{{ route('finance.expenses.reject', $expense) }}">
          @csrf
          <button type="submit" class="ncv-btn w-100" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;">
            <i class="bi bi-x-circle me-1"></i>Reject Expense
          </button>
        </form>
      </div>
    </div>
    @endif
  </div>
</div>

@endsection
