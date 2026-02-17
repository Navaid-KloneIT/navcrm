@extends('layouts.auth')

@section('title', 'Sign In')

@section('form-content')

<h2 class="auth-title">Welcome back</h2>
<p class="auth-subtitle">Sign in to your NavCRM account to continue.</p>

@if($errors->any())
  <div class="auth-error">
    <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0; margin-top:1px;"></i>
    <div>
      @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  </div>
@endif

@if(session('status'))
  <div style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;border-radius:.625rem;padding:.75rem 1rem;font-size:.825rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
    <i class="bi bi-check-circle-fill"></i>
    {{ session('status') }}
  </div>
@endif

<form method="POST" action="{{ route('auth.login') }}" novalidate>
  @csrf

  {{-- Email --}}
  <div class="mb-3">
    <label class="auth-label" for="email">Email Address</label>
    <div class="auth-input-wrap">
      <i class="bi bi-envelope auth-input-icon"></i>
      <input
        id="email"
        type="email"
        name="email"
        class="auth-input @error('email') is-invalid @enderror"
        value="{{ old('email') }}"
        placeholder="you@company.com"
        autocomplete="email"
        autofocus
        required
      />
    </div>
    @error('email')
      <span class="invalid-feedback-ncv">{{ $message }}</span>
    @enderror
  </div>

  {{-- Password --}}
  <div class="mb-3">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.4rem;">
      <label class="auth-label mb-0" for="password">Password</label>
      <a href="{{ route('auth.forgot-password') }}"
         style="font-size:.775rem; color:#2563eb; text-decoration:none; font-weight:600;">
        Forgot password?
      </a>
    </div>
    <div class="auth-input-wrap" style="position:relative;">
      <i class="bi bi-lock auth-input-icon"></i>
      <input
        id="password"
        type="password"
        name="password"
        class="auth-input @error('password') is-invalid @enderror"
        placeholder="Enter your password"
        autocomplete="current-password"
        required
        style="padding-right: 3rem;"
      />
      <button type="button" class="auth-toggle-pw" onclick="togglePassword('password', this)">
        <i class="bi bi-eye"></i>
      </button>
    </div>
    @error('password')
      <span class="invalid-feedback-ncv">{{ $message }}</span>
    @enderror
  </div>

  {{-- Remember me --}}
  <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem;">
    <input
      id="remember"
      type="checkbox"
      name="remember"
      style="width:16px;height:16px;accent-color:#2563eb;cursor:pointer;"
    />
    <label for="remember" style="font-size:.825rem; color:#475569; cursor:pointer; margin:0;">
      Keep me signed in for 30 days
    </label>
  </div>

  {{-- Submit --}}
  <button type="submit" class="auth-submit">
    <span id="loginBtnText">Sign In</span>
    <span id="loginBtnLoader" style="display:none;">
      <span class="spinner-border spinner-border-sm me-1" role="status"></span>
      Signing in…
    </span>
  </button>

</form>

<div class="auth-footer">
  Don't have an account?
  <a href="{{ route('auth.register') }}">Create one free</a>
</div>

@push('scripts')
<script>
  document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('loginBtnText').style.display   = 'none';
    document.getElementById('loginBtnLoader').style.display = 'inline-flex';
    this.querySelector('.auth-submit').disabled = true;
  });
</script>
@endpush

@endsection
