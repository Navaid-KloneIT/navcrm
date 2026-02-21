@extends('layouts.app')

@section('title', isset($vendor) ? 'Edit Vendor' : 'New Vendor')
@section('page-title', isset($vendor) ? 'Edit Vendor' : 'New Vendor')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('inventory.vendors.index') }}" class="ncv-breadcrumb-item">Vendors</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<form method="POST"
      action="{{ isset($vendor) ? route('inventory.vendors.update', $vendor) : route('inventory.vendors.store') }}">
  @csrf
  @if(isset($vendor)) @method('PUT') @endif

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="ncv-card mb-3">
        <div class="ncv-card-header"><span class="ncv-card-title">Vendor Details</span></div>
        <div class="ncv-card-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Company Name <span class="text-danger">*</span></label>
              <input type="text" name="company_name"
                     value="{{ old('company_name', $vendor?->company_name) }}"
                     class="ncv-input @error('company_name') is-invalid @enderror" required>
              @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Contact Name</label>
              <input type="text" name="contact_name"
                     value="{{ old('contact_name', $vendor?->contact_name) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Email</label>
              <input type="email" name="email"
                     value="{{ old('email', $vendor?->email) }}"
                     class="ncv-input @error('email') is-invalid @enderror">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Phone</label>
              <input type="text" name="phone"
                     value="{{ old('phone', $vendor?->phone) }}"
                     class="ncv-input">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Address</label>
              <input type="text" name="address"
                     value="{{ old('address', $vendor?->address) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">City</label>
              <input type="text" name="city"
                     value="{{ old('city', $vendor?->city) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">State</label>
              <input type="text" name="state"
                     value="{{ old('state', $vendor?->state) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Country</label>
              <input type="text" name="country"
                     value="{{ old('country', $vendor?->country) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Postal Code</label>
              <input type="text" name="postal_code"
                     value="{{ old('postal_code', $vendor?->postal_code) }}"
                     class="ncv-input">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Website</label>
              <input type="url" name="website"
                     value="{{ old('website', $vendor?->website) }}"
                     placeholder="https://…"
                     class="ncv-input">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Notes</label>
              <textarea name="notes" rows="3" class="ncv-input" style="height:auto;">{{ old('notes', $vendor?->notes) }}</textarea>
            </div>

          </div>
        </div>
      </div>

      {{-- Portal Access --}}
      <div class="ncv-card">
        <div class="ncv-card-header"><span class="ncv-card-title">Portal Access</span></div>
        <div class="ncv-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Portal Password</label>
              <input type="password" name="portal_password"
                     placeholder="{{ isset($vendor) ? 'Leave blank to keep current' : 'Set portal password' }}"
                     class="ncv-input">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input type="hidden" name="portal_active" value="0">
                <input type="checkbox" name="portal_active" value="1"
                       class="form-check-input"
                       {{ old('portal_active', $vendor?->portal_active) ? 'checked' : '' }}>
                <label class="form-check-label" style="font-size:.82rem;">Enable Vendor Portal Access</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="ncv-card">
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Status <span class="text-danger">*</span></label>
            <select name="status" class="ncv-select" required>
              @foreach(\App\Enums\VendorStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ old('status', $vendor?->status?->value ?? 'active') === $s->value ? 'selected' : '' }}>
                  {{ $s->label() }}
                </option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="ncv-btn ncv-btn-primary w-100 mb-2">
            <i class="bi bi-check-lg me-1"></i>
            {{ isset($vendor) ? 'Update Vendor' : 'Create Vendor' }}
          </button>
          <a href="{{ route('inventory.vendors.index') }}" class="ncv-btn ncv-btn-outline w-100">Cancel</a>

          @if(isset($vendor))
          <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
            <form method="POST" action="{{ route('inventory.vendors.destroy', $vendor) }}"
                  onsubmit="return confirm('Delete this vendor?')">
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
