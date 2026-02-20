@extends('layouts.app')

@section('title', $document ? 'Edit Document' : 'New Document')
@section('page-title', $document ? 'Edit Document' : 'New Document')

@section('breadcrumb-items')
  <a href="{{ route('documents.index') }}" style="color:inherit;text-decoration:none;">Documents</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $document ? 'Edit' : 'New' }}</span>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="row g-3">
  <div class="col-12 col-xl-8">
    <form method="POST" action="{{ $document ? route('documents.update', $document) : route('documents.store') }}">
      @csrf
      @if($document) @method('PUT') @endif

      {{-- Document Info --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-file-earmark-text me-2" style="color:var(--ncv-blue-500);"></i>Document Details</h6>
        </div>
        <div class="ncv-card-body">

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="ncv-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="ncv-input @error('title') is-invalid @enderror"
                     value="{{ old('title', $document?->title) }}" placeholder="e.g. NDA with Acme Corp" required>
              @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
              <label class="ncv-label">Type <span class="text-danger">*</span></label>
              <select name="type" class="ncv-select @error('type') is-invalid @enderror" required>
                <option value="">Select type…</option>
                @foreach(\App\Enums\DocumentType::cases() as $t)
                  <option value="{{ $t->value }}" {{ old('type', $document?->type?->value ?? $selectedTemplate?->type?->value) === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
                @endforeach
              </select>
              @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="ncv-label">Template</label>
              <select name="template_id" class="ncv-select" id="template-select">
                <option value="">No template (blank)</option>
                @foreach($templates as $tpl)
                  <option value="{{ $tpl->id }}"
                    data-type="{{ $tpl->type->value }}"
                    {{ old('template_id', $document?->template_id ?? $selectedTemplate?->id) == $tpl->id ? 'selected' : '' }}>
                    {{ $tpl->name }} ({{ $tpl->type->label() }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="ncv-label">Owner</label>
              <select name="owner_id" class="ncv-select">
                <option value="">Assign to me</option>
                @foreach($users as $u)
                  <option value="{{ $u->id }}" {{ old('owner_id', $document?->owner_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="ncv-label">Account</label>
              <select name="account_id" class="ncv-select" id="account-select">
                <option value="">No account</option>
                @foreach($accounts as $acc)
                  <option value="{{ $acc->id }}" {{ old('account_id', $document?->account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="ncv-label">Contact</label>
              <select name="contact_id" class="ncv-select">
                <option value="">No contact</option>
                @foreach($contacts as $c)
                  <option value="{{ $c->id }}" {{ old('contact_id', $document?->contact_id) == $c->id ? 'selected' : '' }}>{{ $c->first_name }} {{ $c->last_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="ncv-label">Opportunity</label>
              <select name="opportunity_id" class="ncv-select">
                <option value="">No opportunity</option>
                @foreach($opportunities as $opp)
                  <option value="{{ $opp->id }}" {{ old('opportunity_id', $document?->opportunity_id) == $opp->id ? 'selected' : '' }}>{{ $opp->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="ncv-label">Expiry Date</label>
              <input type="date" name="expires_at" class="ncv-input"
                     value="{{ old('expires_at', $document?->expires_at?->format('Y-m-d')) }}">
            </div>
          </div>

          <div class="mb-0">
            <label class="ncv-label">Notes</label>
            <textarea name="notes" class="ncv-input" rows="2" placeholder="Internal notes (not visible to signatory)">{{ old('notes', $document?->notes) }}</textarea>
          </div>

        </div>
      </div>

      {{-- Document Body --}}
      <div class="ncv-card mb-3">
        <div class="ncv-card-header">
          <h6 class="ncv-card-title"><i class="bi bi-file-text me-2" style="color:var(--ncv-blue-500);"></i>Document Content</h6>
        </div>
        <div class="ncv-card-body">
          <div id="quill-editor" style="height:500px;border:1px solid var(--border-color);border-radius:.5rem;background:#fff;">{!! old('body', $prefilledBody ?? $document?->body) !!}</div>
          <textarea name="body" id="body-input" style="display:none;">{{ old('body', $prefilledBody ?? $document?->body) }}</textarea>
          @error('body') <div class="text-danger mt-1" style="font-size:.82rem;">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="ncv-btn ncv-btn-primary">
          <i class="bi bi-check-lg"></i> {{ $document ? 'Save Changes' : 'Create Document' }}
        </button>
        <a href="{{ $document ? route('documents.show', $document) : route('documents.index') }}" class="ncv-btn ncv-btn-ghost">Cancel</a>
        @if($document)
        <form method="POST" action="{{ route('documents.destroy', $document) }}" class="ms-auto" onsubmit="return confirm('Delete this document?')">
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
        <h6 class="ncv-card-title"><i class="bi bi-braces me-2" style="color:var(--ncv-blue-500);"></i>Variables</h6>
      </div>
      <div class="ncv-card-body" style="font-size:.82rem;">
        @foreach([
          ['group' => 'Account',     'vars' => ['Account.Name']],
          ['group' => 'Contact',     'vars' => ['Contact.Name', 'Contact.Email']],
          ['group' => 'Opportunity', 'vars' => ['Opportunity.Name', 'Opportunity.Value']],
          ['group' => 'Date',        'vars' => ['Today.Date']],
          ['group' => 'Company',     'vars' => ['Tenant.Name']],
        ] as $group)
        <div class="mb-2">
          <div style="font-weight:700;color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">{{ $group['group'] }}</div>
          @foreach($group['vars'] as $var)
          <button type="button" onclick="insertVariable('{{{{ {!! $var !!} }}}}')"
                  class="ncv-btn ncv-btn-ghost ncv-btn-sm d-block w-100 text-start mb-1"
                  style="font-family:monospace;font-size:.78rem;border:1px solid var(--border-color);">
            <i class="bi bi-braces me-1" style="font-size:.7rem;color:var(--ncv-blue-500);"></i>{{{{ {!! $var !!} }}}}
          </button>
          @endforeach
        </div>
        @endforeach
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

document.querySelector('form').addEventListener('submit', function() {
  document.getElementById('body-input').value = quill.root.innerHTML;
});

function insertVariable(varText) {
  const range = quill.getSelection(true);
  quill.insertText(range ? range.index : quill.getLength(), varText);
}
</script>
@endpush
