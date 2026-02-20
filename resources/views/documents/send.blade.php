@extends('layouts.app')

@section('title', 'Send for Signing')
@section('page-title', 'Send for Signing')

@section('breadcrumb-items')
  <a href="{{ route('documents.index') }}" style="color:inherit;text-decoration:none;">Documents</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('documents.show', $document) }}" style="color:inherit;text-decoration:none;">{{ $document->document_number }}</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Send for Signing</span>
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-xl-6">

    {{-- Document Info --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
        <div class="d-flex align-items-center gap-3">
          <div style="width:40px;height:40px;border-radius:.625rem;background:#eff6ff;color:#1d4ed8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-file-earmark-text"></i>
          </div>
          <div>
            <div style="font-weight:700;font-size:.9rem;">{{ $document->title }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);">
              {{ $document->document_number }} &nbsp;·&nbsp; {{ $document->type->label() }}
              &nbsp;·&nbsp; <span class="ncv-badge bg-{{ $document->status->color() }}-subtle text-{{ $document->status->color() }}">{{ $document->status->label() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Add Signatory Form --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-person-plus me-2" style="color:var(--ncv-blue-500);"></i>Add Signatory</h6>
      </div>
      <div class="ncv-card-body">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-start gap-2 mb-3" style="font-size:.85rem;">
          <i class="bi bi-check-circle-fill mt-1" style="flex-shrink:0;"></i>
          <div>
            {{ session('success') }}
            <div class="mt-1 text-muted" style="font-size:.78rem;">Share the link above with the signatory to collect their signature.</div>
          </div>
        </div>
        @endif

        <form method="POST" action="{{ route('documents.send.store', $document) }}">
          @csrf
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="ncv-label">Signatory Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="ncv-input @error('name') is-invalid @enderror"
                     value="{{ old('name') }}" placeholder="Full name" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="ncv-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="ncv-input @error('email') is-invalid @enderror"
                     value="{{ old('email') }}" placeholder="signatory@company.com" required>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
          <button type="submit" class="ncv-btn ncv-btn-primary">
            <i class="bi bi-send"></i> Generate Signing Link
          </button>
        </form>
      </div>
    </div>

    {{-- Existing Signatories --}}
    @if($document->signatories->count() > 0)
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-people me-2" style="color:var(--ncv-blue-500);"></i>Existing Signatories</h6>
      </div>
      <div class="ncv-card-body p-0">
        @foreach($document->signatories as $signatory)
        <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border-color);">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div>
              <div style="font-weight:600;font-size:.85rem;">{{ $signatory->name }}</div>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $signatory->email }}</div>
            </div>
            @php
              $sigBadge = match($signatory->status) {
                'signed'   => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => 'Signed'],
                'viewed'   => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'label' => 'Viewed'],
                'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Rejected'],
                default    => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'Pending'],
              };
            @endphp
            <span class="ncv-badge" style="background:{{ $sigBadge['bg'] }};color:{{ $sigBadge['color'] }};">{{ $sigBadge['label'] }}</span>
          </div>
          @if(in_array($signatory->status, ['pending', 'viewed']))
          <div class="d-flex gap-1 align-items-center">
            <input type="text" readonly value="{{ route('signing.show', $signatory->sign_token) }}"
                   style="font-size:.72rem;padding:.3rem .5rem;border:1px solid var(--border-color);border-radius:.375rem;background:#f8fafc;flex:1;min-width:0;color:var(--text-muted);">
            <button type="button"
                    onclick="navigator.clipboard.writeText('{{ route('signing.show', $signatory->sign_token) }}').then(()=>{ this.innerHTML='<i class=\'bi bi-check-lg\'></i>'; setTimeout(()=>this.innerHTML='<i class=\'bi bi-copy\'></i>',2000) })"
                    class="ncv-btn ncv-btn-outline ncv-btn-sm" title="Copy link">
              <i class="bi bi-copy"></i> Copy
            </button>
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @endif

    <div class="d-flex gap-2">
      <a href="{{ route('documents.show', $document) }}" class="ncv-btn ncv-btn-ghost"><i class="bi bi-arrow-left"></i> Back to Document</a>
    </div>

  </div>
</div>
@endsection
