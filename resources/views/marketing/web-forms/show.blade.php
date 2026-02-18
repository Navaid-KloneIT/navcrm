@extends('layouts.app')

@section('title', $webForm->name)
@section('page-title', $webForm->name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.web-forms.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Web Forms</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="ncv-page-title mb-0">{{ $webForm->name }}</h1>
      @if($webForm->is_active)
        <span class="ncv-badge ncv-badge-success"><span class="dot"></span>Active</span>
      @else
        <span class="ncv-badge ncv-badge-muted">Inactive</span>
      @endif
    </div>
    @if($webForm->description)
      <p class="ncv-page-subtitle">{{ $webForm->description }}</p>
    @endif
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('marketing.web-forms.edit', $webForm) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <form method="POST" action="{{ route('marketing.web-forms.destroy', $webForm) }}"
          onsubmit="return confirm('Delete this form and all submissions?')">
      @csrf @method('DELETE')
      <button class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:.625rem;">
        <i class="bi bi-trash"></i> Delete
      </button>
    </form>
  </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total Submissions', 'value'=>number_format($webForm->total_submissions),                    'color'=>'var(--ncv-blue-600)', 'icon'=>'bi-inbox'],
    ['label'=>'Converted to Leads','value'=>number_format($webForm->submissions()->where('is_converted',true)->count()), 'color'=>'#10b981', 'icon'=>'bi-lightning-charge'],
    ['label'=>'Form Fields',       'value'=>is_array($webForm->fields) ? count($webForm->fields) : 0,       'color'=>'#8b5cf6', 'icon'=>'bi-ui-checks'],
    ['label'=>'Lead Routing',      'value'=>$webForm->assignedUser?->name ?? ($webForm->assign_by_geography ? 'Geography' : 'Auto'), 'color'=>'#f59e0b', 'icon'=>'bi-signpost-split'],
  ] as $s)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:.9rem;"></i>
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);">{{ $s['label'] }}</div>
      </div>
      <div style="font-size:1.2rem;font-weight:800;color:{{ $s['color'] }};letter-spacing:-.02em;">{{ $s['value'] }}</div>
    </div>
  </div>
  @endforeach
</div>

<div class="row g-3">

  {{-- Form Preview --}}
  <div class="col-12 col-md-5">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-eye me-2" style="color:var(--ncv-blue-500);"></i>Form Preview</h6></div>
      <div class="ncv-card-body">
        @if(is_array($webForm->fields) && count($webForm->fields))
          @foreach($webForm->fields as $field)
          <div class="mb-3">
            <label class="ncv-label">
              {{ $field['label'] ?? ucfirst($field['name'] ?? 'Field') }}
              @if(!empty($field['required']))<span class="required">*</span>@endif
            </label>
            @if(($field['type'] ?? 'text') === 'textarea')
              <textarea class="ncv-textarea" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}" disabled></textarea>
            @elseif(($field['type'] ?? 'text') === 'select')
              <select class="ncv-select" disabled><option>— Select —</option></select>
            @elseif(($field['type'] ?? 'text') === 'checkbox')
              <label class="d-flex align-items-center gap-2"><input type="checkbox" disabled /> {{ $field['label'] ?? '' }}</label>
            @else
              <input type="{{ $field['type'] ?? 'text' }}" class="ncv-input"
                     placeholder="{{ $field['placeholder'] ?? '' }}" disabled />
            @endif
          </div>
          @endforeach
          <button type="button" class="ncv-btn ncv-btn-primary w-100" disabled>
            {{ $webForm->submit_button_text ?? 'Submit' }}
          </button>
        @else
          <p style="color:var(--text-muted);font-size:.85rem;">No fields configured.</p>
        @endif
      </div>
    </div>
  </div>

  {{-- Submissions --}}
  <div class="col-12 col-md-7">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-inbox me-2" style="color:var(--ncv-blue-500);"></i>Recent Submissions</h6>
      </div>
      <div class="ncv-card-body" style="padding:0;">
        @forelse($submissions as $sub)
        @php $data = $sub->data ?? []; @endphp
        <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--border-color);">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div style="font-weight:600;font-size:.85rem;">
                {{ trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) ?: 'Anonymous' }}
              </div>
              @if(!empty($data['email']))
                <div style="font-size:.775rem;color:var(--ncv-blue-600);">{{ $data['email'] }}</div>
              @endif
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.125rem;">{{ $sub->created_at->diffForHumans() }}</div>
            </div>
            <div class="d-flex align-items-center gap-1">
              @if($sub->is_converted)
                <span class="ncv-badge ncv-badge-success"><span class="dot"></span>Converted</span>
                @if($sub->lead)
                  <a href="{{ route('leads.show', $sub->lead) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm">
                    <i class="bi bi-arrow-right" style="font-size:.75rem;"></i>
                  </a>
                @endif
              @else
                <form method="POST" action="{{ route('marketing.web-forms.submissions.convert', $sub) }}">
                  @csrf
                  <button class="ncv-btn ncv-btn-sm" style="font-size:.72rem;padding:.3rem .625rem;background:#d1fae5;color:#065f46;border:none;border-radius:.5rem;">
                    <i class="bi bi-lightning-charge"></i> Convert
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:.85rem;">
          <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
          No submissions yet.
        </div>
        @endforelse
      </div>
      @if($submissions->hasPages())
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--border-color);">
          {{ $submissions->links() }}
        </div>
      @endif
    </div>
  </div>

</div>

@endsection
