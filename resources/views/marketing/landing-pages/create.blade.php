@extends('layouts.app')

@section('title', isset($landingPage) ? 'Edit Landing Page' : 'New Landing Page')
@section('page-title', isset($landingPage) ? 'Edit Landing Page' : 'New Landing Page')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.landing-pages.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Landing Pages</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($landingPage) ? 'Edit Landing Page' : 'New Landing Page' }}</h1>
        <p class="ncv-page-subtitle">Build a targeted page for a specific offer or campaign.</p>
      </div>
      <a href="{{ route('marketing.landing-pages.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($landingPage) ? route('marketing.landing-pages.update', $landingPage) : route('marketing.landing-pages.store') }}">
      @csrf
      @if(isset($landingPage)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Page Info --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-window me-2" style="color:var(--ncv-blue-500);"></i>Page Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="name">Internal Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $landingPage->name ?? '') }}"
                         placeholder="e.g. Spring Promo – US West" required
                         oninput="autoSlug(this.value)" />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="slug">URL Slug</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-link-45deg ncv-input-icon"></i>
                    <input type="text" class="ncv-input @error('slug') is-invalid @enderror"
                           id="slug" name="slug"
                           value="{{ old('slug', $landingPage->slug ?? '') }}"
                           placeholder="spring-promo-us-west"
                           pattern="[a-z0-9\-]+" />
                  </div>
                  <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">Lowercase letters, numbers, hyphens only.</div>
                  @error('slug')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end pb-1">
                  <label class="d-flex align-items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $landingPage->is_active ?? true) ? 'checked' : '' }}
                           style="accent-color:var(--ncv-blue-500);width:16px;height:16px;" />
                    <span class="ncv-label mb-0">Active</span>
                  </label>
                </div>
                <div class="col-12 col-md-8">
                  <label class="ncv-label" for="title">Page Title (H1) <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('title') is-invalid @enderror"
                         id="title" name="title"
                         value="{{ old('title', $landingPage->title ?? '') }}"
                         placeholder="e.g. Get 30% Off — Spring Sale Ends Sunday" required />
                  @error('title')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="web_form_id">Embedded Form</label>
                  <select class="ncv-select" id="web_form_id" name="web_form_id">
                    <option value="">— None —</option>
                    @foreach($forms as $form)
                      <option value="{{ $form->id }}" {{ old('web_form_id', $landingPage->web_form_id ?? '') == $form->id ? 'selected' : '' }}>{{ $form->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="description">Description / Subheadline</label>
                  <textarea class="ncv-textarea" name="description" rows="2"
                            placeholder="Supporting copy that appears below the headline…">{{ old('description', $landingPage->description ?? '') }}</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Content --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-code-slash me-2" style="color:var(--ncv-blue-500);"></i>Page Content (HTML)</h6>
            </div>
            <div class="ncv-card-body" style="padding:0;">
              <textarea name="content" rows="16"
                        style="width:100%;border:none;font-family:monospace;font-size:.82rem;padding:1rem;resize:vertical;outline:none;border-radius:0 0 .75rem .75rem;"
                        placeholder="Paste or write your HTML page body here…">{{ old('content', $landingPage->content ?? '') }}</textarea>
            </div>
          </div>
        </div>

        {{-- SEO --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-search me-2" style="color:var(--ncv-blue-500);"></i>SEO / Meta</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="meta_title">Meta Title</label>
                  <input type="text" class="ncv-input" id="meta_title" name="meta_title"
                         value="{{ old('meta_title', $landingPage->meta_title ?? '') }}"
                         placeholder="SEO page title (max 60 chars)" maxlength="60" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="meta_description">Meta Description</label>
                  <textarea class="ncv-textarea" name="meta_description" rows="2"
                            placeholder="Brief summary for search engines (max 160 chars)" maxlength="160">{{ old('meta_description', $landingPage->meta_description ?? '') }}</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('marketing.landing-pages.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($landingPage) ? 'Update Page' : 'Create Page' }}
            </button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function autoSlug(val) {
    const slugEl = document.getElementById('slug');
    if (slugEl.dataset.manual === 'true') return;
    slugEl.value = val.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }
  document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = 'true';
  });
</script>
@endpush
