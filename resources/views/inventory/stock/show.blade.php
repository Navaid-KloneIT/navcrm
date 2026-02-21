@extends('layouts.app')

@section('title', $product->name . ' — Stock')
@section('page-title', $product->name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('inventory.stock.index') }}" class="ncv-breadcrumb-item">Stock</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Product Hero --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div>
        <h2 class="mb-1 fw-bold" style="font-size:1.25rem;">{{ $product->name }}</h2>
        <span class="text-muted" style="font-size:.82rem;">SKU: {{ $product->sku }}</span>
        @if($product->stock_quantity <= 0)
          <span class="ncv-badge bg-danger-subtle text-danger ms-2" style="font-size:.72rem;">Out of Stock</span>
        @elseif($product->is_low_stock)
          <span class="ncv-badge bg-warning-subtle text-warning ms-2" style="font-size:.72rem;">Low Stock</span>
        @else
          <span class="ncv-badge bg-success-subtle text-success ms-2" style="font-size:.72rem;">In Stock</span>
        @endif
      </div>
      <div class="d-flex gap-3">
        <div class="text-center">
          <div class="fw-bold" style="font-size:1.5rem;">{{ number_format($product->stock_quantity) }}</div>
          <div class="text-muted" style="font-size:.75rem;">Current Stock</div>
        </div>
        <div class="text-center">
          <div class="fw-bold" style="font-size:1.5rem;">{{ $product->reorder_level > 0 ? number_format($product->reorder_level) : '—' }}</div>
          <div class="text-muted" style="font-size:.75rem;">Reorder Level</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- Stock Movement History --}}
  <div class="col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Stock Movement History</span></div>
      <div class="ncv-card-body p-0">
        @if($movements->isEmpty())
          <div class="text-center py-4" style="color:var(--text-muted);">
            <p class="mb-0 small">No stock movements recorded.</p>
          </div>
        @else
          <table class="ncv-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th style="text-align:right;">Quantity</th>
                <th>Notes</th>
                <th>By</th>
              </tr>
            </thead>
            <tbody>
              @foreach($movements as $m)
              <tr>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $m->created_at->format('M j, Y H:i') }}</td>
                <td>
                  <span class="ncv-badge bg-{{ $m->type->color() }}-subtle text-{{ $m->type->color() }}" style="font-size:.72rem;">
                    {{ $m->type->label() }}
                  </span>
                </td>
                <td style="text-align:right;font-weight:700;font-size:.85rem;color:{{ $m->quantity >= 0 ? '#22c55e' : '#ef4444' }};">
                  {{ $m->quantity >= 0 ? '+' : '' }}{{ $m->quantity }}
                </td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ Str::limit($m->notes, 50) ?? '—' }}</td>
                <td style="font-size:.82rem;color:var(--text-muted);">{{ $m->creator?->name ?? 'System' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
      @if($movements->hasPages())
      <div class="d-flex align-items-center justify-content-between px-3 py-2"
           style="border-top:1px solid var(--border-color); font-size:.82rem;">
        <span style="color:var(--text-muted);">
          Showing {{ $movements->firstItem() }}–{{ $movements->lastItem() }} of {{ $movements->total() }}
        </span>
        {{ $movements->links('pagination::bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>

  {{-- Manual Adjustment --}}
  <div class="col-lg-4">
    <div class="ncv-card">
      <div class="ncv-card-header"><span class="ncv-card-title">Manual Adjustment</span></div>
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('inventory.stock.adjust', $product) }}">
          @csrf

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Quantity <span class="text-danger">*</span></label>
            <input type="number" name="quantity" class="ncv-input @error('quantity') is-invalid @enderror"
                   placeholder="e.g. 10 or -5" required>
            <small class="text-muted">Positive to add, negative to remove.</small>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Reason <span class="text-danger">*</span></label>
            <textarea name="notes" rows="3" class="ncv-input @error('notes') is-invalid @enderror"
                      style="height:auto;" placeholder="Reason for adjustment…" required></textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="ncv-btn ncv-btn-primary w-100">
            <i class="bi bi-arrow-repeat me-1"></i> Adjust Stock
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
