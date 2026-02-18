@extends('layouts.app')

@section('title', 'Web Forms')
@section('page-title', 'Web Forms')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Web Forms</h1>
    <p class="ncv-page-subtitle">Build lead capture forms with automatic routing rules.</p>
  </div>
  <a href="{{ route('marketing.web-forms.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Form
  </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('marketing.web-forms.index') }}">
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <div style="position:relative;flex:1;max-width:300px;">
      <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search forms…"
             class="ncv-input" style="padding-left:2.375rem;height:38px;" />
    </div>
    <select name="active" class="ncv-select" style="width:130px;height:38px;font-size:.82rem;">
      <option value="">All</option>
      <option value="1" {{ request('active')==='1' ? 'selected' : '' }}>Active</option>
      <option value="0" {{ request('active')==='0' ? 'selected' : '' }}>Inactive</option>
    </select>
    <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">Filter</button>
    @if(request()->hasAny(['search','active']))
      <a href="{{ route('marketing.web-forms.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
    @endif
  </div>
</form>

{{-- Table --}}
<div class="ncv-table-wrapper">
  <table class="ncv-table">
    <thead>
      <tr>
        <th>Form Name</th>
        <th>Fields</th>
        <th>Lead Routing</th>
        <th>Submissions</th>
        <th>Status</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($forms as $form)
      <tr>
        <td>
          <a href="{{ route('marketing.web-forms.show', $form) }}"
             class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;">{{ $form->name }}</a>
          @if($form->description)
            <div class="ncv-table-cell-sub">{{ Str::limit($form->description, 55) }}</div>
          @endif
        </td>
        <td style="font-size:.82rem;color:var(--text-secondary);">
          {{ is_array($form->fields) ? count($form->fields) : 0 }} fields
        </td>
        <td style="font-size:.82rem;">
          @if($form->assignedUser)
            <div class="d-flex align-items-center gap-1">
              <i class="bi bi-person-check" style="color:#10b981;font-size:.8rem;"></i>
              <span style="color:var(--text-secondary);">{{ $form->assignedUser->name }}</span>
            </div>
          @elseif($form->assign_by_geography)
            <div class="d-flex align-items-center gap-1">
              <i class="bi bi-geo-alt" style="color:#2563eb;font-size:.8rem;"></i>
              <span style="color:var(--text-secondary);">By Geography</span>
            </div>
          @else
            <span style="color:var(--text-muted);">—</span>
          @endif
        </td>
        <td>
          <span style="font-size:.82rem;font-weight:700;color:var(--ncv-blue-600);">
            {{ number_format($form->total_submissions) }}
          </span>
        </td>
        <td>
          @if($form->is_active)
            <span class="ncv-badge ncv-badge-success"><span class="dot"></span>Active</span>
          @else
            <span class="ncv-badge ncv-badge-muted">Inactive</span>
          @endif
        </td>
        <td style="font-size:.775rem;color:var(--text-muted);">{{ $form->created_at->format('M d, Y') }}</td>
        <td>
          <div class="d-flex gap-1">
            <a href="{{ route('marketing.web-forms.show', $form) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
              <i class="bi bi-eye" style="font-size:.8rem;"></i>
            </a>
            <a href="{{ route('marketing.web-forms.edit', $form) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
              <i class="bi bi-pencil" style="font-size:.8rem;"></i>
            </a>
            <form method="POST" action="{{ route('marketing.web-forms.destroy', $form) }}"
                  onsubmit="return confirm('Delete this form? All submissions will be lost.')">
              @csrf @method('DELETE')
              <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;">
                <i class="bi bi-trash" style="font-size:.8rem;"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center" style="padding:3rem;color:var(--text-muted);">
          <i class="bi bi-ui-checks" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
          No web forms yet. <a href="{{ route('marketing.web-forms.create') }}">Create your first form</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($forms->hasPages())
<div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
    Showing <strong>{{ $forms->firstItem() }}–{{ $forms->lastItem() }}</strong>
    of <strong>{{ $forms->total() }}</strong> forms
  </p>
  {{ $forms->links() }}
</div>
@endif

@endsection
