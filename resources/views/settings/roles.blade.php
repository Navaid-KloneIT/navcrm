@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('breadcrumb-items')
  <a href="{{ route('settings.index') }}" style="color:inherit;text-decoration:none;">Settings</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header">
  <h1 class="ncv-page-title">Roles & Permissions</h1>
  <p class="ncv-page-subtitle">View and manage access control roles for your organization.</p>
</div>

<div class="row g-3">
  @foreach($roles as $role)
  @php
    $colors = ['admin'=>['#fef3c7','#f59e0b'],'manager'=>['#dbeafe','#2563eb'],'sales'=>['#d1fae5','#10b981'],'viewer'=>['#f1f5f9','#94a3b8']];
    $c = $colors[$role->name] ?? ['#f1f5f9','#94a3b8'];
  @endphp
  <div class="col-12 col-md-6">
    <div class="ncv-card">
      <div class="ncv-card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div style="width:44px;height:44px;border-radius:.75rem;background:{{ $c[0] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-shield-fill" style="color:{{ $c[1] }};font-size:1.2rem;"></i>
          </div>
          <div>
            <div style="font-weight:700;font-size:1rem;text-transform:capitalize;">{{ $role->name }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">{{ $role->users_count }} member{{ $role->users_count != 1 ? 's' : '' }}</div>
          </div>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted);">
          @php
            $descriptions = [
              'admin'   => 'Full access to all features including user management, billing, and settings.',
              'manager' => 'Can manage CRM records, view reports, and oversee team members.',
              'sales'   => 'Can create and manage contacts, leads, and opportunities.',
              'viewer'  => 'Read-only access to CRM data. Cannot create or modify records.',
            ];
          @endphp
          {{ $descriptions[$role->name] ?? 'Custom role with specific permissions.' }}
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

<div class="ncv-card mt-3">
  <div class="ncv-card-header">
    <h6 class="ncv-card-title"><i class="bi bi-table me-2" style="color:var(--ncv-blue-500);"></i>Permission Matrix</h6>
  </div>
  <div class="ncv-card-body p-0" style="overflow-x:auto;">
    <table class="table mb-0" style="font-size:.8rem;min-width:600px;">
      <thead style="background:var(--surface-secondary);">
        <tr>
          <th style="padding:.75rem 1.25rem;font-weight:700;">Module</th>
          @foreach($roles as $role)
            <th style="padding:.75rem 1rem;font-weight:700;text-align:center;text-transform:capitalize;">{{ $role->name }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach(['Contacts','Accounts','Leads','Opportunities','Products','Quotes','Forecasts','Settings'] as $module)
        <tr>
          <td style="padding:.625rem 1.25rem;font-weight:600;">{{ $module }}</td>
          @foreach($roles as $role)
            <td style="padding:.625rem 1rem;text-align:center;">
              @if($role->name === 'admin')
                <span style="color:#10b981;font-size:1rem;" title="Full access"><i class="bi bi-check-circle-fill"></i></span>
              @elseif($role->name === 'manager' && $module !== 'Settings')
                <span style="color:#10b981;font-size:1rem;" title="Full access"><i class="bi bi-check-circle-fill"></i></span>
              @elseif($role->name === 'sales' && in_array($module, ['Contacts','Accounts','Leads','Opportunities','Products','Quotes']))
                <span style="color:#2563eb;font-size:1rem;" title="Create & edit"><i class="bi bi-pencil-square"></i></span>
              @elseif($role->name === 'viewer')
                <span style="color:#94a3b8;font-size:1rem;" title="View only"><i class="bi bi-eye-fill"></i></span>
              @else
                <span style="color:#e2e8f0;font-size:1rem;"><i class="bi bi-dash-circle"></i></span>
              @endif
            </td>
          @endforeach
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection
