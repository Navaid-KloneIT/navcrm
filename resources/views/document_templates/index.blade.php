@extends('layouts.app')

@section('title', 'Document Templates')
@section('page-title', 'Document Templates')

@section('breadcrumb-items')
  <a href="{{ route('documents.index') }}" style="color:inherit;text-decoration:none;">Documents</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Templates</span>
@endsection

@section('content')

{{-- Toolbar --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex gap-2 flex-wrap flex-grow-1">
        <div class="ncv-input-group" style="max-width:280px;flex:1;">
          <i class="bi bi-search ncv-input-icon"></i>
          <input type="text" name="search" value="{{ request('search') }}" class="ncv-input ncv-input-search" placeholder="Search templates…">
        </div>
        <select name="type" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Types</option>
          @foreach(\App\Enums\DocumentType::cases() as $t)
            <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
          @endforeach
        </select>
        @if(request()->hasAny(['search','type']))
          <a href="{{ route('document-templates.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
        @endif
      </form>
      <a href="{{ route('document-templates.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
        <i class="bi bi-plus-lg"></i> New Template
      </a>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($templates->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-layout-text-window" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No templates yet</p>
        <p style="font-size:.82rem;">Create reusable HTML templates with <code>{{Variable}}</code> placeholders.</p>
        <a href="{{ route('document-templates.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New Template
        </a>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($templates as $template)
          <tr>
            <td>
              <a href="{{ route('document-templates.show', $template) }}" class="ncv-table-cell-primary" style="color:var(--text-primary);text-decoration:none;font-weight:600;">
                {{ $template->name }}
              </a>
            </td>
            <td>
              <span class="ncv-badge" style="background:#eff6ff;color:#1d4ed8;">{{ $template->type->label() }}</span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);max-width:240px;">
              {{ Str::limit($template->description, 60) ?: '—' }}
            </td>
            <td>
              @if($template->is_active)
                <span class="ncv-badge" style="background:#dcfce7;color:#166534;">Active</span>
              @else
                <span class="ncv-badge" style="background:#f1f5f9;color:#64748b;">Inactive</span>
              @endif
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $template->created_at->format('M j, Y') }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ route('document-templates.show', $template) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
                <a href="{{ route('document-templates.edit', $template) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
                <form method="POST" action="{{ route('document-templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Delete" style="color:#ef4444;"><i class="bi bi-trash" style="font-size:.8rem;"></i></button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($templates->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-top:1px solid var(--border-color);font-size:.82rem;">
    <span style="color:var(--text-muted);">Showing {{ $templates->firstItem() }}–{{ $templates->lastItem() }} of {{ $templates->total() }}</span>
    {{ $templates->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
