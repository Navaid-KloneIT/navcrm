@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header">
  <h1 class="ncv-page-title">Settings</h1>
  <p class="ncv-page-subtitle">Manage your account, team, and system preferences.</p>
</div>

<div class="row g-3">

  {{-- Profile --}}
  <div class="col-12 col-md-6 col-lg-4">
    <a href="{{ route('settings.profile') }}" class="text-decoration-none">
      <div class="ncv-card h-100" style="cursor:pointer;">
        <div class="ncv-card-body d-flex gap-3 align-items-start">
          <div style="width:44px;height:44px;border-radius:.75rem;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-person-circle" style="font-size:1.4rem;color:#2563eb;"></i>
          </div>
          <div>
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:.25rem;">My Profile</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Update your name, email and password</div>
          </div>
        </div>
      </div>
    </a>
  </div>

  @if(auth()->user()->hasRole('admin'))
  {{-- Users --}}
  <div class="col-12 col-md-6 col-lg-4">
    <a href="{{ route('settings.users.index') }}" class="text-decoration-none">
      <div class="ncv-card h-100" style="cursor:pointer;">
        <div class="ncv-card-body d-flex gap-3 align-items-start">
          <div style="width:44px;height:44px;border-radius:.75rem;background:#d1fae5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-people" style="font-size:1.4rem;color:#10b981;"></i>
          </div>
          <div>
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:.25rem;">User Management</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Invite and manage team members</div>
          </div>
        </div>
      </div>
    </a>
  </div>

  {{-- Roles --}}
  <div class="col-12 col-md-6 col-lg-4">
    <a href="{{ route('settings.roles.index') }}" class="text-decoration-none">
      <div class="ncv-card h-100" style="cursor:pointer;">
        <div class="ncv-card-body d-flex gap-3 align-items-start">
          <div style="width:44px;height:44px;border-radius:.75rem;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-shield-lock" style="font-size:1.4rem;color:#f59e0b;"></i>
          </div>
          <div>
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:.25rem;">Roles & Permissions</div>
            <div style="font-size:.8rem;color:var(--text-muted);">Configure access control and roles</div>
          </div>
        </div>
      </div>
    </a>
  </div>
  @endif

</div>

@endsection
