@extends('layouts.app')

@section('title', 'Landing Pages')
@section('page-title', 'Landing Pages')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Landing Pages</h1>
    <p class="ncv-page-subtitle">Create targeted pages for offers, events, and lead capture.</p>
  </div>
  <a href="{{ route('marketing.landing-pages.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Landing Page
  </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('marketing.landing-pages.index') }}">
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <div style="position:relative;flex:1;max-width:300px;">
      <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search pages…"
             class="ncv-input" style="padding-left:2.375rem;height:38px;" />
    </div>
    <select name="active" class="ncv-select" style="width:130px;height:38px;font-size:.82rem;">
      <option value="">All</option>
      <option value="1" {{ request('active')==='1' ? 'selected' : '' }}>Active</option>
      <option value="0" {{ request('active')==='0' ? 'selected' : '' }}>Inactive</option>
    </select>
    <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">Filter</button>
    @if(request()->hasAny(['search','active']))
      <a href="{{ route('marketing.landing-pages.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
    @endif
  </div>
</form>

{{-- Grid --}}
<div class="row g-3">
  @forelse($pages as $page)
  <div class="col-12 col-md-6 col-xl-4">
    <div class="ncv-card h-100" style="display:flex;flex-direction:column;overflow:hidden;">
      {{-- Mockup bar --}}
      <div style="height:80px;background:linear-gradient(135deg,#1e3a5f,#2563eb);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;opacity:.15;background:repeating-linear-gradient(45deg,#fff,#fff 1px,transparent 1px,transparent 10px);"></div>
        <i class="bi bi-window" style="font-size:2rem;color:rgba(255,255,255,.7);"></i>
      </div>
      <div class="ncv-card-body" style="flex:1;display:flex;flex-direction:column;">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
          <div style="flex:1;min-width:0;">
            <a href="{{ route('marketing.landing-pages.show', $page) }}"
               style="font-weight:700;font-size:.9rem;color:var(--text-primary);text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $page->name }}
            </a>
            <div style="font-size:.75rem;color:var(--ncv-blue-600);font-family:monospace;margin-top:.1rem;">/{{ $page->slug }}</div>
          </div>
          @if($page->is_active)
            <span class="ncv-badge ncv-badge-success" style="flex-shrink:0;"><span class="dot"></span>Active</span>
          @else
            <span class="ncv-badge ncv-badge-muted" style="flex-shrink:0;">Inactive</span>
          @endif
        </div>
        @if($page->description)
          <p style="font-size:.78rem;color:var(--text-muted);flex:1;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:.75rem;">
            {{ $page->description }}
          </p>
        @else
          <div style="flex:1;"></div>
        @endif
        <div class="d-flex align-items-center gap-3 mb-2" style="font-size:.75rem;color:var(--text-muted);">
          <span><i class="bi bi-eye me-1"></i>{{ number_format($page->page_views) }} views</span>
          @if($page->webForm)
            <span><i class="bi bi-ui-checks me-1"></i>{{ $page->webForm->name }}</span>
          @endif
        </div>
        <div class="d-flex align-items-center justify-content-between pt-2" style="border-top:1px solid var(--border-color);">
          <span style="font-size:.72rem;color:var(--text-muted);">{{ $page->created_at->diffForHumans() }}</span>
          <div class="d-flex gap-1">
            <a href="{{ route('marketing.landing-pages.show', $page) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
              <i class="bi bi-eye" style="font-size:.8rem;"></i>
            </a>
            <a href="{{ route('marketing.landing-pages.edit', $page) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
              <i class="bi bi-pencil" style="font-size:.8rem;"></i>
            </a>
            <form method="POST" action="{{ route('marketing.landing-pages.destroy', $page) }}"
                  onsubmit="return confirm('Delete this landing page?')">
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
      <i class="bi bi-window" style="font-size:2.5rem;color:var(--text-muted);opacity:.3;display:block;margin-bottom:.75rem;"></i>
      <p style="color:var(--text-muted);margin:0;">No landing pages yet. <a href="{{ route('marketing.landing-pages.create') }}">Create your first page</a>.</p>
    </div>
  </div>
  @endforelse
</div>

@if($pages->hasPages())
<div class="d-flex align-items-center justify-content-between mt-4 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
    Showing <strong>{{ $pages->firstItem() }}–{{ $pages->lastItem() }}</strong>
    of <strong>{{ $pages->total() }}</strong> pages
  </p>
  {{ $pages->links() }}
</div>
@endif

@endsection
