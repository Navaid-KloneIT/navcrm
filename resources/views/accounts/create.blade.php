@extends('layouts.app')

@section('title', isset($account) ? 'Edit Account' : 'New Account')
@section('page-title', isset($account) ? 'Edit Account' : 'New Account')

@section('breadcrumb-items')
  <a href="{{ route('accounts.index') }}" style="color:inherit;text-decoration:none;">Accounts</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($account) ? 'Edit Account' : 'New Account' }}</h1>
        <p class="ncv-page-subtitle">{{ isset($account) ? 'Update company information.' : 'Add a new company to your CRM.' }}</p>
      </div>
      <a href="{{ route('accounts.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($account) ? route('accounts.update', $account->id) : route('accounts.store') }}">
      @csrf
      @if(isset($account)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Company Information --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-building me-2" style="color:var(--ncv-blue-500);"></i>Company Information</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-8">
                  <label class="ncv-label" for="name">Company Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $account->name ?? '') }}"
                         placeholder="Acme Corporation" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="account_type">Account Type</label>
                  <select class="ncv-select" id="account_type" name="account_type">
                    @foreach(['Customer','Prospect','Partner','Vendor','Competitor','Other'] as $type)
                    <option value="{{ $type }}" {{ old('account_type', $account->account_type ?? 'Customer') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="industry">Industry</label>
                  <select class="ncv-select" id="industry" name="industry">
                    <option value="">— Select —</option>
                    @foreach(['Technology','Finance','Healthcare','Manufacturing','Retail','Education','Real Estate','Media','Transportation','Government','Other'] as $ind)
                    <option value="{{ $ind }}" {{ old('industry', $account->industry ?? '') == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="employees">Employees</label>
                  <select class="ncv-select" id="employees" name="employees">
                    <option value="">— Select —</option>
                    @foreach(['1–10','11–50','51–200','201–500','501–1000','1001–5000','5000+'] as $size)
                    <option value="{{ $size }}" {{ old('employees', $account->employees ?? '') == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="annual_revenue">Annual Revenue</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-currency-dollar ncv-input-icon"></i>
                    <input type="text" class="ncv-input" id="annual_revenue" name="annual_revenue"
                           value="{{ old('annual_revenue', $account->annual_revenue ?? '') }}"
                           placeholder="e.g. 45000000" />
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="website">Website</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-globe2 ncv-input-icon"></i>
                    <input type="url" class="ncv-input" id="website" name="website"
                           value="{{ old('website', $account->website ?? '') }}"
                           placeholder="https://company.com" />
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="phone">Main Phone</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-telephone ncv-input-icon"></i>
                    <input type="tel" class="ncv-input" id="phone" name="phone"
                           value="{{ old('phone', $account->phone ?? '') }}"
                           placeholder="+1 (555) 000-0000" />
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="tax_id">Tax ID / VAT</label>
                  <input type="text" class="ncv-input" id="tax_id" name="tax_id"
                         value="{{ old('tax_id', $account->tax_id ?? '') }}"
                         placeholder="12-3456789" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="parent_id">Parent Company</label>
                  <select class="ncv-select" id="parent_id" name="parent_id">
                    <option value="">— No Parent —</option>
                    <option value="1">Acme Holdings Ltd</option>
                    <option value="2">Globex Group</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Billing Address --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-receipt me-2" style="color:var(--ncv-blue-500);"></i>Billing Address</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-2">
                <div class="col-12">
                  <label class="ncv-label" for="billing_street">Street</label>
                  <input type="text" class="ncv-input" id="billing_street" name="billing_street"
                         value="{{ old('billing_street', $account->billing_street ?? '') }}"
                         placeholder="123 Main Street" />
                </div>
                <div class="col-8">
                  <label class="ncv-label" for="billing_city">City</label>
                  <input type="text" class="ncv-input" id="billing_city" name="billing_city"
                         value="{{ old('billing_city', $account->billing_city ?? '') }}" />
                </div>
                <div class="col-4">
                  <label class="ncv-label" for="billing_state">State</label>
                  <input type="text" class="ncv-input" id="billing_state" name="billing_state"
                         value="{{ old('billing_state', $account->billing_state ?? '') }}" />
                </div>
                <div class="col-6">
                  <label class="ncv-label" for="billing_zip">ZIP</label>
                  <input type="text" class="ncv-input" id="billing_zip" name="billing_zip"
                         value="{{ old('billing_zip', $account->billing_zip ?? '') }}" />
                </div>
                <div class="col-6">
                  <label class="ncv-label" for="billing_country">Country</label>
                  <select class="ncv-select" id="billing_country" name="billing_country">
                    <option value="US" selected>United States</option>
                    <option value="CA">Canada</option>
                    <option value="GB">United Kingdom</option>
                    <option value="AU">Australia</option>
                    <option value="DE">Germany</option>
                    <option value="IN">India</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Shipping Address --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-truck me-2" style="color:var(--ncv-blue-500);"></i>Shipping Address</h6>
              <div class="form-check" style="margin:0;">
                <input type="checkbox" class="form-check-input" id="sameAsBilling" onchange="copyBillingAddress(this)" />
                <label class="form-check-label" for="sameAsBilling" style="font-size:.8rem;color:var(--text-muted);">Same as billing</label>
              </div>
            </div>
            <div class="ncv-card-body" id="shippingFields">
              <div class="row g-2">
                <div class="col-12">
                  <label class="ncv-label" for="shipping_street">Street</label>
                  <input type="text" class="ncv-input" id="shipping_street" name="shipping_street"
                         value="{{ old('shipping_street', $account->shipping_street ?? '') }}"
                         placeholder="123 Main Street" />
                </div>
                <div class="col-8">
                  <label class="ncv-label" for="shipping_city">City</label>
                  <input type="text" class="ncv-input" id="shipping_city" name="shipping_city"
                         value="{{ old('shipping_city', $account->shipping_city ?? '') }}" />
                </div>
                <div class="col-4">
                  <label class="ncv-label" for="shipping_state">State</label>
                  <input type="text" class="ncv-input" id="shipping_state" name="shipping_state"
                         value="{{ old('shipping_state', $account->shipping_state ?? '') }}" />
                </div>
                <div class="col-6">
                  <label class="ncv-label" for="shipping_zip">ZIP</label>
                  <input type="text" class="ncv-input" id="shipping_zip" name="shipping_zip"
                         value="{{ old('shipping_zip', $account->shipping_zip ?? '') }}" />
                </div>
                <div class="col-6">
                  <label class="ncv-label" for="shipping_country">Country</label>
                  <select class="ncv-select" id="shipping_country" name="shipping_country">
                    <option value="US" selected>United States</option>
                    <option value="CA">Canada</option>
                    <option value="GB">United Kingdom</option>
                    <option value="AU">Australia</option>
                    <option value="DE">Germany</option>
                    <option value="IN">India</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('accounts.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($account) ? 'Update Account' : 'Create Account' }}
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
  function copyBillingAddress(cb) {
    const fields = ['street','city','state','zip','country'];
    if (cb.checked) {
      fields.forEach(f => {
        const src = document.getElementById('billing_' + f);
        const tgt = document.getElementById('shipping_' + f);
        if (src && tgt) tgt.value = src.value;
      });
      document.getElementById('shippingFields').style.opacity = '.5';
      document.getElementById('shippingFields').style.pointerEvents = 'none';
    } else {
      document.getElementById('shippingFields').style.opacity = '';
      document.getElementById('shippingFields').style.pointerEvents = '';
    }
  }
</script>
@endpush
