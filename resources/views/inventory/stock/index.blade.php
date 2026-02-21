@extends('layouts.app')

@section('title', 'Stock Management')
@section('page-title', 'Stock Management')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .stock-bar { height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden; min-width:80px; }
  .stock-bar-fill { height:100%; border-radius:3px; transition:width .3s; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Stock Management</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">
      {{ $stats['total_products'] }} products &middot;
      <span class="{{ $stats['low_stock'] > 0 ? 'text-warning fw-bold' : '' }}">{{ $stats['low_stock'] }} low stock</span> &middot;
      <span class="{{ $stats['out_of_stock'] > 0 ? 'text-danger fw-bold' : '' }}">{{ $stats['out_of_stock'] }} out of stock</span>
    </p>
  </div>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('inventory.stock.index') }}" class="filter-bar mb-3">
  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search products…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:220px;">
  </div>

  <div class="form-check form-check-inline ms-2">
    <input type="checkbox" name="low_stock" value="1" class="form-check-input"
           {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
    <label class="form-check-label" style="font-size:.82rem;">Low Stock Only</label>
  </div>

  <div class="form-check form-check-inline">
    <input type="checkbox" name="out_of_stock" value="1" class="form-check-input"
           {{ request('out_of_stock') ? 'checked' : '' }} onchange="this.form.submit()">
    <label class="form-check-label" style="font-size:.82rem;">Out of Stock Only</label>
  </div>

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','low_stock','out_of_stock']))
    <a href="{{ route('inventory.stock.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($products->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-box-seam" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No products found</p>
        <p class="small mb-0">Adjust the filters to see stock levels.</p>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Product</th>
            <th>Unit</th>
            <th style="text-align:right;">In Stock</th>
            <th style="text-align:right;">Reorder Level</th>
            <th style="width:120px;">Level</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $product)
          @php
            $pct = $product->reorder_level > 0 ? min(100, ($product->stock_quantity / ($product->reorder_level * 3)) * 100) : ($product->stock_quantity > 0 ? 100 : 0);
            $barColor = $product->stock_quantity <= 0 ? '#ef4444' : ($product->is_low_stock ? '#f59e0b' : '#22c55e');
          @endphp
          <tr>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $product->sku }}</td>
            <td>
              <a href="{{ route('inventory.stock.show', $product) }}"
                 class="ncv-table-cell-primary text-decoration-none" style="color:inherit;font-size:.875rem;">
                {{ $product->name }}
              </a>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $product->unit ?? '—' }}</td>
            <td style="text-align:right;font-weight:700;font-size:.88rem;">{{ number_format($product->stock_quantity) }}</td>
            <td style="text-align:right;font-size:.82rem;color:var(--text-muted);">{{ $product->reorder_level > 0 ? number_format($product->reorder_level) : '—' }}</td>
            <td>
              <div class="stock-bar">
                <div class="stock-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
              </div>
            </td>
            <td>
              @if($product->stock_quantity <= 0)
                <span class="ncv-badge bg-danger-subtle text-danger" style="font-size:.72rem;">Out of Stock</span>
              @elseif($product->is_low_stock)
                <span class="ncv-badge bg-warning-subtle text-warning" style="font-size:.72rem;">Low Stock</span>
              @else
                <span class="ncv-badge bg-success-subtle text-success" style="font-size:.72rem;">In Stock</span>
              @endif
            </td>
            <td>
              <a href="{{ route('inventory.stock.show', $product) }}"
                 class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View History">
                <i class="bi bi-clock-history" style="font-size:.8rem;"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($products->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-top:1px solid var(--border-color); font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
    </span>
    {{ $products->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
