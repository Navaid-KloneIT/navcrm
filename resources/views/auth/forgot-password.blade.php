@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('form-content')

<h2 class="auth-title">Forgot your password?</h2>
<p class="auth-subtitle">Enter your email and we'll send you a reset link.</p>

@if(session('status'))
  <div style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;border-radius:.625rem;padding:.75rem 1rem;font-size:.825rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
    <i class="bi bi-check-circle-fill"></i>
    {{ session('status') }}
  </div>
@endif

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

<form method="POST" action="{{ route('auth.forgot-password.send') }}" novalidate>
  @csrf

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

  <button type="submit" class="auth-submit">
    Send Reset Link
  </button>
</form>

<div class="auth-footer">
  Remember your password?
  <a href="{{ route('auth.login') }}">Back to Sign In</a>
</div>

@endsection
