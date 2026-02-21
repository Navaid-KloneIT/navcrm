@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

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
    <h1 class="ncv-page-title mb-0">Purchase Orders</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">
      {{ $stats['total'] }} total &middot;
      {{ $stats['draft'] }} draft &middot;
      {{ $stats['approved'] }} approved &middot;
      ${{ number_format($stats['total_value'], 2) }} total value
    </p>
  </div>
  <a href="{{ route('inventory.purchase-orders.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New PO
  </a>
</div>

{{-- Status chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ route('inventory.purchase-orders.index', request()->except('status','page')) }}"
     class="ncv-chip {{ !request('status') ? 'active' : '' }}" style="font-size:.78rem;">All</a>
  @foreach(\App\Enums\PurchaseOrderStatus::cases() as $s)
  <a href="{{ route('inventory.purchase-orders.index', array_merge(request()->except('status','page'), ['status' => $s->value])) }}"
     class="ncv-chip {{ request('status') === $s->value ? 'active' : '' }}" style="font-size:.78rem;">
    {{ $s->label() }}
  </a>
  @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('inventory.purchase-orders.index') }}" class="filter-bar mb-3">
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search PO#…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:180px;">
  </div>

  <select name="vendor_id" class="ncv-select ncv-select-sm" style="width:180px;">
    <option value="">All Vendors</option>
    @foreach($vendors as $v)
      <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->company_name }}</option>
    @endforeach
  </select>

  <input type="date" name="date_from" value="{{ request('date_from') }}"
         class="ncv-input ncv-input-sm" title="From">
  <input type="date" name="date_to" value="{{ request('date_to') }}"
         class="ncv-input ncv-input-sm" title="To">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','vendor_id','date_from','date_to','status']))
    <a href="{{ route('inventory.purchase-orders.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($purchaseOrders->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-cart" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No purchase orders found</p>
        <p class="small mb-3">Create a new purchase order or adjust the filters.</p>
        <a href="{{ route('inventory.purchase-orders.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New PO
        </a>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>PO #</th>
            <th>Vendor</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Expected</th>
            <th style="text-align:right;">Total</th>
            <th>Created By</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($purchaseOrders as $po)
          <tr>
            <td>
              <a href="{{ route('inventory.purchase-orders.show', $po) }}"
                 class="ncv-table-cell-primary text-decoration-none" style="color:inherit;font-size:.875rem;">
                {{ $po->po_number }}
              </a>
            </td>
            <td style="font-size:.82rem;">
              <a href="{{ route('inventory.vendors.show', $po->vendor) }}" style="color:var(--accent-blue);text-decoration:none;">
                {{ $po->vendor->company_name }}
              </a>
            </td>
            <td>
              <span class="ncv-badge bg-{{ $po->status->color() }}-subtle text-{{ $po->status->color() }}" style="font-size:.72rem;">
                {{ $po->status->label() }}
              </span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->order_date->format('M j, Y') }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->expected_date?->format('M j, Y') ?? '—' }}</td>
            <td style="text-align:right;font-weight:700;">${{ number_format($po->total_amount, 2) }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->creator?->name ?? '—' }}</td>
            <td>
              <a href="{{ route('inventory.purchase-orders.show', $po) }}"
                 class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                <i class="bi bi-eye" style="font-size:.8rem;"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($purchaseOrders->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-top:1px solid var(--border-color); font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }}
    </span>
    {{ $purchaseOrders->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
