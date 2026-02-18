@extends('layouts.app')

@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Email Templates</h1>
    <p class="ncv-page-subtitle">Design reusable HTML email templates for your campaigns.</p>
  </div>
  <a href="{{ route('marketing.email-templates.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Template
  </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('marketing.email-templates.index') }}">
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <div style="position:relative;flex:1;max-width:300px;">
      <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search templates…"
             class="ncv-input" style="padding-left:2.375rem;height:38px;" />
    </div>
    <select name="active" class="ncv-select" style="width:130px;height:38px;font-size:.82rem;">
      <option value="">All</option>
      <option value="1" {{ request('active')==='1' ? 'selected' : '' }}>Active</option>
      <option value="0" {{ request('active')==='0' ? 'selected' : '' }}>Inactive</option>
    </select>
    <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">Filter</button>
    @if(request()->hasAny(['search','active']))
      <a href="{{ route('marketing.email-templates.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
    @endif
  </div>
</form>

{{-- Grid --}}
<div class="row g-3">
  @forelse($templates as $template)
  <div class="col-12 col-md-6 col-xl-4">
    <div class="ncv-card h-100" style="display:flex;flex-direction:column;">
      {{-- Preview bar --}}
      <div style="height:6px;border-radius:.75rem .75rem 0 0;background:linear-gradient(90deg,var(--ncv-blue-500),var(--ncv-blue-400));"></div>
      <div class="ncv-card-body" style="flex:1;display:flex;flex-direction:column;">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
          <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:.9rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $template->name }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <i class="bi bi-envelope me-1"></i>{{ $template->subject }}
            </div>
          </div>
          @if($template->is_active)
            <span class="ncv-badge ncv-badge-success" style="flex-shrink:0;"><span class="dot"></span>Active</span>
          @else
            <span class="ncv-badge ncv-badge-muted" style="flex-shrink:0;">Inactive</span>
          @endif
        </div>
        <div style="font-size:.775rem;color:var(--text-muted);flex:1;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">
          {!! Str::limit(strip_tags($template->body), 120) !!}
        </div>
        <div class="d-flex align-items-center justify-content-between mt-3 pt-2" style="border-top:1px solid var(--border-color);">
          <span style="font-size:.72rem;color:var(--text-muted);">{{ $template->created_at->diffForHumans() }}</span>
          <div class="d-flex gap-1">
            <a href="{{ route('marketing.email-templates.edit', $template) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
              <i class="bi bi-pencil" style="font-size:.8rem;"></i>
            </a>
            <form method="POST" action="{{ route('marketing.email-templates.destroy', $template) }}"
                  onsubmit="return confirm('Delete this template?')">
              @csrf @method('DELETE')
              <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;" title="Delete">
                <i class="bi bi-trash" style="font-size:.8rem;"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="ncv-card" style="padding:3rem;text-align:center;">
      <i class="bi bi-envelope-open" style="font-size:2.5rem;color:var(--text-muted);opacity:.3;display:block;margin-bottom:.75rem;"></i>
      <p style="color:var(--text-muted);margin:0;">No email templates yet.
        <a href="{{ route('marketing.email-templates.create') }}">Create your first template</a>.
      </p>
    </div>
  </div>
  @endforelse
</div>

@if($templates->hasPages())
<div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
    Showing <strong>{{ $templates->firstItem() }}–{{ $templates->lastItem() }}</strong>
    of <strong>{{ $templates->total() }}</strong> templates
  </p>
  {{ $templates->links() }}
</div>
@endif

@endsection
