@extends('layouts.app')

@section('title', 'Knowledge Base')
@section('page-title', 'Knowledge Base')
@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Support</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">Knowledge Base</h1>
    <p class="mb-0" style="color:var(--text-muted); font-size:.875rem;">How-to guides, FAQs, and solutions for customers and agents.</p>
  </div>
  <a href="{{ route('support.kb-articles.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
    <i class="bi bi-plus-lg"></i> New Article
  </a>
</div>

{{-- Filters --}}
<div class="ncv-card mb-4">
  <div class="ncv-card-body">
    <form method="GET" action="{{ route('support.kb-articles.index') }}" class="row g-3 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label form-label-sm">Search</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Title, body, or category…" value="{{ request('search') }}">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Category</label>
        <select name="category" class="form-select form-select-sm">
          <option value="">All</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Visibility</label>
        <select name="visibility" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="public"   {{ request('visibility') === 'public'   ? 'selected' : '' }}>Public</option>
          <option value="internal" {{ request('visibility') === 'internal' ? 'selected' : '' }}>Internal</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm">Status</label>
        <select name="published" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="1" {{ request('published') === '1' ? 'selected' : '' }}>Published</option>
          <option value="0" {{ request('published') === '0' ? 'selected' : '' }}>Draft</option>
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
        <a href="{{ route('support.kb-articles.index') }}" class="btn btn-outline-secondary btn-sm">✕</a>
      </div>
    </form>
  </div>
</div>

{{-- Articles List --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($articles->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-book" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No articles found</p>
        <p class="small mb-3">Start building your knowledge base.</p>
        <a href="{{ route('support.kb-articles.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-1"></i> Create Article
        </a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
          <thead style="background:var(--bg-subtle); border-bottom:1px solid var(--border-color);">
            <tr>
              <th class="ps-4 py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Article</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Category</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Visibility</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Status</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Author</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Views</th>
              <th class="py-3" style="color:var(--text-muted);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;">Updated</th>
              <th class="py-3 pe-4"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($articles as $article)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td class="ps-4 py-3">
                <a href="{{ route('support.kb-articles.show', $article) }}"
                   class="fw-medium text-decoration-none" style="color:var(--accent-blue);">
                  {{ $article->title }}
                </a>
              </td>
              <td class="py-3">
                @if($article->category)
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.72rem;">
                    {{ $article->category }}
                  </span>
                @else
                  <span style="color:var(--text-muted);">—</span>
                @endif
              </td>
              <td class="py-3">
                @if($article->is_public)
                  <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.72rem;">
                    <i class="bi bi-globe2 me-1"></i>Public
                  </span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.72rem;">
                    <i class="bi bi-lock me-1"></i>Internal
                  </span>
                @endif
              </td>
              <td class="py-3">
                @if($article->is_published)
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.72rem;">Published</span>
                @else
                  <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.72rem;">Draft</span>
                @endif
              </td>
              <td class="py-3" style="color:var(--text-secondary);">{{ $article->author?->name ?? '—' }}</td>
              <td class="py-3" style="color:var(--text-muted);">{{ number_format($article->view_count) }}</td>
              <td class="py-3" style="color:var(--text-muted);font-size:.8rem;">{{ $article->updated_at->format('M j, Y') }}</td>
              <td class="py-3 pe-4">
                <div class="d-flex gap-1">
                  <a href="{{ route('support.kb-articles.show', $article) }}"
                     class="btn btn-ghost btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="{{ route('support.kb-articles.edit', $article) }}"
                     class="btn btn-ghost btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form method="POST" action="{{ route('support.kb-articles.destroy', $article) }}"
                        onsubmit="return confirm('Delete this article?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm text-danger" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($articles->hasPages())
      <div class="d-flex align-items-center justify-content-between px-4 py-3"
           style="border-top:1px solid var(--border-color);">
        <span style="color:var(--text-muted);font-size:.875rem;">
          Showing {{ $articles->firstItem() }}–{{ $articles->lastItem() }} of {{ $articles->total() }}
        </span>
        {{ $articles->links('pagination::bootstrap-5') }}
      </div>
      @endif
    @endif
  </div>
</div>

@endsection
