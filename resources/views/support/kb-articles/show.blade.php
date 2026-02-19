@extends('layouts.app')

@section('title', $kbArticle->title)
@section('page-title', $kbArticle->title)
@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Support</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('support.kb-articles.index') }}" class="ncv-breadcrumb-item">Knowledge Base</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="h4 fw-semibold mb-0" style="color:var(--text-primary);">{{ $kbArticle->title }}</h1>
      @if($kbArticle->is_public)
        <span class="badge bg-success-subtle text-success border border-success-subtle">
          <i class="bi bi-globe2 me-1"></i>Public
        </span>
      @else
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
          <i class="bi bi-lock me-1"></i>Internal
        </span>
      @endif
      @if($kbArticle->is_published)
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Published</span>
      @else
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft</span>
      @endif
    </div>
    <div style="color:var(--text-muted);font-size:.875rem;">
      @if($kbArticle->category)
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-2">{{ $kbArticle->category }}</span>
      @endif
      By {{ $kbArticle->author?->name ?? 'Unknown' }} &middot;
      Updated {{ $kbArticle->updated_at->format('M j, Y') }} &middot;
      {{ number_format($kbArticle->view_count) }} view{{ $kbArticle->view_count !== 1 ? 's' : '' }}
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('support.kb-articles.edit', $kbArticle) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('support.kb-articles.destroy', $kbArticle) }}"
          onsubmit="return confirm('Delete this article?')">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash me-1"></i> Delete
      </button>
    </form>
  </div>
</div>

<div class="row g-4">
  <div class="col-12 col-lg-9">
    <div class="ncv-card">
      <div class="ncv-card-body">
        <div style="color:var(--text-secondary);line-height:1.7;white-space:pre-wrap;">{{ $kbArticle->body }}</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-3">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="mb-0 fw-semibold">Article Info</h6>
      </div>
      <div class="ncv-card-body">
        <dl class="mb-0" style="font-size:.875rem;">
          <dt class="text-muted fw-normal mb-1">Author</dt>
          <dd class="mb-3">{{ $kbArticle->author?->name ?? '—' }}</dd>

          <dt class="text-muted fw-normal mb-1">Category</dt>
          <dd class="mb-3">{{ $kbArticle->category ?? '—' }}</dd>

          <dt class="text-muted fw-normal mb-1">Visibility</dt>
          <dd class="mb-3">{{ $kbArticle->is_public ? 'Public' : 'Internal only' }}</dd>

          <dt class="text-muted fw-normal mb-1">Status</dt>
          <dd class="mb-3">{{ $kbArticle->is_published ? 'Published' : 'Draft' }}</dd>

          <dt class="text-muted fw-normal mb-1">Views</dt>
          <dd class="mb-3">{{ number_format($kbArticle->view_count) }}</dd>

          <dt class="text-muted fw-normal mb-1">Created</dt>
          <dd class="mb-3">{{ $kbArticle->created_at->format('M j, Y') }}</dd>

          <dt class="text-muted fw-normal mb-0">Last Updated</dt>
          <dd class="mb-0">{{ $kbArticle->updated_at->format('M j, Y') }}</dd>
        </dl>
      </div>
    </div>
  </div>
</div>

@endsection
