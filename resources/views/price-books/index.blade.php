@extends('layouts.app')

@section('title', 'Price Books')
@section('page-title', 'Price Books')

@section('breadcrumb-items')
  <a href="{{ route('products.index') }}" style="color:inherit;text-decoration:none;">Products</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Price Books</h1>
    <p class="ncv-page-subtitle">Manage custom pricing lists for different customer tiers or regions.</p>
  </div>
  <button class="ncv-btn ncv-btn-primary ncv-btn-sm" onclick="openCreateModal()">
    <i class="bi bi-plus-lg"></i> New Price Book
  </button>
</div>

{{-- Price Book Cards --}}
<div class="row g-3 mb-4">
  @foreach([
    ['id'=>1,'name'=>'Standard',        'desc'=>'Default pricing for all customers',          'products'=>8,  'currency'=>'USD','active'=>true, 'color'=>'#2563eb','default'=>true],
    ['id'=>2,'name'=>'Enterprise',      'desc'=>'Discounted pricing for enterprise clients',  'products'=>6,  'currency'=>'USD','active'=>true, 'color'=>'#8b5cf6','default'=>false],
    ['id'=>3,'name'=>'Partner',         'desc'=>'Reseller and partner discount tiers',        'products'=>5,  'currency'=>'USD','active'=>true, 'color'=>'#10b981','default'=>false],
    ['id'=>4,'name'=>'EMEA Region',     'desc'=>'Euro-denominated pricing for EU clients',    'products'=>7,  'currency'=>'EUR','active'=>true, 'color'=>'#f59e0b','default'=>false],
    ['id'=>5,'name'=>'APAC Region',     'desc'=>'Asia-Pacific localized pricing',             'products'=>6,  'currency'=>'USD','active'=>true, 'color'=>'#06b6d4','default'=>false],
    ['id'=>6,'name'=>'Legacy Pricing',  'desc'=>'Grandfathered rates for legacy accounts',   'products'=>3,  'currency'=>'USD','active'=>false,'color'=>'#94a3b8','default'=>false],
  ] as $book)
  <div class="col-12 col-sm-6 col-xl-4">
    <div class="ncv-card h-100" style="border-top:3px solid {{ $book['color'] }};">
      <div class="ncv-card-body" style="padding:1.25rem;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.875rem;">
          <div>
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;">
              <h6 style="font-size:1rem;font-weight:800;color:var(--text-primary);margin:0;">{{ $book['name'] }}</h6>
              @if($book['default'])
              <span class="ncv-badge ncv-badge-primary" style="font-size:.65rem;">Default</span>
              @endif
              @if(!$book['active'])
              <span class="ncv-badge ncv-badge-muted" style="font-size:.65rem;">Inactive</span>
              @endif
            </div>
            <p style="font-size:.8rem;color:var(--text-muted);margin:0;">{{ $book['desc'] }}</p>
          </div>
          <div class="ncv-dropdown">
            <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="toggleDropdown('pbMenu{{ $book['id'] }}')"><i class="bi bi-three-dots"></i></button>
            <div class="ncv-dropdown-menu" id="pbMenu{{ $book['id'] }}">
              <button class="ncv-dropdown-item" onclick="openBookDetail({{ $book['id'] }})"><i class="bi bi-eye"></i> View Entries</button>
              <button class="ncv-dropdown-item"><i class="bi bi-pencil"></i> Edit</button>
              @if(!$book['default'])
              <button class="ncv-dropdown-item"><i class="bi bi-pin-fill"></i> Set as Default</button>
              @endif
              <div class="ncv-dropdown-divider"></div>
              <button class="ncv-dropdown-item danger"><i class="bi bi-trash"></i> Delete</button>
            </div>
          </div>
        </div>

        <div style="display:flex;gap:1rem;margin-bottom:1rem;">
          <div style="text-align:center;">
            <div style="font-size:1.4rem;font-weight:800;color:{{ $book['color'] }};">{{ $book['products'] }}</div>
            <div style="font-size:.68rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Products</div>
          </div>
          <div style="text-align:center;">
            <div style="font-size:1.4rem;font-weight:800;color:var(--text-primary);">{{ $book['currency'] }}</div>
            <div style="font-size:.68rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Currency</div>
          </div>
        </div>

        <button class="ncv-btn ncv-btn-outline ncv-btn-sm w-100" onclick="openBookDetail({{ $book['id'] }})"
                style="justify-content:center;">
          <i class="bi bi-list-ul"></i> View Price Entries
        </button>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Price Book Detail Panel --}}
