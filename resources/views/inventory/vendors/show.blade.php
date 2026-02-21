@extends('layouts.app')

@section('title', $vendor->company_name)
@section('page-title', $vendor->company_name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('inventory.vendors.index') }}" class="ncv-breadcrumb-item">Vendors</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Hero --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div>
        <h2 class="mb-1 fw-bold" style="font-size:1.25rem;">{{ $vendor->company_name }}</h2>
        <span class="text-muted" style="font-size:.82rem;">{{ $vendor->vendor_number }}</span>
        <span class="ncv-badge bg-{{ $vendor->status->color() }}-subtle text-{{ $vendor->status->color() }} ms-2" style="font-size:.72rem;">
          {{ $vendor->status->label() }}
        </span>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('inventory.vendors.edit', $vendor) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
          <i class="bi bi-pencil me-1"></i> Edit
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- Details --}}
  <div class="col-lg-5">
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Details</span></div>
      <div class="ncv-card-body" style="font-size:.85rem;">
        <table class="w-100">
          <tr><td class="text-muted pe-3 py-1" style="width:120px;">Contact</td><td class="py-1">{{ $vendor->contact_name ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Email</td><td class="py-1">{{ $vendor->email ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Phone</td><td class="py-1">{{ $vendor->phone ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Address</td><td class="py-1">{{ $vendor->address ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">City</td><td class="py-1">{{ $vendor->city ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">State</td><td class="py-1">{{ $vendor->state ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Country</td><td class="py-1">{{ $vendor->country ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Website</td><td class="py-1">
            @if($vendor->website)
              <a href="{{ $vendor->website }}" target="_blank" style="color:var(--accent-blue);">{{ $vendor->website }}</a>
            @else — @endif
          </td></tr>
          <tr><td class="text-muted pe-3 py-1">Portal</td><td class="py-1">
            @if($vendor->portal_active)
              <span class="ncv-badge bg-success-subtle text-success" style="font-size:.72rem;">Enabled</span>
            @else
              <span class="ncv-badge bg-secondary-subtle text-secondary" style="font-size:.72rem;">Disabled</span>
            @endif
          </td></tr>
        </table>
        @if($vendor->notes)
          <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
            <p class="text-muted mb-1" style="font-size:.78rem;font-weight:600;">Notes</p>
            <p class="mb-0" style="font-size:.84rem;">{{ $vendor->notes }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Purchase Orders --}}
  <div class="col-lg-7">
    <div class="ncv-card">
      <div class="ncv-card-header d-flex align-items-center justify-content-between">
        <span class="ncv-card-title">Purchase Orders</span>
        <a href="{{ route('inventory.purchase-orders.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New PO
        </a>
      </div>
      <div class="ncv-card-body p-0">
        @if($vendor->purchaseOrders->isEmpty())
          <div class="text-center py-4" style="color:var(--text-muted);">
            <p class="mb-0 small">No purchase orders yet.</p>
          </div>
        @else
          <table class="ncv-table">
            <thead>
              <tr>
                <th>PO #</th>
                <th>Status</th>
                <th>Order Date</th>
                <th style="text-align:right;">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vendor->purchaseOrders as $po)
              <tr>
                <td>
                  <a href="{{ route('inventory.purchase-orders.show', $po) }}" style="color:var(--accent-blue);text-decoration:none;font-size:.85rem;">
                    {{ $po->po_number }}
                  </a>
                </td>
                <td>
                  <span class="ncv-badge bg-{{ $po->status->color() }}-subtle text-{{ $po->status->color() }}" style="font-size:.72rem;">
                    {{ $po->status->label() }}
                  </span>
                </td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->order_date->format('M j, Y') }}</td>
                <td style="text-align:right;font-weight:700;font-size:.85rem;">${{ number_format($po->total_amount, 2) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
