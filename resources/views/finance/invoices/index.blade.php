@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

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
    <h1 class="ncv-page-title mb-0">Invoices</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">{{ $invoices->total() }} invoice{{ $invoices->total() !== 1 ? 's' : '' }}</p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    @if($stats['overdue_count'] > 0)
    <span class="ncv-badge" style="background:#fef2f2;color:#ef4444;font-size:.78rem;">
      {{ $stats['overdue_count'] }} overdue
    </span>
    @endif
    @if($stats['total_outstanding'] > 0)
    <span style="font-size:.82rem;color:var(--text-muted);">
      ${{ number_format($stats['total_outstanding'], 2) }} outstanding
    </span>
    @endif
    <a href="{{ route('finance.invoices.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Invoice
    </a>
  </div>
</div>

{{-- Status chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ route('finance.invoices.index', request()->except('status','page')) }}"
     class="ncv-chip {{ !request('status') ? 'active' : '' }}" style="font-size:.78rem;">All</a>
  @foreach(\App\Enums\InvoiceStatus::cases() as $s)
  <a href="{{ route('finance.invoices.index', array_merge(request()->except('status','page'), ['status' => $s->value])) }}"
     class="ncv-chip {{ request('status') === $s->value ? 'active' : '' }}" style="font-size:.78rem;">
    {{ $s->label() }}
  </a>
  @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('finance.invoices.index') }}" class="filter-bar mb-3">
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search invoice #…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:180px;">
  </div>

  <select name="account_id" class="ncv-select ncv-select-sm" style="width:150px;">
    <option value="">All Accounts</option>
    @foreach($accounts as $acc)
      <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
    @endforeach
  </select>

  <select name="owner_id" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Owners</option>
    @foreach($owners as $u)
      <option value="{{ $u->id }}" {{ request('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>

  <input type="date" name="date_from" value="{{ request('date_from') }}"
         class="ncv-input ncv-input-sm" title="Issue date from">
  <input type="date" name="date_to" value="{{ request('date_to') }}"
         class="ncv-input ncv-input-sm" title="Issue date to">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','account_id','owner_id','date_from','date_to','status']))
    <a href="{{ route('finance.invoices.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($invoices->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-receipt" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No invoices found</p>
        <p class="small mb-3">Create your first invoice or adjust the filters.</p>
        <a href="{{ route('finance.invoices.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New Invoice
        </a>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Account</th>
            <th>Status</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th style="text-align:right;">Total</th>
            <th style="text-align:right;">Amount Due</th>
            <th>Owner</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoices as $inv)
          <tr>
            <td>
              <a href="{{ route('finance.invoices.show', $inv) }}"
                 class="ncv-table-cell-primary text-decoration-none" style="color:inherit;font-size:.875rem;">
                {{ $inv->invoice_number }}
              </a>
              @if($inv->is_recurring)
                <i class="bi bi-arrow-repeat ms-1" style="font-size:.7rem;color:var(--text-muted);" title="Recurring"></i>
              @endif
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $inv->account?->name ?? '—' }}</td>
            <td>
              <span class="ncv-badge bg-{{ $inv->status->color() }}-subtle text-{{ $inv->status->color() }}">
                {{ $inv->status->label() }}
              </span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $inv->issue_date?->format('M j, Y') ?? '—' }}</td>
            <td style="font-size:.82rem;{{ $inv->status === \App\Enums\InvoiceStatus::Overdue ? 'color:#ef4444;font-weight:600;' : 'color:var(--text-muted);' }}">
              {{ $inv->due_date?->format('M j, Y') ?? '—' }}
            </td>
            <td style="text-align:right;font-weight:700;">${{ number_format($inv->total, 2) }}</td>
            <td style="text-align:right;font-weight:700;{{ $inv->amount_due > 0 ? 'color:#ef4444;' : 'color:#10b981;' }}">
              ${{ number_format($inv->amount_due, 2) }}
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $inv->owner?->name ?? '—' }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ route('finance.invoices.show', $inv) }}"
                   class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                  <i class="bi bi-eye" style="font-size:.8rem;"></i>
                </a>
                <a href="{{ route('finance.invoices.edit', $inv) }}"
                   class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
                  <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                </a>
                <a href="{{ route('finance.invoices.pdf', $inv) }}"
                   class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Download PDF" target="_blank">
                  <i class="bi bi-file-earmark-pdf" style="font-size:.8rem;"></i>
                </a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($invoices->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-top:1px solid var(--border-color); font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}
    </span>
    {{ $invoices->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
