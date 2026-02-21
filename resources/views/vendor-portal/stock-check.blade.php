@extends('vendor-portal.layout')

@section('title', 'Stock Check')

@section('content')
<h1 class="h4 fw-bold mb-4">Stock Check</h1>

<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($products->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="bi bi-box-seam" style="font-size:2rem;opacity:.4;"></i>
        <p class="mt-3 mb-0">No products available.</p>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Product</th>
            <th>Unit</th>
            <th style="text-align:right;">In Stock</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $product)
          <tr>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $product->sku }}</td>
            <td style="font-size:.85rem;font-weight:500;">{{ $product->name }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $product->unit ?? '—' }}</td>
            <td style="text-align:right;font-weight:700;font-size:.88rem;">{{ number_format($product->stock_quantity) }}</td>
            <td>
              @if($product->stock_quantity <= 0)
                <span class="ncv-badge bg-danger-subtle text-danger" style="font-size:.72rem;">Out of Stock</span>
              @elseif($product->reorder_level > 0 && $product->stock_quantity <= $product->reorder_level)
                <span class="ncv-badge bg-warning-subtle text-warning" style="font-size:.72rem;">Low Stock</span>
              @else
                <span class="ncv-badge bg-success-subtle text-success" style="font-size:.72rem;">Available</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
