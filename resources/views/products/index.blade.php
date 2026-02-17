@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Product Catalog</h1>
    <p class="ncv-page-subtitle">Manage products, services, and pricing.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('price-books.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-book"></i> Price Books
    </a>
    <a href="{{ route('products.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Product
    </a>
  </div>
</div>

{{-- Filters --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div style="position:relative;min-width:240px;flex:1;">
        <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
        <input type="text" placeholder="Search by name, SKU, or category…" class="ncv-input" style="padding-left:2.375rem;" />
      </div>
      <select class="ncv-select" style="width:150px;height:38px;font-size:.82rem;">
        <option>All Categories</option>
        <option>Software</option>
        <option>Hardware</option>
        <option>Service</option>
        <option>Add-On</option>
      </select>
      <div class="d-flex gap-1 flex-wrap">
        <button class="ncv-chip active">All</button>
        <button class="ncv-chip">Active</button>
        <button class="ncv-chip">Inactive</button>
      </div>
    </div>
  </div>
</div>

{{-- Products Table --}}
<div class="ncv-table-wrapper">
  <table class="ncv-table">
    <thead>
      <tr>
        <th class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></th>
        <th class="sorted">Product <i class="bi bi-arrow-up ms-1" style="font-size:.65rem;"></i></th>
        <th>SKU</th>
        <th>Category</th>
        <th>Unit Price</th>
        <th>Cost</th>
        <th>Margin</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach([
        ['id'=>1,'name'=>'NavCRM Enterprise','desc'=>'Full CRM suite — unlimited users',        'sku'=>'NCR-ENT-001','cat'=>'Software', 'price'=>'$2,500/mo','cost'=>'$480','margin'=>'81%','active'=>true, 'color'=>'#2563eb'],
        ['id'=>2,'name'=>'NavCRM Pro',        'desc'=>'CRM suite — up to 25 users',            'sku'=>'NCR-PRO-001','cat'=>'Software', 'price'=>'$499/mo', 'cost'=>'$95', 'margin'=>'81%','active'=>true, 'color'=>'#10b981'],
        ['id'=>3,'name'=>'NavCRM Starter',    'desc'=>'Basic CRM — up to 5 users',             'sku'=>'NCR-STR-001','cat'=>'Software', 'price'=>'$99/mo',  'cost'=>'$20', 'margin'=>'80%','active'=>true, 'color'=>'#8b5cf6'],
        ['id'=>4,'name'=>'API Access Module', 'desc'=>'REST API with 100k calls/day',          'sku'=>'NCR-API-001','cat'=>'Add-On',   'price'=>'$199/mo', 'cost'=>'$35', 'margin'=>'82%','active'=>true, 'color'=>'#f59e0b'],
        ['id'=>5,'name'=>'AI Forecasting',    'desc'=>'Machine learning revenue predictions',   'sku'=>'NCR-AI-001', 'cat'=>'Add-On',   'price'=>'$349/mo', 'cost'=>'$80', 'margin'=>'77%','active'=>true, 'color'=>'#06b6d4'],
        ['id'=>6,'name'=>'Onboarding Service','desc'=>'4-week guided onboarding program',       'sku'=>'SVC-ONB-001','cat'=>'Service',  'price'=>'$4,500',  'cost'=>'$1,800','margin'=>'60%','active'=>true,'color'=>'#0891b2'],
        ['id'=>7,'name'=>'Training Package',  'desc'=>'10-hour admin and user training',        'sku'=>'SVC-TRN-001','cat'=>'Service',  'price'=>'$1,500',  'cost'=>'$600','margin'=>'60%','active'=>true, 'color'=>'#16a34a'],
        ['id'=>8,'name'=>'Legacy Module',     'desc'=>'Deprecated — no new sales',              'sku'=>'NCR-LEG-001','cat'=>'Software', 'price'=>'$149/mo', 'cost'=>'$30', 'margin'=>'80%','active'=>false,'color'=>'#94a3b8'],
      ] as $product)
      <tr>
        <td class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></td>
        <td>
          <div class="ncv-table-name">
            <div class="ncv-table-avatar" style="background:{{ $product['color'] }}18;color:{{ $product['color'] }};border-radius:.5rem;">
              <i class="bi bi-box-seam" style="font-size:.875rem;"></i>
            </div>
            <div>
              <a href="{{ route('products.show', $product['id']) }}" class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;font-size:.875rem;">{{ $product['name'] }}</a>
              <div class="ncv-table-cell-sub">{{ $product['desc'] }}</div>
            </div>
          </div>
        </td>
        <td style="font-family:monospace;font-size:.8rem;color:var(--text-muted);background:#f8faff;border-radius:.375rem;padding:.25rem .5rem;">{{ $product['sku'] }}</td>
        <td><span class="ncv-badge ncv-badge-muted" style="font-size:.72rem;">{{ $product['cat'] }}</span></td>
        <td style="font-weight:700;color:var(--text-primary);font-size:.875rem;">{{ $product['price'] }}</td>
        <td style="font-size:.82rem;color:var(--text-muted);">{{ $product['cost'] }}</td>
        <td>
          <span style="font-size:.82rem;font-weight:700;color:{{ (int)$product['margin'] >= 75 ? '#059669' : '#d97706' }};">{{ $product['margin'] }}</span>
        </td>
        <td>
          @if($product['active'])
            <span class="ncv-badge ncv-badge-success"><span class="dot"></span>Active</span>
          @else
            <span class="ncv-badge ncv-badge-muted"><span class="dot"></span>Inactive</span>
          @endif
        </td>
        <td>
          <div class="d-flex gap-1">
            <a href="{{ route('products.edit', $product['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
            <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Delete" style="color:#ef4444;"><i class="bi bi-trash" style="font-size:.8rem;"></i></button>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Showing <strong style="color:var(--text-primary);">1–8</strong> of <strong style="color:var(--text-primary);">8</strong> products</p>
  <nav class="ncv-pagination">
    <a href="#" class="ncv-page-btn active">1</a>
  </nav>
</div>

@endsection
