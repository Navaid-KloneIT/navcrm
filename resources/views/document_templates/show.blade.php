@extends('layouts.app')

@section('title', $template->name)
@section('page-title', $template->name)

@section('breadcrumb-items')
  <a href="{{ route('document-templates.index') }}" style="color:inherit;text-decoration:none;">Templates</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $template->name }}</span>
@endsection

@section('content')
<div class="row g-3">

  {{-- Template Info + Preview --}}
  <div class="col-12 col-xl-8">
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-layout-text-window me-2" style="color:var(--ncv-blue-500);"></i>{{ $template->name }}</h6>
        <div class="d-flex gap-2">
          <a href="{{ route('document-templates.edit', $template) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-pencil"></i> Edit</a>
          <a href="{{ route('documents.create', ['template_id' => $template->id]) }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
            <i class="bi bi-file-earmark-plus"></i> Use Template
          </a>
        </div>
      </div>
      <div class="ncv-card-body">
        <div class="d-flex flex-wrap gap-3 mb-3" style="font-size:.82rem;">
          <div>
            <span style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Type</span>
            <div><span class="ncv-badge" style="background:#eff6ff;color:#1d4ed8;">{{ $template->type->label() }}</span></div>
          </div>
          <div>
            <span style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Status</span>
            <div>
              @if($template->is_active)
                <span class="ncv-badge" style="background:#dcfce7;color:#166534;">Active</span>
              @else
                <span class="ncv-badge" style="background:#f1f5f9;color:#64748b;">Inactive</span>
              @endif
            </div>
          </div>
          <div>
            <span style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Created By</span>
            <div style="color:var(--text-secondary);">{{ $template->createdBy?->name ?? '—' }}</div>
          </div>
          <div>
            <span style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Created</span>
            <div style="color:var(--text-secondary);">{{ $template->created_at->format('M j, Y') }}</div>
          </div>
        </div>
        @if($template->description)
        <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:1.25rem;">{{ $template->description }}</p>
        @endif
      </div>
    </div>

    {{-- Body Preview --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-eye me-2" style="color:var(--ncv-blue-500);"></i>Template Preview</h6>
      </div>
      <div class="ncv-card-body">
        <div style="border:1px solid var(--border-color);border-radius:.5rem;padding:1.5rem;background:#fff;font-size:.9rem;line-height:1.7;min-height:200px;">
          {!! $template->body !!}
        </div>
      </div>
    </div>
  </div>

  {{-- Side Panel: documents using this template --}}
  <div class="col-12 col-xl-4">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-file-earmark me-2" style="color:var(--ncv-blue-500);"></i>Documents Using This Template</h6>
        <span style="font-size:.78rem;color:var(--text-muted);">{{ $template->documents->count() }}</span>
      </div>
      <div class="ncv-card-body p-0">
        @forelse($template->documents->take(10) as $doc)
        <div style="padding:.6rem 1rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
          <div>
            <a href="{{ route('documents.show', $doc) }}" style="font-size:.82rem;font-weight:600;color:var(--ncv-blue-600);text-decoration:none;">{{ $doc->document_number }}</a>
            <div style="font-size:.75rem;color:var(--text-muted);">{{ Str::limit($doc->title, 40) }}</div>
          </div>
          <span class="ncv-badge bg-{{ $doc->status->color() }}-subtle text-{{ $doc->status->color() }}" style="font-size:.7rem;">{{ $doc->status->label() }}</span>
        </div>
        @empty
        <div class="text-center py-4" style="color:var(--text-muted);font-size:.82rem;">
          <i class="bi bi-file-earmark" style="font-size:1.5rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
          No documents yet
        </div>
        @endforelse
      </div>
      @if($template->documents->count() > 0)
      <div class="ncv-card-footer" style="padding:.75rem 1rem;">
        <a href="{{ route('documents.create', ['template_id' => $template->id]) }}" class="ncv-btn ncv-btn-primary ncv-btn-sm w-100">
          <i class="bi bi-file-earmark-plus"></i> Create Document from Template
        </a>
      </div>
      @endif
    </div>
  </div>

</div>
@endsection
