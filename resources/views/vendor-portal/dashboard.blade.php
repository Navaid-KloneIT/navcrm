@extends('vendor-portal.layout')

@section('title', 'Vendor Dashboard')

@section('content')
<h1 class="h4 fw-bold mb-1">Welcome, {{ $vendor->company_name }}</h1>
<p class="text-muted mb-4" style="font-size:.85rem;">{{ $vendor->vendor_number }}</p>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6">
    <div class="ncv-card">
      <div class="ncv-card-body text-center py-4">
        <div class="fw-bold" style="font-size:2rem;color:var(--accent-blue);">{{ $stats['open_pos'] }}</div>
        <div class="text-muted" style="font-size:.82rem;">Open Purchase Orders</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="ncv-card">
      <div class="ncv-card-body text-center py-4">
        <div class="fw-bold" style="font-size:2rem;color:#22c55e;">${{ number_format($stats['total_value'], 2) }}</div>
        <div class="text-muted" style="font-size:.82rem;">Total PO Value</div>
      </div>
    </div>
  </div>
</div>

{{-- Recent POs --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-header d-flex align-items-center justify-content-between">
    <span class="ncv-card-title">Recent Purchase Orders</span>
    <a href="{{ route('vendor-portal.purchase-orders') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">View All</a>
  </div>
  <div class="ncv-card-body p-0">
    @if($recentPOs->isEmpty())
      <div class="text-center py-4 text-muted small">No purchase orders yet.</div>
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
          @foreach($recentPOs as $po)
          <tr>
            <td style="font-size:.85rem;font-weight:600;">{{ $po->po_number }}</td>
            <td>
              <span class="ncv-badge bg-{{ $po->status->color() }}-subtle text-{{ $po->status->color() }}" style="font-size:.72rem;">
                {{ $po->status->label() }}
              </span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->order_date->format('M j, Y') }}</td>
            <td style="text-align:right;font-weight:700;">${{ number_format($po->total_amount, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>

{{-- Quick Links --}}
<div class="row g-3">
  <div class="col-sm-4">
    <a href="{{ route('vendor-portal.purchase-orders') }}" class="ncv-card text-decoration-none d-block">
      <div class="ncv-card-body text-center py-4">
        <i class="bi bi-cart" style="font-size:1.5rem;color:var(--accent-blue);"></i>
        <div class="mt-2 fw-medium" style="font-size:.88rem;color:var(--text-primary);">Purchase Orders</div>
      </div>
    </a>
  </div>
  <div class="col-sm-4">
    <a href="{{ route('vendor-portal.stock-check') }}" class="ncv-card text-decoration-none d-block">
      <div class="ncv-card-body text-center py-4">
        <i class="bi bi-box-seam" style="font-size:1.5rem;color:#22c55e;"></i>
        <div class="mt-2 fw-medium" style="font-size:.88rem;color:var(--text-primary);">Stock Check</div>
      </div>
    </a>
  </div>
  <div class="col-sm-4">
    <a href="{{ route('vendor-portal.register-lead') }}" class="ncv-card text-decoration-none d-block">
      <div class="ncv-card-body text-center py-4">
        <i class="bi bi-person-plus" style="font-size:1.5rem;color:#f59e0b;"></i>
        <div class="mt-2 fw-medium" style="font-size:.88rem;color:var(--text-primary);">Register Lead</div>
      </div>
    </a>
  </div>
</div>
@endsection
