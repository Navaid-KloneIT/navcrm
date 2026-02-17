@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumb-items')
  <a href="{{ route('settings.index') }}" style="color:inherit;text-decoration:none;">Settings</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header">
  <h1 class="ncv-page-title">My Profile</h1>
  <p class="ncv-page-subtitle">Manage your personal information and security settings.</p>
</div>

<div class="row g-4">

  {{-- Profile Details --}}
  <div class="col-12 col-lg-7">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-person me-2" style="color:var(--ncv-blue-500);"></i>Personal Information</h6>
      </div>
      <div class="ncv-card-body">

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show border-0 mb-3"
               style="background:#d1fae5;color:#065f46;border-radius:.75rem;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <form method="POST" action="{{ route('settings.profile.update') }}">
          @csrf @method('PUT')

          <div class="row g-3">
            <div class="col-12">
              <label class="ncv-label" for="name">Full Name</label>
              <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                     id="name" name="name" value="{{ old('name', $user->name) }}" required />
              @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="col-12">
              <label class="ncv-label" for="email">Email Address</label>
              <input type="email" class="ncv-input @error('email') is-invalid @enderror"
                     id="email" name="email" value="{{ old('email', $user->email) }}" required />
              @error('email')<span class="ncv-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="col-12">
              <label class="ncv-label" for="phone">Phone</label>
              <input type="tel" class="ncv-input" id="phone" name="phone"
                     value="{{ old('phone', $user->phone ?? '') }}" placeholder="+1 (555) 000-0000" />
            </div>
            <div class="col-12">
              <div class="d-flex justify-content-end">
                <button type="submit" class="ncv-btn ncv-btn-primary">
                  <i class="bi bi-check-lg"></i> Save Changes
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Change Password --}}
  <div class="col-12 col-lg-5">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-lock me-2" style="color:var(--ncv-blue-500);"></i>Change Password</h6>
      </div>
      <div class="ncv-card-body">
        <form method="POST" action="{{ route('settings.password.update') }}">
          @csrf @method('PUT')

          <div class="row g-3">
            <div class="col-12">
              <label class="ncv-label" for="current_password">Current Password</label>
              <input type="password" class="ncv-input @error('current_password') is-invalid @enderror"
                     id="current_password" name="current_password" required />
              @error('current_password')<span class="ncv-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="col-12">
              <label class="ncv-label" for="password">New Password</label>
              <input type="password" class="ncv-input @error('password') is-invalid @enderror"
                     id="password" name="password" required />
              @error('password')<span class="ncv-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="col-12">
              <label class="ncv-label" for="password_confirmation">Confirm New Password</label>
              <input type="password" class="ncv-input" id="password_confirmation" name="password_confirmation" required />
            </div>
            <div class="col-12">
              <div class="d-flex justify-content-end">
                <button type="submit" class="ncv-btn ncv-btn-primary">
                  <i class="bi bi-shield-lock"></i> Update Password
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Account Info --}}
    <div class="ncv-card mt-3">
      <div class="ncv-card-body">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.75rem;">Account Info</div>
        <div class="d-flex gap-3 align-items-center mb-2">
          <div style="width:48px;height:48px;border-radius:50%;background:var(--ncv-blue-600);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.1rem;">
            {{ strtoupper(substr($user->name, 0, 2)) }}
          </div>
          <div>
            <div style="font-weight:600;">{{ $user->name }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);">{{ $user->getRoleNames()->first() ?? 'Member' }}</div>
          </div>
        </div>
        <div style="font-size:.8rem;color:var(--text-muted);">
          Member since {{ $user->created_at->format('M j, Y') }}
        </div>
      </div>
    </div>
  </div>

</div>

@endsection
