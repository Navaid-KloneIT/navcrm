@extends('layouts.app')

@section('title', $template ? 'Edit Template' : 'New Template')
@section('page-title', $template ? 'Edit Template' : 'New Template')

@section('breadcrumb-items')
  <a href="{{ route('document-templates.index') }}" style="color:inherit;text-decoration:none;">Templates</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $template ? 'Edit' : 'New' }}</span>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="row g-3">
  <div class="col-12 col-xl-8">
    <form method="POST" action="{{ $template ? route('document-templates.update', $template) : route('document-templates.store') }}">
      @csrf
      @if($template) @method('PUT') @endif

      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-layout-text-window me-2" style="color:var(--ncv-blue-500);"></i>Template Details</h6>
        </div>
        <div class="ncv-card-body">

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="ncv-label">Template Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="ncv-input @error('name') is-invalid @enderror"
                     value="{{ old('name', $template?->name) }}" placeholder="e.g. Standard NDA" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
              <label class="ncv-label">Type <span class="text-danger">*</span></label>
              <select name="type" class="ncv-select @error('type') is-invalid @enderror" required>
                <option value="">Select type…</option>
                @foreach(\App\Enums\DocumentType::cases() as $t)
                  <option value="{{ $t->value }}" {{ old('type', $template?->type?->value) === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
                @endforeach
              </select>
              @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="ncv-label">Description</label>
            <input type="text" name="description" class="ncv-input"
                   value="{{ old('description', $template?->description) }}" placeholder="Brief description of when to use this template">
          </div>

          <div class="mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <label class="ncv-label mb-0">Template Body <span class="text-danger">*</span></label>
            </div>
            {{-- Quill editor --}}
            <div id="quill-editor" style="height:420px;border:1px solid var(--border-color);border-radius:.5rem;background:#fff;">{!! old('body', $template?->body) !!}</div>
            <textarea name="body" id="body-input" style="display:none;">{{ old('body', $template?->body) }}</textarea>
            @error('body') <div class="text-danger mt-1" style="font-size:.82rem;">{{ $message }}</div> @enderror
          </div>

          <div class="d-flex align-items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                   {{ old('is_active', $template ? ($template->is_active ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
            <label for="is_active" class="ncv-label mb-0">Active (available for use in new documents)</label>
          </div>

        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-check-lg"></i> {{ $template ? 'Save Changes' : 'Create Template' }}
        </button>
        <a href="{{ $template ? route('document-templates.show', $template) : route('document-templates.index') }}" class="ncv-btn ncv-btn-ghost">Cancel</a>
        @if($template)
        <form method="POST" action="{{ route('document-templates.destroy', $template) }}" class="ms-auto" onsubmit="return confirm('Delete this template?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-ghost" style="color:#ef4444;"><i class="bi bi-trash"></i> Delete</button>
        </form>
        @endif
      </div>

    </form>
  </div>

  {{-- Variable Cheatsheet --}}
  <div class="col-12 col-xl-4">
    <div class="ncv-card" style="position:sticky;top:1rem;">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-braces me-2" style="color:var(--ncv-blue-500);"></i>Available Variables</h6>
      </div>
      <div class="ncv-card-body" style="font-size:.82rem;">
        <p style="color:var(--text-muted);margin-bottom:.75rem;">Click a variable to insert it at the cursor position in the editor.</p>
        @foreach([
          ['group' => 'Account',     'vars' => ['Account.Name']],
          ['group' => 'Contact',     'vars' => ['Contact.Name', 'Contact.Email']],
          ['group' => 'Opportunity', 'vars' => ['Opportunity.Name', 'Opportunity.Value']],
          ['group' => 'Date',        'vars' => ['Today.Date']],
          ['group' => 'Company',     'vars' => ['Tenant.Name']],
        ] as $group)
        <div class="mb-3">
          <div style="font-weight:700;color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">{{ $group['group'] }}</div>
          @foreach($group['vars'] as $var)
          <button type="button" onclick="insertVariable('{{{{ {!! $var !!} }}}}')"
                  class="ncv-btn ncv-btn-ghost ncv-btn-sm d-block w-100 text-start mb-1"
                  style="font-family:monospace;font-size:.8rem;border:1px solid var(--border-color);">
            <i class="bi bi-braces me-1" style="font-size:.7rem;color:var(--ncv-blue-500);"></i>{{{{ {!! $var !!} }}}}
          </button>
          @endforeach
        </div>
        @endforeach
        <div style="background:#fef9c3;border:1px solid #fde047;border-radius:.375rem;padding:.6rem .75rem;font-size:.78rem;color:#713f12;">
          <i class="bi bi-lightbulb me-1"></i> Variables are auto-filled when generating a document from this template. Any remaining <code>{{placeholder}}</code> can be edited manually.
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const quill = new Quill('#quill-editor', {
  theme: 'snow',
  modules: {
    toolbar: [
      [{ 'header': [1, 2, 3, false] }],
      ['bold', 'italic', 'underline'],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'align': [] }],
      ['link'],
      ['clean']
    ]
  }
});

// Sync Quill content to textarea on form submit
document.querySelector('form').addEventListener('submit', function() {
  document.getElementById('body-input').value = quill.root.innerHTML;
});

function insertVariable(varText) {
  const range = quill.getSelection(true);
  quill.insertText(range ? range.index : quill.getLength(), varText);
}
</script>
@endpush
