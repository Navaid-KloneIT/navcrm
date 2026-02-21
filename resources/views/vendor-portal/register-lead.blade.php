@extends('vendor-portal.layout')

@section('title', 'Register Lead')

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-7">
    <h1 class="h4 fw-bold mb-1">Register a Lead</h1>
    <p class="text-muted mb-4" style="font-size:.85rem;">
      Submit a potential customer lead for our sales team to follow up.
    </p>

    <div class="ncv-card">
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('vendor-portal.register-lead.store') }}">
          @csrf

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" value="{{ old('first_name') }}"
                     class="form-control @error('first_name') is-invalid @enderror" required>
              @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" value="{{ old('last_name') }}"
                     class="form-control @error('last_name') is-invalid @enderror" required>
              @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" value="{{ old('email') }}"
                     class="form-control @error('email') is-invalid @enderror" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-medium">Phone</label>
              <input type="text" name="phone" value="{{ old('phone') }}"
                     class="form-control @error('phone') is-invalid @enderror">
              @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <label class="form-label fw-medium">Company Name</label>
              <input type="text" name="company_name" value="{{ old('company_name') }}"
                     class="form-control @error('company_name') is-invalid @enderror">
              @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <label class="form-label fw-medium">Notes</label>
              <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
              @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i> Submit Lead
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
