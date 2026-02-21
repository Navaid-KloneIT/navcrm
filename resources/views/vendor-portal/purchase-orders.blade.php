@extends('vendor-portal.layout')

@section('title', 'My Purchase Orders')

@section('content')
<h1 class="h4 fw-bold mb-4">My Purchase Orders</h1>

<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($purchaseOrders->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="bi bi-cart" style="font-size:2rem;opacity:.4;"></i>
        <p class="mt-3 mb-0">No purchase orders found.</p>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>PO #</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Expected</th>
            <th>Items</th>
            <th style="text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($purchaseOrders as $po)
          <tr>
            <td style="font-size:.85rem;font-weight:600;">{{ $po->po_number }}</td>
            <td>
              <span class="ncv-badge bg-{{ $po->status->color() }}-subtle text-{{ $po->status->color() }}" style="font-size:.72rem;">
                {{ $po->status->label() }}
              </span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->order_date->format('M j, Y') }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $po->expected_date?->format('M j, Y') ?? '—' }}</td>
            <td style="font-size:.82rem;">
              @foreach($po->items as $item)
                <span class="d-block">{{ $item->product->name }} x {{ number_format($item->quantity) }}</span>
              @endforeach
            </td>
            <td style="text-align:right;font-weight:700;">${{ number_format($po->total_amount, 2) }}</td>
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
