<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Vendor Portal') — NavCRM</title>

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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- NavCRM Theme -->
  <link rel="stylesheet" href="{{ asset('css/navcrm-theme.css') }}" />

  <style>
    .portal-header {
      background: var(--sidebar-bg, #0f172a);
      color: #fff;
      padding: 1rem 1.5rem;
    }
    .portal-brand { font-size: 1.1rem; font-weight: 700; color: #fff; text-decoration: none; }
    .portal-brand span { color: #60a5fa; }
    .portal-nav-link { color: rgba(255,255,255,.75); text-decoration: none; font-size: .875rem; }
    .portal-nav-link:hover { color: #fff; }
    .portal-footer { background: var(--bg-subtle); border-top: 1px solid var(--border-color); }
  </style>

  @stack('styles')
</head>
<body style="background:var(--bg-main,#f8fafc); min-height:100vh;">

  {{-- Portal Header --}}
  <header class="portal-header d-flex align-items-center justify-content-between">
    <a href="{{ route('vendor-portal.dashboard') }}" class="portal-brand">
      Nav<span>CRM</span>
      <span style="color:rgba(255,255,255,.5);font-weight:400;margin-left:.5rem;">Vendor Portal</span>
    </a>
    <div class="d-flex align-items-center gap-3">
      @if(session('vendor_portal_id'))
        <a href="{{ route('vendor-portal.dashboard') }}" class="portal-nav-link">
          <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        <a href="{{ route('vendor-portal.purchase-orders') }}" class="portal-nav-link">
          <i class="bi bi-cart me-1"></i> Purchase Orders
        </a>
        <a href="{{ route('vendor-portal.stock-check') }}" class="portal-nav-link">
          <i class="bi bi-box-seam me-1"></i> Stock Check
        </a>
        <a href="{{ route('vendor-portal.register-lead') }}" class="portal-nav-link">
          <i class="bi bi-person-plus me-1"></i> Register Lead
        </a>
        <form method="POST" action="{{ route('vendor-portal.logout') }}" class="mb-0">
          @csrf
          <button type="submit" class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
          </button>
        </form>
      @endif
    </div>
  </header>

  {{-- Main Content --}}
  <main class="container py-5" style="max-width:1000px;">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-4 border-0 rounded-3"
           style="background:#d1fae5;color:#065f46;" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 rounded-3"
           style="background:#fee2e2;color:#991b1b;" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @yield('content')
  </main>

  {{-- Footer --}}
  <footer class="portal-footer text-center py-3" style="color:var(--text-muted);font-size:.8rem;">
    Powered by NavCRM &mdash; Vendor Portal
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmJ3H/jviwjBVfJSYBDiGhCDHMer"
          crossorigin="anonymous"></script>

  @stack('scripts')
</body>
</html>