<div id="priceBookDetail" style="display:none;">
  <div class="ncv-card">
    <div class="ncv-card-header">
      <h6 class="ncv-card-title" id="bookDetailTitle"><i class="bi bi-book me-2" style="color:var(--ncv-blue-500);"></i>Standard — Price Entries</h6>
      <div class="d-flex gap-2">
        <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="openAddEntry()">
          <i class="bi bi-plus-lg"></i> Add Product
        </button>
        <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="closeBookDetail()">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </div>
    <div class="ncv-card-body p-0">
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>List Price</th>
            <th>Book Price</th>
            <th>Discount</th>
            <th>Currency</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach([
            ['name'=>'NavCRM Enterprise','sku'=>'NCR-ENT-001','list'=>'$2,500/mo','book'=>'$2,000/mo','disc'=>'20%','cur'=>'USD'],
            ['name'=>'NavCRM Pro',        'sku'=>'NCR-PRO-001','list'=>'$499/mo', 'book'=>'$399/mo', 'disc'=>'20%','cur'=>'USD'],
            ['name'=>'NavCRM Starter',    'sku'=>'NCR-STR-001','list'=>'$99/mo',  'book'=>'$99/mo',  'disc'=>'0%', 'cur'=>'USD'],
            ['name'=>'API Access Module', 'sku'=>'NCR-API-001','list'=>'$199/mo', 'book'=>'$149/mo', 'disc'=>'25%','cur'=>'USD'],
            ['name'=>'AI Forecasting',    'sku'=>'NCR-AI-001', 'list'=>'$349/mo', 'book'=>'$279/mo', 'disc'=>'20%','cur'=>'USD'],
            ['name'=>'Onboarding Service','sku'=>'SVC-ONB-001','list'=>'$4,500',  'book'=>'$3,600',  'disc'=>'20%','cur'=>'USD'],
          ] as $entry)
          <tr>
            <td style="font-weight:600;font-size:.875rem;">{{ $entry['name'] }}</td>
            <td style="font-family:monospace;font-size:.78rem;color:var(--text-muted);">{{ $entry['sku'] }}</td>
            <td style="color:var(--text-muted);text-decoration:line-through;font-size:.82rem;">{{ $entry['list'] }}</td>
            <td style="font-weight:800;color:var(--text-primary);">{{ $entry['book'] }}</td>
            <td>
              <span class="ncv-badge ncv-badge-success" style="font-size:.7rem;">{{ $entry['disc'] }}</span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $entry['cur'] }}</td>
            <td>
              <div class="d-flex gap-1">
                <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"><i class="bi bi-pencil" style="font-size:.8rem;"></i></button>
                <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash" style="font-size:.8rem;"></i></button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Create Price Book Modal --}}
<div class="ncv-modal-overlay" id="createBookModal" style="display:none;">
  <div class="ncv-modal">
    <div class="ncv-modal-header">
      <h5 class="ncv-modal-title"><i class="bi bi-book me-2" style="color:var(--ncv-blue-500);"></i>New Price Book</h5>
      <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="closeCreateModal()"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="{{ route('price-books.store') }}">
      @csrf
      <div class="ncv-modal-body">
        <div class="ncv-form-group">
          <label class="ncv-label" for="book_name">Price Book Name <span class="required">*</span></label>
          <input type="text" class="ncv-input" id="book_name" name="name" placeholder="e.g. Enterprise 2026" required />
        </div>
        <div class="ncv-form-group">
          <label class="ncv-label" for="book_desc">Description</label>
          <textarea class="ncv-textarea" id="book_desc" name="description" rows="2" placeholder="Who is this price book for?"></textarea>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="ncv-label" for="book_currency">Currency</label>
            <select class="ncv-select" id="book_currency" name="currency">
              <option value="USD">USD — US Dollar</option>
              <option value="EUR">EUR — Euro</option>
              <option value="GBP">GBP — British Pound</option>
              <option value="AUD">AUD — Australian Dollar</option>
              <option value="CAD">CAD — Canadian Dollar</option>
            </select>
          </div>
          <div class="col-6">
            <label class="ncv-label">Set as Default</label>
            <div style="height:42px;display:flex;align-items:center;gap:.625rem;">
              <input type="checkbox" name="is_default" id="book_default" style="accent-color:#2563eb;width:16px;height:16px;" />
              <label for="book_default" style="font-size:.875rem;color:var(--text-secondary);cursor:pointer;margin:0;">Make this the default price book</label>
            </div>
          </div>
        </div>
      </div>
      <div class="ncv-modal-footer">
        <button type="button" class="ncv-btn ncv-btn-outline" onclick="closeCreateModal()">Cancel</button>
        <button type="submit" class="ncv-btn ncv-btn-primary"><i class="bi bi-check-lg"></i> Create Price Book</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function openCreateModal()  { document.getElementById('createBookModal').style.display = 'flex'; }
  function closeCreateModal() { document.getElementById('createBookModal').style.display = 'none'; }

  function openBookDetail(id) {
    document.getElementById('priceBookDetail').style.display = 'block';
    document.getElementById('priceBookDetail').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  function closeBookDetail() { document.getElementById('priceBookDetail').style.display = 'none'; }

  document.getElementById('createBookModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
  });
</script>
@endpush
