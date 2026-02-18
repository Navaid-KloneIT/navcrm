@extends('layouts.app')

@section('title', $landingPage->name)
@section('page-title', $landingPage->name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.landing-pages.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Landing Pages</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="ncv-page-title mb-0">{{ $landingPage->name }}</h1>
      @if($landingPage->is_active)
        <span class="ncv-badge ncv-badge-success"><span class="dot"></span>Active</span>
      @else
        <span class="ncv-badge ncv-badge-muted">Inactive</span>
      @endif
    </div>
    <div style="font-size:.78rem;color:var(--ncv-blue-600);font-family:monospace;">/{{ $landingPage->slug }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('marketing.landing-pages.edit', $landingPage) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <form method="POST" action="{{ route('marketing.landing-pages.destroy', $landingPage) }}"
          onsubmit="return confirm('Delete this landing page?')">
      @csrf @method('DELETE')
      <button class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:.625rem;">
        <i class="bi bi-trash"></i> Delete
      </button>
    </form>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-4">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6></div>
      <div class="ncv-card-body">
        <dl style="margin:0;display:grid;gap:.75rem;">
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Title</dt>
            <dd style="margin:0;font-size:.875rem;font-weight:600;">{{ $landingPage->title }}</dd>
          </div>
          @if($landingPage->description)
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Description</dt>
            <dd style="margin:0;font-size:.85rem;color:var(--text-secondary);">{{ $landingPage->description }}</dd>
          </div>
          @endif
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Page Views</dt>
            <dd style="margin:0;font-size:1.2rem;font-weight:800;color:var(--ncv-blue-600);">{{ number_format($landingPage->page_views) }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Embedded Form</dt>
            <dd style="margin:0;font-size:.875rem;">
              @if($landingPage->webForm)
                <a href="{{ route('marketing.web-forms.show', $landingPage->webForm) }}" style="color:var(--ncv-blue-600);">
                  {{ $landingPage->webForm->name }}
                </a>
              @else
                <span style="color:var(--text-muted);">None</span>
              @endif
            </dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Created By</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $landingPage->creator?->name ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Created</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $landingPage->created_at->format('M d, Y') }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-8">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-eye me-2" style="color:var(--ncv-blue-500);"></i>Content Preview</h6></div>
      <div style="padding:1.5rem;min-height:300px;border-top:1px solid var(--border-color);">
        @if($landingPage->content)
          {!! $landingPage->content !!}
        @else
          <div style="text-align:center;color:var(--text-muted);padding:3rem 0;">
            <i class="bi bi-file-earmark" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
            No content added yet.
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
