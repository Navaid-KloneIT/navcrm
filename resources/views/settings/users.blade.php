@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('breadcrumb-items')
  <a href="{{ route('settings.index') }}" style="color:inherit;text-decoration:none;">Settings</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">User Management</h1>
    <p class="ncv-page-subtitle">Invite and manage team members for your organization.</p>
  </div>
  <button class="ncv-btn ncv-btn-primary ncv-btn-sm" onclick="document.getElementById('inviteModal').style.display='flex'">
    <i class="bi bi-person-plus"></i> Invite User
  </button>
</div>

<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($users->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
        <p>No users found.</p>
      </div>
    @else
      <table class="table table-hover mb-0" style="font-size:.875rem;">
        <thead style="background:var(--surface-secondary);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;">
          <tr>
            <th style="padding:.75rem 1.25rem;font-weight:700;">User</th>
            <th style="padding:.75rem 1rem;font-weight:700;">Role</th>
            <th style="padding:.75rem 1rem;font-weight:700;">Status</th>
            <th style="padding:.75rem 1rem;font-weight:700;">Last Login</th>
            <th style="padding:.75rem 1rem;font-weight:700;text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $u)
          <tr>
            <td style="padding:.875rem 1.25rem;">
              <div class="d-flex align-items-center gap-2">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--ncv-blue-600);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0;">
                  {{ strtoupper(substr($u->name, 0, 2)) }}
                </div>
                <div>
                  <div style="font-weight:600;">{{ $u->name }}</div>
                  <div style="font-size:.75rem;color:var(--text-muted);">{{ $u->email }}</div>
                </div>
              </div>
            </td>
            <td style="padding:.875rem 1rem;vertical-align:middle;">
              <span class="badge" style="background:#dbeafe;color:#2563eb;font-weight:600;font-size:.72rem;padding:.3rem .6rem;border-radius:.375rem;">
                {{ $u->getRoleNames()->first() ?? '—' }}
              </span>
            </td>
            <td style="padding:.875rem 1rem;vertical-align:middle;">
              @if($u->is_active)
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.75rem;color:#10b981;font-weight:600;">
                  <span style="width:7px;height:7px;border-radius:50%;background:#10b981;display:inline-block;"></span> Active
                </span>
              @else
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.75rem;color:#94a3b8;font-weight:600;">
                  <span style="width:7px;height:7px;border-radius:50%;background:#94a3b8;display:inline-block;"></span> Inactive
                </span>
              @endif
            </td>
            <td style="padding:.875rem 1rem;vertical-align:middle;color:var(--text-muted);font-size:.8rem;">
              {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}
            </td>
            <td style="padding:.875rem 1rem;vertical-align:middle;text-align:right;">
              @if($u->id !== auth()->id())
                <form method="POST" action="{{ route('settings.users.destroy', $u) }}" class="d-inline"
                      onsubmit="return confirm('Delete {{ $u->name }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              @else
                <span style="font-size:.75rem;color:var(--text-muted);">You</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @if($users->hasPages())
        <div class="px-4 py-3">{{ $users->links() }}</div>
      @endif
    @endif
  </div>
</div>

{{-- Invite Modal --}}
<div id="inviteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1050;align-items:center;justify-content:center;">
  <div class="ncv-card" style="width:100%;max-width:500px;margin:1rem;">
    <div class="ncv-card-header">
      <h6 class="ncv-card-title">Invite Team Member</h6>
      <button onclick="document.getElementById('inviteModal').style.display='none'" class="ncv-btn ncv-btn-ghost ncv-btn-sm">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="ncv-card-body">
      <form method="POST" action="{{ route('settings.users.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-12">
            <label class="ncv-label">Full Name</label>
            <input type="text" name="name" class="ncv-input" required />
          </div>
          <div class="col-12">
            <label class="ncv-label">Email Address</label>
            <input type="email" name="email" class="ncv-input" required />
          </div>
          <div class="col-12">
            <label class="ncv-label">Temporary Password</label>
            <input type="password" name="password" class="ncv-input" required minlength="8" />
          </div>
          <div class="col-12">
            <label class="ncv-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="ncv-input" required />
          </div>
          <div class="col-12">
            <label class="ncv-label">Role</label>
            <select name="role" class="ncv-select" required>
              <option value="">— Select Role —</option>
              @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" onclick="document.getElementById('inviteModal').style.display='none'"
                    class="ncv-btn ncv-btn-outline">Cancel</button>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-person-plus"></i> Create User
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
