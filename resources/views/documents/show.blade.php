@extends('layouts.app')

@section('title', $document->document_number)
@section('page-title', $document->document_number)

@section('breadcrumb-items')
  <a href="{{ route('documents.index') }}" style="color:inherit;text-decoration:none;">Documents</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $document->document_number }}</span>
@endsection

@section('content')
<div class="row g-3">

  {{-- Left: document body + history --}}
  <div class="col-12 col-xl-8">

    {{-- Header card --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <div>
          <h6 class="ncv-card-title mb-0">{{ $document->title }}</h6>
          <div style="font-size:.75rem;color:var(--text-muted);margin-top:.15rem;">
            {{ $document->document_number }} &nbsp;·&nbsp;
            <span class="ncv-badge bg-{{ $document->status->color() }}-subtle text-{{ $document->status->color() }}">{{ $document->status->label() }}</span>
            &nbsp;·&nbsp;
            <span class="ncv-badge" style="background:#eff6ff;color:#1d4ed8;">{{ $document->type->label() }}</span>
          </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('documents.edit', $document) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-pencil"></i> Edit</a>
          <a href="{{ route('documents.send', $document) }}" class="ncv-btn ncv-btn-primary ncv-btn-sm"><i class="bi bi-send"></i> Send for Signing</a>
          <a href="{{ route('documents.download', $document) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        </div>
      </div>
      <div class="ncv-card-body">
        <div class="row g-3" style="font-size:.82rem;">
          @if($document->account)
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Account</div>
            <a href="{{ route('accounts.show', $document->account) }}" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $document->account->name }}</a>
          </div>
          @endif
          @if($document->contact)
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Contact</div>
            <a href="{{ route('contacts.show', $document->contact) }}" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $document->contact->first_name }} {{ $document->contact->last_name }}</a>
          </div>
          @endif
          @if($document->opportunity)
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Opportunity</div>
            <a href="{{ route('opportunities.show', $document->opportunity) }}" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $document->opportunity->name }}</a>
          </div>
          @endif
          @if($document->expires_at)
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Expires</div>
            <span style="color:{{ $document->expires_at->isPast() ? '#ef4444' : 'var(--text-secondary)' }};">{{ $document->expires_at->format('M j, Y') }}</span>
          </div>
          @endif
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Created</div>
            <span style="color:var(--text-secondary);">{{ $document->created_at->format('M j, Y') }}</span>
          </div>
          @if($document->sent_at)
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Sent</div>
            <span style="color:var(--text-secondary);">{{ $document->sent_at->format('M j, Y') }}</span>
          </div>
          @endif
          @if($document->template)
          <div class="col-6 col-md-3">
            <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Template</div>
            <a href="{{ route('document-templates.show', $document->template) }}" style="color:var(--ncv-blue-600);text-decoration:none;">{{ $document->template->name }}</a>
          </div>
          @endif
        </div>
        @if($document->notes)
        <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
          <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Notes</div>
          <p style="font-size:.85rem;color:var(--text-secondary);margin:0;">{{ $document->notes }}</p>
        </div>
        @endif
      </div>
    </div>

    {{-- Tabs: Document | History --}}
    <ul class="nav nav-tabs mb-3" id="docTabs" style="border-bottom:1px solid var(--border-color);">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-body" type="button">
          <i class="bi bi-file-text me-1"></i> Document
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history" type="button">
          <i class="bi bi-clock-history me-1"></i> Version History
          @if($document->versions->count() > 0)
            <span class="ncv-badge ms-1" style="background:#f1f5f9;color:#64748b;">{{ $document->versions->count() }}</span>
          @endif
        </button>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-body">
        <div class="ncv-card">
          <div class="ncv-card-body">
            <div style="border:1px solid var(--border-color);border-radius:.5rem;padding:2rem;background:#fff;font-size:.9rem;line-height:1.8;min-height:300px;">
              {!! $document->body !!}
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-history">
        <div class="ncv-card">
          <div class="ncv-card-body p-0">
            @forelse($document->versions as $version)
            <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border-color);">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span style="font-weight:700;font-size:.85rem;">Version {{ $version->version_number }}</span>
                <span style="font-size:.75rem;color:var(--text-muted);">
                  {{ $version->savedBy?->name ?? 'System' }} &nbsp;·&nbsp; {{ $version->created_at?->format('M j, Y H:i') }}
                </span>
              </div>
              <details>
                <summary style="font-size:.78rem;color:var(--ncv-blue-600);cursor:pointer;">Show snapshot</summary>
                <div style="margin-top:.5rem;padding:1rem;border:1px solid var(--border-color);border-radius:.375rem;background:#f9fafb;font-size:.82rem;line-height:1.6;max-height:300px;overflow-y:auto;">
                  {!! $version->body !!}
                </div>
              </details>
            </div>
            @empty
            <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
              <i class="bi bi-clock-history" style="font-size:1.5rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
              No version history yet.<br>
              <span style="font-size:.78rem;">Versions are saved when a sent/signed document is edited.</span>
            </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    {{-- Delete --}}
    <div class="mt-3">
      <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Delete this document?')">
        @csrf @method('DELETE')
        <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash"></i> Delete Document</button>
      </form>
    </div>

  </div>

  {{-- Right: Signatories --}}
  <div class="col-12 col-xl-4">
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-pen me-2" style="color:var(--ncv-blue-500);"></i>Signatories</h6>
        <a href="{{ route('documents.send', $document) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> Add
        </a>
      </div>
      <div class="ncv-card-body p-0">
        @forelse($document->signatories as $signatory)
        <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border-color);">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div style="font-weight:600;font-size:.85rem;">{{ $signatory->name }}</div>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $signatory->email }}</div>
              @if($signatory->signed_at)
              <div style="font-size:.72rem;color:#16a34a;margin-top:.2rem;"><i class="bi bi-check-circle-fill me-1"></i>Signed {{ $signatory->signed_at->format('M j, Y H:i') }}</div>
              @elseif($signatory->viewed_at)
              <div style="font-size:.72rem;color:#2563eb;margin-top:.2rem;"><i class="bi bi-eye-fill me-1"></i>Viewed {{ $signatory->viewed_at->format('M j, Y H:i') }}</div>
              @endif
            </div>
            @php
              $sigBadge = match($signatory->status) {
                'signed'   => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => 'Signed'],
                'viewed'   => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'label' => 'Viewed'],
                'rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Rejected'],
                default    => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'Pending'],
              };
            @endphp
            <span class="ncv-badge" style="background:{{ $sigBadge['bg'] }};color:{{ $sigBadge['color'] }};flex-shrink:0;">{{ $sigBadge['label'] }}</span>
          </div>
          @if($signatory->status === 'pending' || $signatory->status === 'viewed')
          <div class="mt-2">
            <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem;">Signing link:</div>
            <div class="d-flex gap-1 align-items-center">
              <input type="text" readonly value="{{ route('signing.show', $signatory->sign_token) }}"
                     style="font-size:.72rem;padding:.25rem .5rem;border:1px solid var(--border-color);border-radius:.375rem;background:#f8fafc;flex:1;min-width:0;color:var(--text-muted);">
              <button type="button" onclick="navigator.clipboard.writeText('{{ route('signing.show', $signatory->sign_token) }}').then(()=>this.innerHTML='<i class=\'bi bi-check-lg\'></i>')"
                      class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Copy link">
                <i class="bi bi-copy" style="font-size:.8rem;"></i>
              </button>
            </div>
          </div>
          @endif
          @if($signatory->signature_data)
          <div class="mt-2">
            <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem;">Signature:</div>
            <img src="{{ $signatory->signature_data }}" alt="Signature" style="max-width:160px;border:1px solid var(--border-color);border-radius:.375rem;background:#fff;">
          </div>
          @endif
        </div>
        @empty
        <div class="text-center py-4" style="color:var(--text-muted);font-size:.85rem;">
          <i class="bi bi-pen" style="font-size:1.5rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
          No signatories yet
          <div class="mt-2">
            <a href="{{ route('documents.send', $document) }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
              <i class="bi bi-send"></i> Send for Signing
            </a>
          </div>
        </div>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection
