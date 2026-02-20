@extends('layouts.app')

@section('title', 'Documents')
@section('page-title', 'Documents')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>Documents</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-3">
  @foreach([
    ['label'=>'Total Documents', 'value'=>$stats['total'],   'icon'=>'bi-file-earmark-text', 'color'=>'#6366f1'],
    ['label'=>'Sent',            'value'=>$stats['sent'],    'icon'=>'bi-send',               'color'=>'#3b82f6'],
    ['label'=>'Signed',          'value'=>$stats['signed'],  'icon'=>'bi-patch-check',        'color'=>'#10b981'],
    ['label'=>'Expired',         'value'=>$stats['expired'], 'icon'=>'bi-clock-history',      'color'=>'#f59e0b'],
  ] as $kpi)
  <div class="col-6 col-md-3">
    <div class="ncv-card h-100">
      <div class="ncv-card-body" style="padding:1rem 1.25rem;">
        <div class="d-flex align-items-center gap-3">
          <div style="width:40px;height:40px;border-radius:.625rem;background:{{ $kpi['color'] }}18;color:{{ $kpi['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
            <i class="bi {{ $kpi['icon'] }}"></i>
          </div>
          <div>
            <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);line-height:1;">{{ $kpi['value'] }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem;">{{ $kpi['label'] }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Toolbar --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex gap-2 flex-wrap flex-grow-1">
        <div class="ncv-input-group" style="max-width:260px;flex:1;">
          <i class="bi bi-search ncv-input-icon"></i>
          <input type="text" name="search" value="{{ request('search') }}" class="ncv-input ncv-input-search" placeholder="Search documents…">
        </div>
        <select name="status" class="ncv-select" style="width:140px;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          @foreach(\App\Enums\DocumentStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
          @endforeach
        </select>
        <select name="type" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Types</option>
          @foreach(\App\Enums\DocumentType::cases() as $t)
            <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
          @endforeach
        </select>
        <select name="account_id" class="ncv-select" style="width:160px;" onchange="this.form.submit()">
          <option value="">All Accounts</option>
          @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
          @endforeach
        </select>
        @if(request()->hasAny(['search','status','type','account_id']))
          <a href="{{ route('documents.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
        @endif
      </form>
      <a href="{{ route('documents.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
        <i class="bi bi-plus-lg"></i> New Document
      </a>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($documents->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-file-earmark-text" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No documents found</p>
        <a href="{{ route('documents.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New Document
        </a>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Document #</th>
            <th>Title</th>
            <th>Type</th>
            <th>Account</th>
            <th>Status</th>
            <th>Sent</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($documents as $document)
          <tr>
            <td>
              <a href="{{ route('documents.show', $document) }}" style="font-weight:700;color:var(--ncv-blue-600);text-decoration:none;font-size:.85rem;">
                {{ $document->document_number }}
              </a>
            </td>
            <td style="font-size:.85rem;font-weight:500;color:var(--text-primary);">{{ Str::limit($document->title, 50) }}</td>
            <td>
              <span class="ncv-badge" style="background:#eff6ff;color:#1d4ed8;font-size:.72rem;">{{ $document->type->label() }}</span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $document->account?->name ?? '—' }}</td>
            <td>
              <span class="ncv-badge bg-{{ $document->status->color() }}-subtle text-{{ $document->status->color() }}">{{ $document->status->label() }}</span>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $document->sent_at?->format('M j, Y') ?? '—' }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ route('documents.show', $document) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
                <a href="{{ route('documents.edit', $document) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
                <a href="{{ route('documents.download', $document) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Download PDF"><i class="bi bi-file-earmark-pdf" style="font-size:.8rem;"></i></a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($documents->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-top:1px solid var(--border-color);font-size:.82rem;">
    <span style="color:var(--text-muted);">Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }}</span>
    {{ $documents->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
