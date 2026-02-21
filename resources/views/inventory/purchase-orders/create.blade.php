@extends('layouts.app')

@section('title', isset($purchaseOrder) ? 'Edit Purchase Order' : 'New Purchase Order')
@section('page-title', isset($purchaseOrder) ? 'Edit Purchase Order' : 'New Purchase Order')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('inventory.purchase-orders.index') }}" class="ncv-breadcrumb-item">Purchase Orders</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .line-items-table th, .line-items-table td { padding:.4rem .5rem; font-size:.82rem; vertical-align:middle; }
  .line-items-table input, .line-items-table select { font-size:.82rem; height:32px; }
</style>
@endpush

@section('content')

<form method="POST"
      action="{{ isset($purchaseOrder) ? route('inventory.purchase-orders.update', $purchaseOrder) : route('inventory.purchase-orders.store') }}"
      id="poForm">
  @csrf
  @if(isset($purchaseOrder)) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-8">
      {{-- PO Details --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header"><span class="ncv-card-title">Purchase Order Details</span></div>
        <div class="ncv-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Vendor <span class="text-danger">*</span></label>
              <select name="vendor_id" class="ncv-select @error('vendor_id') is-invalid @enderror" required>
                <option value="">— Select vendor —</option>
                @foreach($vendors as $v)
                  <option value="{{ $v->id }}" {{ old('vendor_id', $purchaseOrder?->vendor_id) == $v->id ? 'selected' : '' }}>
                    {{ $v->company_name }}
                  </option>
                @endforeach
              </select>
              @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Order Date <span class="text-danger">*</span></label>
              <input type="date" name="order_date"
                     value="{{ old('order_date', $purchaseOrder?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                     class="ncv-input @error('order_date') is-invalid @enderror" required>
              @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Expected Date</label>
              <input type="date" name="expected_date"
                     value="{{ old('expected_date', $purchaseOrder?->expected_date?->format('Y-m-d')) }}"
                     class="ncv-input">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Notes</label>
              <textarea name="notes" rows="2" class="ncv-input" style="height:auto;">{{ old('notes', $purchaseOrder?->notes) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Line Items --}}
      <div class="ncv-card">
        <div class="ncv-card-header d-flex align-items-center justify-content-between">
          <span class="ncv-card-title">Line Items</span>
          <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="addLineItem()">
            <i class="bi bi-plus-lg"></i> Add Item
          </button>
        </div>
        <div class="ncv-card-body p-0">
          <table class="ncv-table line-items-table">
            <thead>
              <tr>
                <th style="width:30%;">Product</th>
                <th>Description</th>
                <th style="width:80px;">Qty</th>
                <th style="width:100px;">Unit Price</th>
                <th style="width:80px;">Tax %</th>
                <th style="width:40px;"></th>
              </tr>
            </thead>
            <tbody id="lineItemsBody">
              @if(isset($purchaseOrder) && $purchaseOrder->items->count())
                @foreach($purchaseOrder->items as $i => $item)
                <tr>
                  <td>
                    <select name="items[{{ $i }}][product_id]" class="ncv-select" required>
                      <option value="">— Product —</option>
                      @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="text" name="items[{{ $i }}][description]" value="{{ $item->description }}" class="ncv-input" placeholder="Optional"></td>
                  <td><input type="number" name="items[{{ $i }}][quantity]" value="{{ $item->quantity }}" class="ncv-input" min="0.01" step="0.01" required></td>
                  <td><input type="number" name="items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}" class="ncv-input" min="0" step="0.01" required></td>
                  <td><input type="number" name="items[{{ $i }}][tax_rate]" value="{{ $item->tax_rate }}" class="ncv-input" min="0" max="100" step="0.01"></td>
                  <td><button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm text-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button></td>
                </tr>
                @endforeach
              @else
                <tr>
                  <td>
                    <select name="items[0][product_id]" class="ncv-select" required>
                      <option value="">— Product —</option>
                      @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="text" name="items[0][description]" class="ncv-input" placeholder="Optional"></td>
                  <td><input type="number" name="items[0][quantity]" class="ncv-input" min="0.01" step="0.01" value="1" required></td>
                  <td><input type="number" name="items[0][unit_price]" class="ncv-input" min="0" step="0.01" value="0" required></td>
                  <td><input type="number" name="items[0][tax_rate]" class="ncv-input" min="0" max="100" step="0.01" value="0"></td>
                  <td><button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm text-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button></td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Status</label>
            <select name="status" class="ncv-select">
              <option value="draft" {{ old('status', $purchaseOrder?->status?->value ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
              <option value="submitted" {{ old('status', $purchaseOrder?->status?->value) === 'submitted' ? 'selected' : '' }}>Submitted</option>
            </select>
          </div>

          <button type="submit" class="ncv-btn ncv-btn-primary w-100 mb-2">
            <i class="bi bi-check-lg me-1"></i>
            {{ isset($purchaseOrder) ? 'Update PO' : 'Create PO' }}
          </button>
          <a href="{{ route('inventory.purchase-orders.index') }}" class="ncv-btn ncv-btn-outline w-100">Cancel</a>

          @if(isset($purchaseOrder))
          <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
            <form method="POST" action="{{ route('inventory.purchase-orders.destroy', $purchaseOrder) }}"
                  onsubmit="return confirm('Delete this purchase order?')">
              @csrf @method('DELETE')
              <button type="submit" class="ncv-btn ncv-btn-ghost w-100 text-danger">
                <i class="bi bi-trash me-1"></i>Delete
              </button>
            </form>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</form>

@endsection

@push('scripts')
<script>
  var lineItemIdx = {{ isset($purchaseOrder) ? $purchaseOrder->items->count() : 1 }};
  var productsJson = @json($products);

  function addLineItem() {
    var opts = '<option value="">— Product —</option>';
    productsJson.forEach(function(p) {
      opts += '<option value="'+p.id+'">'+p.name+' ('+p.sku+')</option>';
    });
    var row = '<tr>'
      +'<td><select name="items['+lineItemIdx+'][product_id]" class="ncv-select" required>'+opts+'</select></td>'
      +'<td><input type="text" name="items['+lineItemIdx+'][description]" class="ncv-input" placeholder="Optional"></td>'
      +'<td><input type="number" name="items['+lineItemIdx+'][quantity]" class="ncv-input" min="0.01" step="0.01" value="1" required></td>'
      +'<td><input type="number" name="items['+lineItemIdx+'][unit_price]" class="ncv-input" min="0" step="0.01" value="0" required></td>'
      +'<td><input type="number" name="items['+lineItemIdx+'][tax_rate]" class="ncv-input" min="0" max="100" step="0.01" value="0"></td>'
      +'<td><button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm text-danger" onclick="this.closest(\'tr\').remove()"><i class="bi bi-x-lg"></i></button></td>'
      +'</tr>';
    document.getElementById('lineItemsBody').insertAdjacentHTML('beforeend', row);
    lineItemIdx++;
  }
</script>
@endpush
