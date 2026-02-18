@extends('layouts.app')

@section('title', isset($emailTemplate) ? 'Edit Template' : 'New Email Template')
@section('page-title', isset($emailTemplate) ? 'Edit Template' : 'New Email Template')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.email-templates.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Templates</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .template-toolbar { display:flex; gap:.375rem; flex-wrap:wrap; padding:.625rem; background:#f8fafc; border-bottom:1px solid var(--border-color); border-radius:.625rem .625rem 0 0; }
  .toolbar-btn { padding:.3rem .55rem; border:1px solid var(--border-color); border-radius:.375rem; background:#fff; cursor:pointer; font-size:.78rem; color:var(--text-secondary); transition:all .15s; }
  .toolbar-btn:hover { background:var(--ncv-blue-50); border-color:var(--ncv-blue-300); color:var(--ncv-blue-600); }
  #bodyEditor { min-height:400px; border:none; border-radius:0 0 .625rem .625rem; padding:1rem; font-family:monospace; font-size:.83rem; resize:vertical; width:100%; outline:none; }
  .preview-pane { min-height:400px; border:1px solid var(--border-color); border-radius:.625rem; padding:1.5rem; background:#fafbfc; overflow:auto; }
</style>
@endpush

@section('content')

<div class="row justify-content-center">
  <div class="col-12 col-xl-11">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h1 class="ncv-page-title">{{ isset($emailTemplate) ? 'Edit Email Template' : 'New Email Template' }}</h1>
        <p class="ncv-page-subtitle">Build an HTML email template for use in campaigns.</p>
      </div>
      <a href="{{ route('marketing.email-templates.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <form method="POST"
          action="{{ isset($emailTemplate) ? route('marketing.email-templates.update', $emailTemplate) : route('marketing.email-templates.store') }}">
      @csrf
      @if(isset($emailTemplate)) @method('PUT') @endif

      <div class="row g-3">

        {{-- Name & Subject --}}
        <div class="col-12">
          <div class="ncv-card">
            <div class="ncv-card-header">
              <h6 class="ncv-card-title"><i class="bi bi-card-text me-2" style="color:var(--ncv-blue-500);"></i>Template Info</h6>
            </div>
            <div class="ncv-card-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="ncv-label" for="name">Template Name <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('name') is-invalid @enderror"
                         id="name" name="name"
                         value="{{ old('name', $emailTemplate->name ?? '') }}"
                         placeholder="e.g. Welcome Email – Onboarding" required />
                  @error('name')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-5">
                  <label class="ncv-label" for="subject">Default Subject Line <span class="required">*</span></label>
                  <input type="text" class="ncv-input @error('subject') is-invalid @enderror"
                         id="subject" name="subject"
                         value="{{ old('subject', $emailTemplate->subject ?? '') }}"
                         placeholder="e.g. Welcome to NavCRM, {{first_name}}!" required />
                  @error('subject')<span class="ncv-form-error">{{ $message }}</span>@enderror
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                  <label class="d-flex align-items-center gap-2 cursor-pointer" style="white-space:nowrap;">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $emailTemplate->is_active ?? true) ? 'checked' : '' }}
                           style="accent-color:var(--ncv-blue-500);width:16px;height:16px;" />
                    <span class="ncv-label mb-0" style="cursor:pointer;">Active</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Builder --}}
        <div class="col-12 col-lg-7">
          <div class="ncv-card" style="overflow:hidden;">
            <div class="ncv-card-header d-flex align-items-center justify-content-between">
              <h6 class="ncv-card-title mb-0"><i class="bi bi-code-slash me-2" style="color:var(--ncv-blue-500);"></i>HTML Editor</h6>
              <div class="d-flex gap-1">
                <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="setView('code')" id="btnCode">
                  <i class="bi bi-code-slash"></i> Code
                </button>
                <button type="button" class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="setView('preview')" id="btnPreview">
                  <i class="bi bi-eye"></i> Preview
                </button>
              </div>
            </div>
            <div id="codeView">
              <div class="template-toolbar">
                @foreach([
                  ['cmd'=>'bold',          'icon'=>'bi-type-bold',      'label'=>'B'],
                  ['cmd'=>'italic',        'icon'=>'bi-type-italic',    'label'=>'I'],
                  ['cmd'=>'underline',     'icon'=>'bi-type-underline', 'label'=>'U'],
                  ['cmd'=>'insertOrderedList',   'icon'=>'bi-list-ol',  'label'=>'OL'],
                  ['cmd'=>'insertUnorderedList', 'icon'=>'bi-list-ul',  'label'=>'UL'],
                  ['cmd'=>'createLink',    'icon'=>'bi-link-45deg',     'label'=>'Link'],
                ] as $btn)
                <button type="button" class="toolbar-btn" onclick="execCmd('{{ $btn['cmd'] }}')"
                        title="{{ $btn['label'] }}">
                  <i class="bi {{ $btn['icon'] }}"></i>
                </button>
                @endforeach
                <button type="button" class="toolbar-btn" onclick="insertSnippet('&lt;h2 style=&quot;color:#1e40af;&quot;&gt;Heading&lt;/h2&gt;')">H2</button>
                <button type="button" class="toolbar-btn" onclick="insertSnippet('&lt;p&gt;Paragraph text here.&lt;/p&gt;')">P</button>
                <button type="button" class="toolbar-btn" onclick="insertSnippet('&lt;a href=&quot;#&quot; style=&quot;background:#2563eb;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;&quot;&gt;CTA Button&lt;/a&gt;')">
                  Button
                </button>
                <button type="button" class="toolbar-btn" onclick="insertSnippet('{{ \'{{\' }}first_name{{ \'}}\' }}')">
                  <i class="bi bi-person"></i> Name
                </button>
              </div>
              <textarea id="bodyEditor" name="body"
                        class="@error('body') is-invalid @enderror"
                        placeholder="Paste or write your HTML email here…">{{ old('body', $emailTemplate->body ?? '') }}</textarea>
            </div>
            <div id="previewView" class="preview-pane" style="display:none;"></div>
            @error('body')<span class="ncv-form-error px-3 pb-2">{{ $message }}</span>@enderror
          </div>
        </div>

        {{-- Merge Tags & Tips --}}
        <div class="col-12 col-lg-5">
          <div class="ncv-card mb-3">
            <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-braces me-2" style="color:var(--ncv-blue-500);"></i>Merge Tags</h6></div>
            <div class="ncv-card-body">
              <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">Click to copy and paste into the editor.</p>
              @foreach([
                ['tag'=>'{{first_name}}',   'desc'=>'Contact first name'],
                ['tag'=>'{{last_name}}',    'desc'=>'Contact last name'],
                ['tag'=>'{{email}}',        'desc'=>'Contact email'],
                ['tag'=>'{{company}}',      'desc'=>'Company name'],
                ['tag'=>'{{unsubscribe_url}}','desc'=>'Unsubscribe link (required)'],
              ] as $mt)
              <div class="d-flex align-items-center justify-content-between mb-2">
                <button type="button"
                        onclick="copyTag('{{ $mt['tag'] }}')"
                        class="ncv-btn ncv-btn-ghost ncv-btn-sm"
                        style="font-family:monospace;font-size:.75rem;border:1px solid var(--border-color);padding:.25rem .5rem;">
                  {{ $mt['tag'] }}
                </button>
                <span style="font-size:.73rem;color:var(--text-muted);">{{ $mt['desc'] }}</span>
              </div>
              @endforeach
            </div>
          </div>
          <div class="ncv-card">
            <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-lightbulb me-2" style="color:#f59e0b;"></i>Tips</h6></div>
            <div class="ncv-card-body">
              <ul style="font-size:.78rem;color:var(--text-muted);margin:0;padding-left:1.25rem;line-height:1.7;">
                <li>Use inline CSS for best email client compatibility.</li>
                <li>Always include an <code>{{unsubscribe_url}}</code> link.</li>
                <li>Keep width ≤ 600px for mobile-friendly layouts.</li>
                <li>Test in multiple email clients before sending.</li>
              </ul>
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
          <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('marketing.email-templates.index') }}" class="ncv-btn ncv-btn-outline">Cancel</a>
            <button type="submit" class="ncv-btn ncv-btn-primary">
              <i class="bi bi-check-lg"></i> {{ isset($emailTemplate) ? 'Update Template' : 'Save Template' }}
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
  function setView(v) {
    const code    = document.getElementById('codeView');
    const preview = document.getElementById('previewView');
    const btnC    = document.getElementById('btnCode');
    const btnP    = document.getElementById('btnPreview');

    if (v === 'code') {
      code.style.display    = 'block';
      preview.style.display = 'none';
      btnC.style.background = 'var(--ncv-blue-50)';
      btnC.style.color      = 'var(--ncv-blue-600)';
      btnP.style.background = '';
      btnP.style.color      = '';
    } else {
      code.style.display    = 'none';
      preview.style.display = 'block';
      preview.innerHTML     = document.getElementById('bodyEditor').value;
      btnP.style.background = 'var(--ncv-blue-50)';
      btnP.style.color      = 'var(--ncv-blue-600)';
      btnC.style.background = '';
      btnC.style.color      = '';
    }
  }

  function execCmd(cmd) {
    const ta = document.getElementById('bodyEditor');
    const start = ta.selectionStart, end = ta.selectionEnd;
    const sel = ta.value.substring(start, end);
    let result = sel;
    if (cmd === 'bold')      result = '<strong>' + sel + '</strong>';
    if (cmd === 'italic')    result = '<em>' + sel + '</em>';
    if (cmd === 'underline') result = '<u>' + sel + '</u>';
    if (cmd === 'createLink') {
      const url = prompt('Enter URL:', 'https://');
      if (url) result = '<a href="' + url + '">' + (sel || url) + '</a>';
    }
    ta.value = ta.value.substring(0, start) + result + ta.value.substring(end);
    ta.focus();
  }

  function insertSnippet(html) {
    const ta = document.getElementById('bodyEditor');
    const pos = ta.selectionStart;
    ta.value = ta.value.substring(0, pos) + html + ta.value.substring(pos);
    ta.focus();
  }

  function copyTag(tag) {
    navigator.clipboard?.writeText(tag);
    insertSnippet(tag);
  }

  setView('code');
</script>
@endpush
