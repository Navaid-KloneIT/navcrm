@extends('layouts.app')

@section('title', isset($contact) ? 'Edit Contact' : 'New Contact')
@section('page-title', isset($contact) ? 'Edit Contact' : 'New Contact')

@section('breadcrumb-items')
  <a href="{{ route('contacts.index') }}" style="color:inherit;text-decoration:none;">Contacts</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-10">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($contact) ? 'Edit Contact' : 'New Contact' }}</h1>
        <p class="ncv-page-subtitle">{{ isset($contact) ? 'Update contact information.' : 'Add a new contact to your CRM.' }}</p>
      </div>
      <a href="{{ route('contacts.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($contact) ? route('contacts.update', $contact->id) : route('contacts.store') }}"
          id="contactForm">
      @csrf
      @if(isset($contact)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Personal Information --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-person me-2" style="color:var(--ncv-blue-500);"></i>Personal Information</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" class="ncv-input @error('first_name') is-invalid @enderror"
                           id="first_name" name="first_name"
                           value="{{ old('first_name', $contact->first_name ?? '') }}"
                           placeholder="Jane" required />
                    @error('first_name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" class="ncv-input @error('last_name') is-invalid @enderror"
                           id="last_name" name="last_name"
                           value="{{ old('last_name', $contact->last_name ?? '') }}"
                           placeholder="Smith" required />
                    @error('last_name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="title">Job Title</label>
                    <input type="text" class="ncv-input" id="title" name="title"
                           value="{{ old('title', $contact->title ?? '') }}"
                           placeholder="VP of Sales" />
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="department">Department</label>
                    <select class="ncv-select" id="department" name="department">
                      <option value="">— Select Department —</option>
                      @foreach(['Sales','Marketing','Engineering','Finance','HR','Operations','Legal','Executive','Other'] as $dept)
                      <option value="{{ $dept }}" {{ old('department', $contact->department ?? '') == $dept ? 'selected' : '' }}>
                        {{ $dept }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="account_id">Account (Company)</label>
                    <select class="ncv-select" id="account_id" name="account_id">
                      <option value="">— No Account —</option>
                      <option value="1" {{ old('account_id', $contact->account_id ?? '') == 1 ? 'selected' : '' }}>Acme Corporation</option>
                      <option value="2" {{ old('account_id', $contact->account_id ?? '') == 2 ? 'selected' : '' }}>TechStart Inc</option>
                      <option value="3" {{ old('account_id', $contact->account_id ?? '') == 3 ? 'selected' : '' }}>Globex Inc</option>
                    </select>
                    <span class="ncv-form-hint">Or <a href="{{ route('accounts.create') }}" style="color:var(--ncv-blue-600);">create a new account</a></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Contact Details --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-telephone me-2" style="color:var(--ncv-blue-500);"></i>Contact Details</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="email">Email Address <span class="required">*</span></label>
                    <div class="ncv-input-group">
                      <i class="bi bi-envelope ncv-input-icon"></i>
                      <input type="email" class="ncv-input @error('email') is-invalid @enderror"
                             id="email" name="email"
                             value="{{ old('email', $contact->email ?? '') }}"
                             placeholder="jane@company.com" required />
                    </div>
                    @error('email')<span class="ncv-form-error">{{ $message }}</span>@enderror
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="phone">Work Phone</label>
                    <div class="ncv-input-group">
                      <i class="bi bi-telephone ncv-input-icon"></i>
                      <input type="tel" class="ncv-input" id="phone" name="phone"
                             value="{{ old('phone', $contact->phone ?? '') }}"
                             placeholder="+1 (555) 000-0000" />
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="mobile">Mobile</label>
                    <div class="ncv-input-group">
                      <i class="bi bi-phone ncv-input-icon"></i>
                      <input type="tel" class="ncv-input" id="mobile" name="mobile"
                             value="{{ old('mobile', $contact->mobile ?? '') }}"
                             placeholder="+1 (555) 000-0000" />
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="linkedin">LinkedIn URL</label>
                    <div class="ncv-input-group">
                      <i class="bi bi-linkedin ncv-input-icon"></i>
                      <input type="url" class="ncv-input" id="linkedin" name="linkedin"
                             value="{{ old('linkedin', $contact->linkedin ?? '') }}"
                             placeholder="https://linkedin.com/in/username" />
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="ncv-form-group">
                    <label class="ncv-label" for="twitter">Twitter/X Handle</label>
                    <div class="ncv-input-group">
                      <i class="bi bi-twitter-x ncv-input-icon"></i>
                      <input type="text" class="ncv-input" id="twitter" name="twitter"
                             value="{{ old('twitter', $contact->twitter ?? '') }}"
                             placeholder="@username" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Address --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-geo-alt me-2" style="color:var(--ncv-blue-500);"></i>Address</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="ncv-label" for="street">Street Address</label>
                  <input type="text" class="ncv-input" id="street" name="street"
                         value="{{ old('street', $contact->street ?? '') }}"
                         placeholder="123 Main Street, Suite 400" />
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="city">City</label>
                  <input type="text" class="ncv-input" id="city" name="city"
                         value="{{ old('city', $contact->city ?? '') }}" placeholder="New York" />
                </div>
                <div class="col-12 col-md-4">
                  <label class="ncv-label" for="state">State / Province</label>
                  <input type="text" class="ncv-input" id="state" name="state"
                         value="{{ old('state', $contact->state ?? '') }}" placeholder="NY" />
                </div>
                <div class="col-12 col-md-2">
                  <label class="ncv-label" for="zip">ZIP / Postal</label>
                  <input type="text" class="ncv-input" id="zip" name="zip"
                         value="{{ old('zip', $contact->zip ?? '') }}" placeholder="10001" />
                </div>
                <div class="col-12 col-md-2">
                  <label class="ncv-label" for="country">Country</label>
                  <select class="ncv-select" id="country" name="country">
                    <option value="US" selected>United States</option>
                    <option value="CA">Canada</option>
                    <option value="GB">United Kingdom</option>
                    <option value="AU">Australia</option>
                    <option value="DE">Germany</option>
                    <option value="FR">France</option>
                    <option value="IN">India</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Tags & Notes --}}
        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-tags me-2" style="color:var(--ncv-blue-500);"></i>Tags</h6>
            </div>
            <div class="ncv-card-body">
              <label class="ncv-label">Add Tags</label>
              <div id="tagBadges" style="display:flex;gap:.4rem;flex-wrap:wrap;min-height:32px;margin-bottom:.625rem;">
                {{-- Tags will be rendered here --}}
              </div>
              <div style="display:flex;gap:.5rem;">
                <input type="text" class="ncv-input" id="tagInput" placeholder="Type and press Enter"
                       style="height:36px;font-size:.82rem;" />
                <button type="button" class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="addFormTag()">Add</button>
              </div>
              <input type="hidden" name="tags" id="tagsHidden" value="" />
              <div style="margin-top:.75rem;">
                <p class="ncv-form-hint">Suggestions:</p>
                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                  @foreach(['VIP','Hot Lead','Customer','Prospect','Partner','Decision Maker','Renewal'] as $suggest)
                  <button type="button" class="ncv-chip" onclick="quickAddTag('{{ $suggest }}')" style="font-size:.72rem;padding:.25rem .625rem;">
                    {{ $suggest }}
                  </button>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="ncv-card h-100">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-sticky me-2" style="color:var(--ncv-blue-500);"></i>Initial Note</h6>
            </div>
            <div class="ncv-card-body">
              <label class="ncv-label" for="note">Note (optional)</label>
              <textarea class="ncv-textarea" id="note" name="note" rows="5"
                        placeholder="Add a note about how you met, key details, or next steps…">{{ old('note', '') }}</textarea>
            </div>
          </div>
        </div>

        {{-- Action Buttons --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('contacts.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" name="action" value="save_and_new" class="ncv-btn ncv-btn-outline">
              <i class="bi bi-plus-circle"></i> Save & Add Another
            </button>
            <button type="submit" name="action" value="save" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($contact) ? 'Update Contact' : 'Create Contact' }}
            </button>
          </div>
        </div>

      </div>{{-- end row --}}
    </form>

  </div>
</div>

@endsection

@push('scripts')
<script>
  let formTags = [];

  function renderFormTags() {
    const container = document.getElementById('tagBadges');
    container.innerHTML = formTags.map(t =>
      `<span class="ncv-badge ncv-badge-primary" style="cursor:default;">${t}
        <button type="button" onclick="removeFormTag('${t}')" style="background:none;border:none;padding:0;margin-left:3px;color:inherit;opacity:.7;cursor:pointer;font-size:.85rem;">&times;</button>
      </span>`
    ).join('');
    document.getElementById('tagsHidden').value = formTags.join(',');
  }

  function addFormTag() {
    const val = document.getElementById('tagInput').value.trim();
    if (val && !formTags.includes(val)) {
      formTags.push(val);
      renderFormTags();
    }
    document.getElementById('tagInput').value = '';
  }

  function quickAddTag(val) {
    if (!formTags.includes(val)) {
      formTags.push(val);
      renderFormTags();
    }
  }

  function removeFormTag(tag) {
    formTags = formTags.filter(t => t !== tag);
    renderFormTags();
  }

  document.getElementById('tagInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); addFormTag(); }
  });
</script>
@endpush
