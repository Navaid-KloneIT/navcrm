@extends('layouts.app')

@section('title', isset($article) ? 'Edit Article' : 'New Article')
@section('page-title', isset($article) ? 'Edit Article' : 'New Article')
@section('breadcrumb-items')
  <span class="ncv-breadcrumb-item">Support</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('support.kb-articles.index') }}" class="ncv-breadcrumb-item">Knowledge Base</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h4 fw-semibold mb-1" style="color:var(--text-primary);">
      {{ isset($article) ? 'Edit Article' : 'New Article' }}
    </h1>
    <p class="mb-0" style="color:var(--text-muted);font-size:.875rem;">
      {{ isset($article) ? 'Update knowledge base article.' : 'Create a new how-to guide or FAQ.' }}
    </p>
  </div>
  <a href="{{ route('support.kb-articles.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<form method="POST"
      action="{{ isset($article) ? route('support.kb-articles.update', $article) : route('support.kb-articles.store') }}">
  @csrf
  @if(isset($article)) @method('PUT') @endif

  <div class="row g-4">

    {{-- Article Content --}}
    <div class="col-12 col-lg-8">
      <div class="ncv-card">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Article Content</h6>
        </div>
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $article->title ?? '') }}" placeholder="Article title…">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Category</label>
            <input type="text" name="category"
                   class="form-control @error('category') is-invalid @enderror"
                   value="{{ old('category', $article->category ?? '') }}"
                   placeholder="e.g. Billing, Technical, Account…"
                   list="category-suggestions">
            <datalist id="category-suggestions">
              @foreach($categories as $cat)
                <option value="{{ $cat }}">
              @endforeach
            </datalist>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-0">
            <label class="form-label fw-medium">Body <span class="text-danger">*</span></label>
            <textarea name="body" rows="16" class="form-control @error('body') is-invalid @enderror"
                      placeholder="Write your article content here…">{{ old('body', $article->body ?? '') }}</textarea>
            @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
    </div>

    {{-- Settings Sidebar --}}
    <div class="col-12 col-lg-4">

      {{-- Visibility & Status --}}
      <div class="ncv-card mb-4">
        <div class="ncv-card-header">
          <h6 class="mb-0 fw-semibold">Settings</h6>
        </div>
        <div class="ncv-card-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Visibility</label>
            <div class="d-flex gap-2">
              <div class="flex-fill border rounded-3 p-3 text-center visibility-opt"
                   id="vis-public" onclick="setVisibility(true)" style="cursor:pointer;">
                <i class="bi bi-globe2 d-block mb-1" style="font-size:1.2rem;color:#10b981;"></i>
                <div class="fw-medium" style="font-size:.8rem;">Public</div>
                <div style="color:var(--text-muted);font-size:.72rem;">Customers can see</div>
              </div>
              <div class="flex-fill border rounded-3 p-3 text-center visibility-opt"
                   id="vis-internal" onclick="setVisibility(false)" style="cursor:pointer;">
                <i class="bi bi-lock d-block mb-1" style="font-size:1.2rem;color:var(--text-muted);"></i>
                <div class="fw-medium" style="font-size:.8rem;">Internal</div>
                <div style="color:var(--text-muted);font-size:.72rem;">Agents only</div>
              </div>
            </div>
            <input type="hidden" name="is_public" id="isPublicInput" value="{{ old('is_public', $article->is_public ?? true) ? '1' : '0' }}">
          </div>

          <div class="border-top pt-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_published" id="isPublished"
                     value="1" {{ old('is_published', $article->is_published ?? false) ? 'checked' : '' }}>
              <label class="form-check-label fw-medium" for="isPublished">Published</label>
            </div>
            <p class="mb-0 mt-1" style="color:var(--text-muted);font-size:.78rem;">
              Unpublished articles are in draft mode.
            </p>
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-{{ isset($article) ? 'check-lg' : 'file-earmark-plus' }} me-1"></i>
        {{ isset($article) ? 'Save Changes' : 'Create Article' }}
      </button>
    </div>

  </div>
</form>

@endsection

@push('scripts')
<script>
function setVisibility(isPublic) {
  document.getElementById('isPublicInput').value = isPublic ? '1' : '0';
  document.querySelectorAll('.visibility-opt').forEach(el => {
    el.style.borderColor = '';
    el.style.background  = '';
  });
  const activeEl = document.getElementById(isPublic ? 'vis-public' : 'vis-internal');
  if (activeEl) {
    activeEl.style.borderColor = 'var(--accent-blue)';
    activeEl.style.background  = 'rgba(59,130,246,.06)';
  }
}
// Init
const currentVal = document.getElementById('isPublicInput').value;
setVisibility(currentVal === '1');
</script>
@endpush
