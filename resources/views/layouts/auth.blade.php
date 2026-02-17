<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Sign In') — NavCRM</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

  <!-- Google Fonts — Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

  <!-- NavCRM Theme -->
  <link rel="stylesheet" href="{{ asset('css/navcrm-theme.css') }}" />

  <style>
    /* Auth-specific overrides */
    body {
      background: #f0f4fb;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .auth-wrapper {
      flex: 1;
      display: flex;
      min-height: 100vh;
    }

    /* Left panel — decorative */
    .auth-panel-left {
      flex: 1;
      background: linear-gradient(145deg, #0d1f4e 0%, #1a3a8f 40%, #2563eb 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 3rem 3.5rem;
      position: relative;
      overflow: hidden;
    }

    /* Decorative blobs */
    .auth-blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(70px);
      opacity: .18;
      pointer-events: none;
    }
    .auth-blob-1 {
      width: 340px; height: 340px;
      background: #60a5fa;
      top: -80px; right: -80px;
    }
    .auth-blob-2 {
      width: 260px; height: 260px;
      background: #c4b5fd;
      bottom: -60px; left: 40px;
    }
    .auth-blob-3 {
      width: 180px; height: 180px;
      background: #34d399;
      top: 50%; right: 10%;
    }

    /* Floating card mockup */
    .auth-mockup {
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.12);
      backdrop-filter: blur(12px);
      border-radius: 1rem;
      padding: 1.25rem 1.5rem;
      margin-top: 2rem;
      width: 100%;
      max-width: 380px;
    }
    .auth-mockup-row {
      display: flex;
      align-items: center;
      gap: .875rem;
      padding: .65rem 0;
      border-bottom: 1px solid rgba(255,255,255,.07);
    }
    .auth-mockup-row:last-child { border-bottom: none; }
    .auth-mockup-dot {
      width: 36px; height: 36px;
      border-radius: .5rem;
      flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: .75rem; font-weight: 700; color: #fff;
    }
    .auth-mockup-bar {
      flex: 1;
    }
    .auth-mockup-bar-top {
      height: 8px;
      background: rgba(255,255,255,.25);
      border-radius: 99px;
      margin-bottom: 5px;
    }
    .auth-mockup-bar-bot {
      height: 6px;
      background: rgba(255,255,255,.1);
      border-radius: 99px;
      width: 60%;
    }
    .auth-mockup-val {
      font-size: .8rem;
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
    }

    .auth-panel-tagline {
      font-size: 2rem;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -.04em;
      line-height: 1.2;
      margin: 0;
      position: relative;
      z-index: 1;
    }
    .auth-panel-tagline span {
      color: #93c5fd;
    }
    .auth-panel-sub {
      font-size: .95rem;
      color: rgba(255,255,255,.65);
      margin-top: .875rem;
      max-width: 340px;
      position: relative;
      z-index: 1;
      line-height: 1.6;
    }

    /* Feature list */
    .auth-features {
      list-style: none;
      padding: 0; margin: 1.5rem 0 0;
      position: relative; z-index: 1;
    }
    .auth-features li {
      display: flex;
      align-items: center;
      gap: .625rem;
      font-size: .85rem;
      color: rgba(255,255,255,.8);
      margin-bottom: .625rem;
    }
    .auth-features li .check {
      width: 20px; height: 20px;
      background: rgba(16,185,129,.25);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      color: #34d399;
      font-size: .7rem;
    }

    /* Right panel — form */
    .auth-panel-right {
      width: 480px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: #ffffff;
      padding: 2.5rem 2rem;
    }

    .auth-form-box {
      width: 100%;
      max-width: 380px;
    }

    .auth-logo {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin-bottom: 2rem;
    }
    .auth-logo-icon {
      width: 42px; height: 42px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      border-radius: .75rem;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 14px rgba(37,99,235,.4);
    }
    .auth-logo-icon svg { width: 24px; height: 24px; fill: #fff; }
    .auth-logo-name {
      font-size: 1.35rem;
      font-weight: 800;
      color: #0d1f4e;
      letter-spacing: -.03em;
    }
    .auth-logo-name span { color: #2563eb; }

    .auth-title {
      font-size: 1.5rem;
      font-weight: 800;
      color: #0d1f4e;
      letter-spacing: -.03em;
      margin-bottom: .375rem;
    }
    .auth-subtitle {
      font-size: .875rem;
      color: #64748b;
      margin-bottom: 1.75rem;
    }

    /* Form elements */
    .auth-input-wrap { position: relative; margin-bottom: 1rem; }
    .auth-input-icon {
      position: absolute;
      left: .875rem; top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 1rem;
      pointer-events: none;
      z-index: 2;
    }
    .auth-input {
      width: 100%;
      height: 48px;
      padding: 0 1rem 0 2.75rem;
      background: #f8faff;
      border: 1.5px solid #e2e8f0;
      border-radius: .75rem;
      font-size: .9rem;
      color: #0d1f4e;
      font-family: inherit;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .auth-input:focus {
      border-color: #60a5fa;
      box-shadow: 0 0 0 3px rgba(59,130,246,.12);
      background: #fff;
    }
    .auth-input::placeholder { color: #94a3b8; }
    .auth-input.is-invalid {
      border-color: #ef4444;
      box-shadow: 0 0 0 3px rgba(239,68,68,.1);
    }

    .auth-label {
      display: block;
      font-size: .8rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: .4rem;
    }

    .auth-toggle-pw {
      position: absolute;
      right: .875rem; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      color: #94a3b8; cursor: pointer;
      padding: 0;
      font-size: 1.05rem;
      transition: color .15s;
      z-index: 2;
    }
    .auth-toggle-pw:hover { color: #2563eb; }

    .auth-submit {
      width: 100%;
      height: 50px;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      color: #fff;
      border: none;
      border-radius: .875rem;
      font-size: .9375rem;
      font-weight: 700;
      cursor: pointer;
      transition: all .2s;
      box-shadow: 0 4px 16px rgba(37,99,235,.4);
      letter-spacing: -.01em;
      margin-top: .5rem;
    }
    .auth-submit:hover {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      box-shadow: 0 6px 22px rgba(37,99,235,.5);
      transform: translateY(-1px);
    }
    .auth-submit:active { transform: translateY(0); }

    .auth-divider {
      display: flex; align-items: center; gap: .75rem;
      font-size: .775rem; color: #94a3b8; margin: 1.25rem 0;
    }
    .auth-divider::before, .auth-divider::after {
      content: ''; flex: 1;
      height: 1px; background: #e2e8f0;
    }

    .auth-footer {
      text-align: center;
      font-size: .825rem;
      color: #64748b;
      margin-top: 1.5rem;
    }
    .auth-footer a {
      color: #2563eb;
      font-weight: 600;
      text-decoration: none;
    }
    .auth-footer a:hover { text-decoration: underline; }

    .auth-error {
      background: #fee2e2;
      color: #991b1b;
      border-radius: .625rem;
      padding: .75rem 1rem;
      font-size: .825rem;
      margin-bottom: 1rem;
      display: flex; align-items: flex-start; gap: .5rem;
      border: 1px solid #fecaca;
    }

    .invalid-feedback-ncv {
      font-size: .75rem;
      color: #ef4444;
      margin-top: .3rem;
      display: block;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
      .auth-panel-left { display: none; }
      .auth-panel-right { width: 100%; min-height: 100vh; padding: 2rem 1.5rem; }
    }
    @media (max-width: 400px) {
      .auth-panel-right { padding: 1.5rem 1.25rem; }
    }
  </style>

  @stack('styles')
</head>
<body>

<div class="auth-wrapper">
  {{-- Left decorative panel --}}
  <div class="auth-panel-left">
    <div class="auth-blob auth-blob-1"></div>
    <div class="auth-blob auth-blob-2"></div>
    <div class="auth-blob auth-blob-3"></div>

    <div style="position:relative; z-index:1; width:100%;">
      <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:3rem;">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:.625rem;display:flex;align-items:center;justify-content:center;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
          </svg>
        </div>
        <span style="font-size:1.2rem;font-weight:800;color:#fff;letter-spacing:-.02em;">Nav<span style="color:#93c5fd;">CRM</span></span>
      </div>

      <h1 class="auth-panel-tagline">
        Close more deals.<br/>
        <span>Grow faster.</span>
      </h1>
      <p class="auth-panel-sub">
        A modern CRM built for high-performance sales teams. Track leads, manage pipelines, and forecast revenue — all in one place.
      </p>

      <ul class="auth-features">
        <li>
          <span class="check"><i class="bi bi-check2"></i></span>
          Contact & Account management
        </li>
        <li>
          <span class="check"><i class="bi bi-check2"></i></span>
          Visual pipeline with drag-and-drop
        </li>
        <li>
          <span class="check"><i class="bi bi-check2"></i></span>
          Automated lead scoring &amp; conversion
        </li>
        <li>
          <span class="check"><i class="bi bi-check2"></i></span>
          Quote builder with PDF export
        </li>
        <li>
          <span class="check"><i class="bi bi-check2"></i></span>
          Revenue forecasting &amp; targets
        </li>
      </ul>

      <div class="auth-mockup mt-4">
        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.5);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.75rem;">Pipeline Overview</div>
        <div class="auth-mockup-row">
          <div class="auth-mockup-dot" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">Q</div>
          <div class="auth-mockup-bar">
            <div class="auth-mockup-bar-top"></div>
            <div class="auth-mockup-bar-bot"></div>
          </div>
          <div class="auth-mockup-val">$42k</div>
        </div>
        <div class="auth-mockup-row">
          <div class="auth-mockup-dot" style="background:linear-gradient(135deg,#10b981,#059669);">P</div>
          <div class="auth-mockup-bar">
            <div class="auth-mockup-bar-top" style="width:75%;"></div>
            <div class="auth-mockup-bar-bot" style="width:45%;"></div>
          </div>
          <div class="auth-mockup-val">$28k</div>
        </div>
        <div class="auth-mockup-row">
          <div class="auth-mockup-dot" style="background:linear-gradient(135deg,#f59e0b,#d97706);">N</div>
          <div class="auth-mockup-bar">
            <div class="auth-mockup-bar-top" style="width:55%;"></div>
            <div class="auth-mockup-bar-bot" style="width:30%;"></div>
          </div>
          <div class="auth-mockup-val">$18k</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Right form panel --}}
  <div class="auth-panel-right">
    <div class="auth-form-box">

      <div class="auth-logo">
        <div class="auth-logo-icon">
          <svg viewBox="0 0 24 24" fill="white">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
          </svg>
        </div>
        <div class="auth-logo-name">Nav<span>CRM</span></div>
      </div>

      @yield('form-content')

      <p class="text-center mt-4" style="font-size:.75rem;color:#94a3b8;">
        &copy; {{ date('Y') }} NavCRM. All rights reserved.
      </p>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmJ3H/jviwjBVfJSYBDiGhCDHMer"
        crossorigin="anonymous"></script>

<script>
  // Toggle password visibility
  function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye';
    }
  }
</script>

@stack('scripts')
</body>
</html>
