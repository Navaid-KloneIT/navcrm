@extends('layouts.auth')

@section('title', 'Create Account')

@section('form-content')

<h2 class="auth-title">Create your account</h2>
<p class="auth-subtitle">Start your free NavCRM trial — no credit card required.</p>

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

<form method="POST" action="{{ route('auth.register') }}" novalidate>
  @csrf

  {{-- Full name --}}
  <div class="mb-3">
    <label class="auth-label" for="name">Full Name</label>
    <div class="auth-input-wrap">
      <i class="bi bi-person auth-input-icon"></i>
      <input
        id="name"
        type="text"
        name="name"
        class="auth-input @error('name') is-invalid @enderror"
        value="{{ old('name') }}"
        placeholder="Jane Smith"
        autocomplete="name"
        autofocus
        required
      />
    </div>
    @error('name')
      <span class="invalid-feedback-ncv">{{ $message }}</span>
    @enderror
  </div>

  {{-- Email --}}
  <div class="mb-3">
    <label class="auth-label" for="email">Work Email</label>
    <div class="auth-input-wrap">
      <i class="bi bi-envelope auth-input-icon"></i>
      <input
        id="email"
        type="email"
        name="email"
        class="auth-input @error('email') is-invalid @enderror"
        value="{{ old('email') }}"
        placeholder="jane@company.com"
        autocomplete="email"
        required
      />
    </div>
    @error('email')
      <span class="invalid-feedback-ncv">{{ $message }}</span>
    @enderror
  </div>

  {{-- Password --}}
  <div class="mb-3">
    <label class="auth-label" for="password">Password</label>
    <div class="auth-input-wrap" style="position:relative;">
      <i class="bi bi-lock auth-input-icon"></i>
      <input
        id="password"
        type="password"
        name="password"
        class="auth-input @error('password') is-invalid @enderror"
        placeholder="At least 8 characters"
        autocomplete="new-password"
        required
        style="padding-right: 3rem;"
        oninput="checkPasswordStrength(this.value)"
      />
      <button type="button" class="auth-toggle-pw" onclick="togglePassword('password', this)">
        <i class="bi bi-eye"></i>
      </button>
    </div>
    <!-- Password strength indicator -->
    <div id="pwStrengthBar" style="height:3px;border-radius:9999px;background:#e2e8f0;margin-top:.4rem;overflow:hidden;">
      <div id="pwStrengthFill" style="height:100%;width:0;border-radius:9999px;transition:width .3s,background .3s;"></div>
    </div>
    <div id="pwStrengthText" style="font-size:.7rem;color:#94a3b8;margin-top:.25rem;"></div>
    @error('password')
      <span class="invalid-feedback-ncv">{{ $message }}</span>
    @enderror
  </div>

  {{-- Confirm Password --}}
  <div class="mb-3">
    <label class="auth-label" for="password_confirmation">Confirm Password</label>
    <div class="auth-input-wrap" style="position:relative;">
      <i class="bi bi-shield-lock auth-input-icon"></i>
      <input
        id="password_confirmation"
        type="password"
        name="password_confirmation"
        class="auth-input"
        placeholder="Re-enter password"
        autocomplete="new-password"
        required
        style="padding-right: 3rem;"
      />
      <button type="button" class="auth-toggle-pw" onclick="togglePassword('password_confirmation', this)">
        <i class="bi bi-eye"></i>
      </button>
    </div>
  </div>

  {{-- Terms --}}
  <div style="display:flex; align-items:flex-start; gap:.625rem; margin-bottom:1.5rem;">
    <input
      id="terms"
      type="checkbox"
      name="terms"
      style="width:16px;height:16px;accent-color:#2563eb;cursor:pointer;margin-top:2px;flex-shrink:0;"
      required
    />
    <label for="terms" style="font-size:.8rem; color:#475569; cursor:pointer; margin:0; line-height:1.5;">
      I agree to the
      <a href="#" style="color:#2563eb;font-weight:600;text-decoration:none;">Terms of Service</a>
      and
      <a href="#" style="color:#2563eb;font-weight:600;text-decoration:none;">Privacy Policy</a>
    </label>
  </div>

  {{-- Submit --}}
  <button type="submit" class="auth-submit" id="registerBtn">
    <span id="registerBtnText">Create Account</span>
    <span id="registerBtnLoader" style="display:none;">
      <span class="spinner-border spinner-border-sm me-1" role="status"></span>
      Creating account…
    </span>
  </button>

</form>

<div class="auth-footer">
  Already have an account?
  <a href="{{ route('auth.login') }}">Sign in instead</a>
</div>

@push('scripts')
<script>
  // Password strength checker
  function checkPasswordStrength(val) {
    const fill = document.getElementById('pwStrengthFill');
    const text = document.getElementById('pwStrengthText');
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
      { w:'0%',   c:'#ef4444', t:'' },
      { w:'25%',  c:'#ef4444', t:'Weak' },
      { w:'50%',  c:'#f59e0b', t:'Fair' },
      { w:'75%',  c:'#3b82f6', t:'Good' },
      { w:'100%', c:'#10b981', t:'Strong' },
    ];
    const l = levels[score];
    fill.style.width      = l.w;
    fill.style.background = l.c;
    text.textContent      = l.t;
    text.style.color      = l.c;
  }

  document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('registerBtnText').style.display   = 'none';
    document.getElementById('registerBtnLoader').style.display = 'inline-flex';
    document.getElementById('registerBtn').disabled = true;
  });
</script>
@endpush

@endsection
