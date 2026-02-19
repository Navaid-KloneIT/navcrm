@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Expenses</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">
      ${{ number_format($stats['total_pending'], 2) }} pending approval &middot;
      ${{ number_format($stats['total_approved'], 2) }} approved
    </p>
  </div>
  <a href="{{ route('finance.expenses.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Expense
  </a>
</div>

{{-- Status chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ route('finance.expenses.index', request()->except('status','page')) }}"
     class="ncv-chip {{ !request('status') ? 'active' : '' }}" style="font-size:.78rem;">All</a>
  @foreach(\App\Enums\ExpenseStatus::cases() as $s)
  <a href="{{ route('finance.expenses.index', array_merge(request()->except('status','page'), ['status' => $s->value])) }}"
     class="ncv-chip {{ request('status') === $s->value ? 'active' : '' }}" style="font-size:.78rem;">
    {{ $s->label() }}
  </a>
  @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('finance.expenses.index') }}" class="filter-bar mb-3">
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search description…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:200px;">
  </div>

  <select name="category" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Categories</option>
    @foreach(\App\Enums\ExpenseCategory::cases() as $c)
      <option value="{{ $c->value }}" {{ request('category') === $c->value ? 'selected' : '' }}>{{ $c->label() }}</option>
    @endforeach
  </select>

  <select name="user_id" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Submitters</option>
    @foreach($owners as $u)
      <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>

  <select name="opportunity_id" class="ncv-select ncv-select-sm" style="width:160px;">
    <option value="">All Opportunities</option>
    @foreach($opportunities as $opp)
      <option value="{{ $opp->id }}" {{ request('opportunity_id') == $opp->id ? 'selected' : '' }}>{{ $opp->name }}</option>
    @endforeach
  </select>

  <input type="date" name="date_from" value="{{ request('date_from') }}"
         class="ncv-input ncv-input-sm" title="Expense date from">
  <input type="date" name="date_to" value="{{ request('date_to') }}"
         class="ncv-input ncv-input-sm" title="Expense date to">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','category','user_id','opportunity_id','date_from','date_to','status']))
    <a href="{{ route('finance.expenses.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($expenses->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-receipt-cutoff" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No expenses found</p>
        <p class="small mb-3">Submit a new expense or adjust the filters.</p>
        <a href="{{ route('finance.expenses.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New Expense
        </a>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Description</th>
            <th>Category</th>
            <th style="text-align:right;">Amount</th>
            <th>Date</th>
            <th>Linked To</th>
            <th>Submitted By</th>
            <th>Status</th>
            <th>Approver</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($expenses as $exp)
          <tr>
            <td>
              <a href="{{ route('finance.expenses.show', $exp) }}"
                 class="ncv-table-cell-primary text-decoration-none" style="color:inherit;font-size:.875rem;">
                {{ Str::limit($exp->description, 40) }}
              </a>
            </td>
            <td>
              <span class="ncv-badge" style="background:#f1f5f9;color:#475569;font-size:.72rem;">
                {{ $exp->category->label() }}
              </span>
            </td>
            <td style="text-align:right;font-weight:700;">${{ number_format($exp->amount, 2) }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $exp->expense_date->format('M j, Y') }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">
              @if($exp->opportunity)
                <a href="{{ route('opportunities.show', $exp->opportunity) }}" style="color:var(--accent-blue);text-decoration:none;">
                  {{ Str::limit($exp->opportunity->name, 25) }}
                </a>
              @elseif($exp->account)
                {{ Str::limit($exp->account->name, 25) }}
              @else
                —
              @endif
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $exp->user?->name ?? '—' }}</td>
            <td>
              <span class="ncv-badge bg-{{ $exp->status->color() }}-subtle text-{{ $exp->status->color() }}" style="font-size:.72rem;">
                {{ $exp->status->label() }}
              </span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $exp->approvedBy?->name ?? '—' }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ route('finance.expenses.show', $exp) }}"
                   class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                  <i class="bi bi-eye" style="font-size:.8rem;"></i>
                </a>
                @if($exp->status->value === 'pending')
                  <a href="{{ route('finance.expenses.edit', $exp) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
                    <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                  </a>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($expenses->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-top:1px solid var(--border-color); font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $expenses->firstItem() }}–{{ $expenses->lastItem() }} of {{ $expenses->total() }}
    </span>
    {{ $expenses->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
