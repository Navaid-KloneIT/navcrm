@extends('layouts.app')

@section('title', $purchaseOrder->po_number)
@section('page-title', $purchaseOrder->po_number)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('inventory.purchase-orders.index') }}" class="ncv-breadcrumb-item">Purchase Orders</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Hero --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div>
        <h2 class="mb-1 fw-bold" style="font-size:1.25rem;">{{ $purchaseOrder->po_number }}</h2>
        <span class="text-muted" style="font-size:.82rem;">
          Vendor: <a href="{{ route('inventory.vendors.show', $purchaseOrder->vendor) }}" style="color:var(--accent-blue);text-decoration:none;">
            {{ $purchaseOrder->vendor->company_name }}
          </a>
        </span>
        <span class="ncv-badge bg-{{ $purchaseOrder->status->color() }}-subtle text-{{ $purchaseOrder->status->color() }} ms-2" style="font-size:.72rem;">
          {{ $purchaseOrder->status->label() }}
        </span>
      </div>
      <div class="d-flex gap-2">
        @if($purchaseOrder->status->value === 'submitted')
          <form method="POST" action="{{ route('inventory.purchase-orders.approve', $purchaseOrder) }}">
            @csrf
            <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">
              <i class="bi bi-check-circle me-1"></i> Approve
            </button>
          </form>
        @endif

        @if($purchaseOrder->status->value === 'approved')
          <button type="button" class="ncv-btn ncv-btn-success ncv-btn-sm" data-bs-toggle="modal" data-bs-target="#receiveModal">
            <i class="bi bi-box-arrow-in-down me-1"></i> Mark Received
          </button>
        @endif

        @if(in_array($purchaseOrder->status->value, ['draft', 'submitted']))
          <a href="{{ route('inventory.purchase-orders.edit', $purchaseOrder) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
          </a>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- Details --}}
  <div class="col-lg-4">
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Details</span></div>
      <div class="ncv-card-body" style="font-size:.85rem;">
        <table class="w-100">
          <tr><td class="text-muted pe-3 py-1" style="width:110px;">Order Date</td><td class="py-1">{{ $purchaseOrder->order_date->format('M j, Y') }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Expected</td><td class="py-1">{{ $purchaseOrder->expected_date?->format('M j, Y') ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Received</td><td class="py-1">{{ $purchaseOrder->received_date?->format('M j, Y') ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Created By</td><td class="py-1">{{ $purchaseOrder->creator?->name ?? '—' }}</td></tr>
          <tr><td class="text-muted pe-3 py-1">Approved By</td><td class="py-1">{{ $purchaseOrder->approver?->name ?? '—' }}</td></tr>
          @if($purchaseOrder->approved_at)
          <tr><td class="text-muted pe-3 py-1">Approved At</td><td class="py-1">{{ $purchaseOrder->approved_at->format('M j, Y H:i') }}</td></tr>
          @endif
        </table>
        <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
          <table class="w-100" style="font-size:.88rem;">
            <tr><td class="text-muted py-1">Subtotal</td><td class="py-1 text-end">${{ number_format($purchaseOrder->subtotal, 2) }}</td></tr>
            <tr><td class="text-muted py-1">Tax</td><td class="py-1 text-end">${{ number_format($purchaseOrder->tax_amount, 2) }}</td></tr>
            <tr style="font-weight:700;font-size:.95rem;"><td class="py-1">Total</td><td class="py-1 text-end">${{ number_format($purchaseOrder->total_amount, 2) }}</td></tr>
          </table>
        </div>
        @if($purchaseOrder->notes)
          <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
            <p class="text-muted mb-1" style="font-size:.78rem;font-weight:600;">Notes</p>
            <p class="mb-0" style="font-size:.84rem;">{{ $purchaseOrder->notes }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Line Items --}}
  <div class="col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Line Items</span></div>
      <div class="ncv-card-body p-0">
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Description</th>
              <th style="text-align:right;">Qty</th>
              <th style="text-align:right;">Unit Price</th>
              <th style="text-align:right;">Tax %</th>
              <th style="text-align:right;">Total</th>
              @if($purchaseOrder->status->value === 'received')
              <th style="text-align:right;">Received</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($purchaseOrder->items as $item)
            <tr>
              <td style="font-size:.85rem;">
                <a href="{{ route('inventory.stock.show', $item->product) }}" style="color:var(--accent-blue);text-decoration:none;">
                  {{ $item->product->name }}
                </a>
                <span class="text-muted d-block" style="font-size:.75rem;">{{ $item->product->sku }}</span>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted);">{{ $item->description ?? '—' }}</td>
              <td style="text-align:right;font-size:.85rem;">{{ number_format($item->quantity, 2) }}</td>
              <td style="text-align:right;font-size:.85rem;">${{ number_format($item->unit_price, 2) }}</td>
              <td style="text-align:right;font-size:.85rem;">{{ number_format($item->tax_rate, 2) }}%</td>
              <td style="text-align:right;font-weight:700;font-size:.85rem;">${{ number_format($item->total, 2) }}</td>
              @if($purchaseOrder->status->value === 'received')
              <td style="text-align:right;font-size:.85rem;">{{ number_format($item->received_quantity, 2) }}</td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Receive Modal --}}
@if($purchaseOrder->status->value === 'approved')
<div class="modal fade" id="receiveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('inventory.purchase-orders.receive', $purchaseOrder) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Receive Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Enter the quantities received for each item.</p>
          <table class="table table-sm">
            <thead><tr><th>Product</th><th>Ordered</th><th>Received</th></tr></thead>
            <tbody>
              @foreach($purchaseOrder->items as $item)
              <tr>
                <td style="font-size:.85rem;">{{ $item->product->name }}</td>
                <td style="font-size:.85rem;">{{ number_format($item->quantity, 2) }}</td>
                <td>
                  <input type="number" name="received_quantities[{{ $item->id }}]"
                         value="{{ $item->quantity }}" min="0" step="0.01"
                         class="form-control form-control-sm" style="width:100px;" required>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i> Confirm Received</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection
