@extends('portal.layout')

@section('title', 'Customer Login')

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-5">

    <div class="text-center mb-4">
      <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
           style="width:56px;height:56px;background:rgba(59,130,246,.12);">
        <i class="bi bi-headset" style="font-size:1.5rem;color:#3b82f6;"></i>
      </div>
      <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Customer Support Portal</h1>
      <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
        Log in to submit and track your support tickets.
      </p>
    </div>

    <div class="ncv-card">
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('portal.login') }}">
          @csrf

          <div class="mb-3">
            <label for="email" class="form-label fw-medium">Email Address</label>
            <input type="email" id="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="password" class="form-label fw-medium">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Your portal password" required>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
          </button>
        </form>
      </div>
    </div>

    <p class="text-center mt-3" style="color:var(--text-muted);font-size:.8rem;">
      Need portal access? Contact our support team.
    </p>

  </div>
</div>
@endsection
