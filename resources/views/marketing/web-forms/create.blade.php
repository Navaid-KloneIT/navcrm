@extends('layouts.app')

@section('title', isset($webForm) ? 'Edit Web Form' : 'New Web Form')
@section('page-title', isset($webForm) ? 'Edit Web Form' : 'New Web Form')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.web-forms.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Web Forms</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .field-card { background:#f8fafc; border:1.5px solid var(--border-color); border-radius:.75rem; padding:.875rem 1rem; position:relative; }
  .field-card:hover { border-color:var(--ncv-blue-300); }
  .drag-handle { cursor:grab; color:var(--text-muted); padding:.25rem; }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-11">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($webForm) ? 'Edit Web Form' : 'New Web Form' }}</h1>
        <p class="ncv-page-subtitle">Build a lead capture form and configure routing rules.</p>
      </div>
      <a href="{{ route('marketing.web-forms.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($webForm) ? route('marketing.web-forms.update', $webForm) : route('marketing.web-forms.store') }}"
          id="formBuilder">
      @csrf
      @if(isset($webForm)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Form Info --}}
        <div class="col-12 col-lg-8">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-ui-checks me-2" style="color:var(--ncv-blue-500);"></i>Form Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-8">
                  <label class="ncv-label" for="name">Form Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $webForm->name ?? '') }}"
                         placeholder="e.g. Contact Us Form" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end pb-1">
                  <label class="d-flex align-items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $webForm->is_active ?? true) ? 'checked' : '' }}
                           style="accent-color:var(--ncv-blue-500);width:16px;height:16px;" />
                    <span class="ncv-label mb-0">Active</span>
                  </label>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="description">Description</label>
                  <textarea class="ncv-textarea" name="description" rows="2"
                            placeholder="Internal note about where this form is used…">{{ old('description', $webForm->description ?? '') }}</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Settings --}}
        <div class="col-12 col-lg-4">
          <div class="ncv-card">
            <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-gear me-2" style="color:var(--ncv-blue-500);"></i>Settings</h6></div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="submit_button_text">Button Text</label>
                  <input type="text" class="ncv-input" id="submit_button_text" name="submit_button_text"
                         value="{{ old('submit_button_text', $webForm->submit_button_text ?? 'Submit') }}"
                         placeholder="Submit" />
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="success_message">Success Message</label>
                  <textarea class="ncv-textarea" name="success_message" rows="2"
                            placeholder="Thank you! We'll be in touch.">{{ old('success_message', $webForm->success_message ?? '') }}</textarea>
                </div>
                <div class="col-12">
                  <label class="ncv-label" for="redirect_url">Redirect URL (optional)</label>
                  <div class="ncv-input-group">
                    <i class="bi bi-link-45deg ncv-input-icon"></i>
                    <input type="url" class="ncv-input" id="redirect_url" name="redirect_url"
                           value="{{ old('redirect_url', $webForm->redirect_url ?? '') }}"
                           placeholder="https://…" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Field Builder --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header d-flex align-items-center justify-content-between">
              <h6 class="ncv-card-title mb-0"><i class="bi bi-layout-text-sidebar me-2" style="color:var(--ncv-blue-500);"></i>Form Fields</h6>
              <div class="d-flex gap-1 flex-wrap">
                @foreach(['text'=>'Text','email'=>'Email','tel'=>'Phone','textarea'=>'Textarea','select'=>'Dropdown','checkbox'=>'Checkbox'] as $ft=>$fl)
                <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="addField('{{ $ft }}')"
                        style="font-size:.72rem;border:1px solid var(--border-color);">
                  <i class="bi bi-plus-lg"></i> {{ $fl }}
                </button>
                @endforeach
              </div>
            </div>
            <div class="ncv-card-body">
              <div id="fieldsContainer">
                @php $existingFields = old('fields', isset($webForm) ? ($webForm->fields ?? []) : []); @endphp
                @if(empty($existingFields))
                  {{-- Default starter fields --}}
                  @php
                    $existingFields = [
                      ['type'=>'text',  'label'=>'First Name', 'name'=>'first_name', 'required'=>true,  'placeholder'=>''],
                      ['type'=>'text',  'label'=>'Last Name',  'name'=>'last_name',  'required'=>true,  'placeholder'=>''],
                      ['type'=>'email', 'label'=>'Email',      'name'=>'email',      'required'=>true,  'placeholder'=>''],
                      ['type'=>'tel',   'label'=>'Phone',      'name'=>'phone',      'required'=>false, 'placeholder'=>''],
                      ['type'=>'text',  'label'=>'Company',    'name'=>'company',    'required'=>false, 'placeholder'=>''],
                    ];
                  @endphp
                @endif
                @foreach($existingFields as $i => $field)
                  @include('marketing.web-forms._field', ['field'=>$field, 'index'=>$i])
                @endforeach
              </div>
              <p style="font-size:.75rem;color:var(--text-muted);margin-top:.75rem;margin-bottom:0;">
                <i class="bi bi-info-circle me-1"></i>
                The fields <code>first_name</code>, <code>last_name</code>, <code>email</code> are used for automatic lead conversion.
              </p>
            </div>
          </div>
        </div>

        {{-- Lead Routing --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-signpost-split me-2" style="color:var(--ncv-blue-500);"></i>Lead Routing</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-5">
                  <label class="ncv-label" for="assign_to_user_id">Assign Leads To</label>
                  <select class="ncv-select" id="assign_to_user_id" name="assign_to_user_id">
                    <option value="">— Round Robin / Auto —</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}" {{ old('assign_to_user_id', $webForm->assign_to_user_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end pb-1">
                  <label class="d-flex align-items-center gap-2 cursor-pointer">
                    <input type="hidden" name="assign_by_geography" value="0" />
                    <input type="checkbox" name="assign_by_geography" value="1"
                           {{ old('assign_by_geography', $webForm->assign_by_geography ?? false) ? 'checked' : '' }}
                           style="accent-color:var(--ncv-blue-500);width:16px;height:16px;" />
                    <div>
                      <span class="ncv-label mb-0">Geography-based routing</span>
                      <div style="font-size:.72rem;color:var(--text-muted);">Auto-assign by lead's country/state</div>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('marketing.web-forms.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($webForm) ? 'Update Form' : 'Create Form' }}
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
  let fieldIndex = {{ count($existingFields ?? []) }};

  function addField(type) {
    const container = document.getElementById('fieldsContainer');
    const label = type.charAt(0).toUpperCase() + type.slice(1) + ' Field';
    const name  = type + '_' + fieldIndex;
    const html  = `
      <div class="field-card mb-2" id="field_${fieldIndex}">
        <div class="d-flex align-items-start gap-2">
          <span class="drag-handle mt-1"><i class="bi bi-grip-vertical"></i></span>
          <div class="row g-2 flex-grow-1">
            <div class="col-12 col-md-3">
              <label class="ncv-label" style="font-size:.72rem;">Label</label>
              <input type="text" name="fields[${fieldIndex}][label]" class="ncv-input" value="${label}" placeholder="Field label" />
            </div>
            <div class="col-12 col-md-3">
              <label class="ncv-label" style="font-size:.72rem;">Field Name (key)</label>
              <input type="text" name="fields[${fieldIndex}][name]" class="ncv-input" value="${name}" placeholder="field_name" />
            </div>
            <div class="col-12 col-md-2">
              <label class="ncv-label" style="font-size:.72rem;">Type</label>
              <input type="text" name="fields[${fieldIndex}][type]" class="ncv-input" value="${type}" readonly />
            </div>
            <div class="col-12 col-md-3">
              <label class="ncv-label" style="font-size:.72rem;">Placeholder</label>
              <input type="text" name="fields[${fieldIndex}][placeholder]" class="ncv-input" placeholder="Optional…" />
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end pb-1">
              <label class="d-flex align-items-center gap-1" style="cursor:pointer;">
                <input type="checkbox" name="fields[${fieldIndex}][required]" value="1" style="accent-color:var(--ncv-blue-500);" />
                <span style="font-size:.72rem;color:var(--text-muted);">Req.</span>
              </label>
            </div>
          </div>
          <button type="button" onclick="removeField(${fieldIndex})" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm mt-1" style="color:#ef4444;">
            <i class="bi bi-x-lg" style="font-size:.75rem;"></i>
          </button>
        </div>
      </div>`;
    container.insertAdjacentHTML('beforeend', html);
    fieldIndex++;
  }

  function removeField(i) {
    document.getElementById('field_' + i)?.remove();
  }
</script>
@endpush
