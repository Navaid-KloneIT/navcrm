@extends('layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'New Product')
@section('page-title', isset($product) ? 'Edit Product' : 'New Product')

@section('breadcrumb-items')
  <a href="{{ route('products.index') }}" style="color:inherit;text-decoration:none;">Products</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($product) ? 'Edit Product' : 'New Product' }}</h1>
        <p class="ncv-page-subtitle">Add or update a product in your catalog.</p>
      </div>
      <a href="{{ route('products.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}">
      @csrf
      @if(isset($product)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Product Info --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-box-seam me-2" style="color:var(--ncv-blue-500);"></i>Product Information</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-8">
                  <label class="ncv-label" for="name">Product Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $product->name ?? '') }}"
                         placeholder="NavCRM Enterprise" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="sku">SKU / Product Code</label>
                  <input type="text" class="ncv-input" id="sku" name="sku"
                         value="{{ old('sku', $product->sku ?? '') }}"
                         placeholder="NCR-ENT-001" />
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="description">Description</label>
                  <textarea class="ncv-textarea" id="description" name="description" rows="3"
                            placeholder="Full CRM suite with all features, unlimited users, 24/7 support…">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="category">Category</label>
                  <select class="ncv-select" id="category" name="category">
                    @foreach(['Software','Hardware','Service','Add-On','Subscription','One-Time','Other'] as $cat)
                    <option value="{{ $cat }}" {{ old('category', $product->category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="unit">Unit of Measure</label>
                  <select class="ncv-select" id="unit" name="unit">
                    @foreach(['Each','Monthly','Annual','Hour','Day','License','Seat','GB'] as $u)
                    <option value="{{ $u }}" {{ old('unit', $product->unit ?? 'Each') === $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label">Status</label>
                  <div style="display:flex;gap:.625rem;margin-top:.25rem;">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem;color:var(--text-secondary);">
                      <input type="radio" name="is_active" value="1" style="accent-color:#2563eb;" {{ old('is_active', $product->is_active ?? 1) == 1 ? 'checked' : '' }} /> Active
                    </label>
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem;color:var(--text-secondary);">
                      <input type="radio" name="is_active" value="0" style="accent-color:#2563eb;" {{ old('is_active', $product->is_active ?? 1) == 0 ? 'checked' : '' }} /> Inactive
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Pricing --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-currency-dollar me-2" style="color:var(--ncv-blue-500);"></i>Pricing</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="unit_price">List Price <span class="required">*</span></label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="number" step="0.01" class="ncv-input @error('unit_price') is-invalid @enderror"
                           id="unit_price" name="unit_price"
                           value="{{ old('unit_price', $product->unit_price ?? '') }}"
                           placeholder="0.00" required oninput="calcMargin()" />
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="cost">Standard Cost</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="number" step="0.01" class="ncv-input"
                           id="cost" name="cost"
                           value="{{ old('cost', $product->cost ?? '') }}"
                           placeholder="0.00" oninput="calcMargin()" />
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label">Gross Margin</label>
                  <div style="height:42px;display:flex;align-items:center;padding:0 .875rem;background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:.625rem;">
                    <span id="marginDisplay" style="font-size:1.1rem;font-weight:800;color:#0891b2;">— %</span>
                  </div>
                </div>
                <div class="col-12">
                  <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:.625rem;padding:.75rem 1rem;font-size:.82rem;color:#92400e;display:flex;align-items:center;gap:.625rem;">
                    <i class="bi bi-info-circle-fill"></i>
                    The list price here is the standard price. You can override it in specific <strong>Price Books</strong> for different regions or customer tiers.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('products.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($product) ? 'Update Product' : 'Create Product' }}
            </button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function calcMargin() {
    const price = parseFloat(document.getElementById('unit_price').value) || 0;
    const cost  = parseFloat(document.getElementById('cost').value)       || 0;
    const display = document.getElementById('marginDisplay');
    if (price > 0) {
      const margin = ((price - cost) / price * 100).toFixed(1);
      display.textContent = margin + '%';
      display.style.color = margin >= 70 ? '#059669' : margin >= 40 ? '#d97706' : '#ef4444';
    } else {
      display.textContent = '— %';
      display.style.color = '#0891b2';
    }
  }
</script>
@endpush
