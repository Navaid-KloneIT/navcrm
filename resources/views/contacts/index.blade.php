@extends('layouts.app')

@section('title', 'Contacts')
@section('page-title', 'Contacts')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .contact-card { border-radius:.75rem; padding:1rem; cursor:pointer; transition:box-shadow .15s; }
  .contact-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Contacts</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">
      {{ number_format($stats['total']) }} total &middot; {{ number_format($stats['thisMonth']) }} added this month
    </p>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="toggleView('table')" id="btnTable" title="Table view">
      <i class="bi bi-table"></i>
    </button>
    <button class="ncv-btn ncv-btn-ghost ncv-btn-sm" onclick="toggleView('grid')" id="btnGrid" title="Grid view">
      <i class="bi bi-grid"></i>
    </button>
    <a href="{{ route('contacts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Contact
    </a>
  </div>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('contacts.index') }}" class="filter-bar mb-3">
  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search name, email, phone…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:210px;">
  </div>

  <select name="owner_id" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Owners</option>
    @foreach($owners as $u)
      <option value="{{ $u->id }}" {{ request('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>

  <select name="account_id" class="ncv-select ncv-select-sm" style="width:150px;">
    <option value="">All Accounts</option>
    @foreach($accounts as $acc)
      <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
    @endforeach
  </select>

  <input type="text" name="tag" value="{{ request('tag') }}"
         placeholder="Tag…" class="ncv-input ncv-input-sm" style="width:100px;">

  <input type="date" name="date_from" value="{{ request('date_from') }}"
         class="ncv-input ncv-input-sm" title="Created from">
  <input type="date" name="date_to" value="{{ request('date_to') }}"
         class="ncv-input ncv-input-sm" title="Created to">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','owner_id','account_id','tag','date_from','date_to']))
    <a href="{{ route('contacts.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- TABLE VIEW --}}
<div id="view-table">
  <div class="ncv-card">
    <div class="ncv-card-body p-0">
      @if($contacts->isEmpty())
        <div class="text-center py-5" style="color:var(--text-muted);">
          <i class="bi bi-people" style="font-size:2.5rem;opacity:.4;"></i>
          <p class="mt-3 mb-1 fw-medium">No contacts found</p>
          <p class="small mb-3">Try adjusting your filters or add a new contact.</p>
          <a href="{{ route('contacts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
            <i class="bi bi-plus-lg"></i> New Contact
          </a>
        </div>
      @else
        <table class="ncv-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Account</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Title</th>
              <th>Owner</th>
              <th>Tags</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($contacts as $contact)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="ncv-table-avatar">
                    {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) }}
                  </div>
                  <a href="{{ route('contacts.show', $contact) }}"
                     class="ncv-table-cell-primary text-decoration-none" style="color:inherit;">
                    {{ $contact->full_name }}
                  </a>
                </div>
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">
                {{ $contact->accounts->first()?->name ?? '—' }}
              </td>
              <td style="font-size:.82rem;">
                @if($contact->email)
                  <a href="mailto:{{ $contact->email }}" style="color:var(--accent-blue); text-decoration:none;">{{ $contact->email }}</a>
                @else
                  <span style="color:var(--text-muted);">—</span>
                @endif
              </td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $contact->phone ?? '—' }}</td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $contact->job_title ?? '—' }}</td>
              <td style="font-size:.82rem; color:var(--text-muted);">{{ $contact->owner?->name ?? '—' }}</td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  @foreach($contact->tags->take(3) as $tag)
                    <span class="ncv-badge" style="background:{{ $tag->color }}22; color:{{ $tag->color }}; font-size:.68rem;">
                      {{ $tag->name }}
                    </span>
                  @endforeach
                </div>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('contacts.show', $contact) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                    <i class="bi bi-eye" style="font-size:.8rem;"></i>
                  </a>
                  <a href="{{ route('contacts.edit', $contact) }}"
                     class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
                    <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                  </a>
                  <form method="POST" action="{{ route('contacts.destroy', $contact) }}"
                        onsubmit="return confirm('Delete {{ addslashes($contact->full_name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm text-danger" title="Delete">
                      <i class="bi bi-trash" style="font-size:.8rem;"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    @if($contacts->hasPages())
    <div class="d-flex align-items-center justify-content-between px-3 py-2"
         style="border-top:1px solid var(--border-color); font-size:.82rem;">
      <span style="color:var(--text-muted);">
        Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ $contacts->total() }}
      </span>
      {{ $contacts->links('pagination::bootstrap-5') }}
    </div>
    @endif
  </div>
</div>

{{-- GRID VIEW --}}
<div id="view-grid" style="display:none;">
  @if($contacts->isEmpty())
    <p class="text-center text-muted py-4">No contacts match your filters.</p>
  @else
    <div class="row g-3">
      @foreach($contacts as $contact)
      <div class="col-sm-6 col-md-4 col-xl-3">
        <div class="ncv-card contact-card h-100"
             onclick="window.location='{{ route('contacts.show', $contact) }}'">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="ncv-table-avatar" style="width:38px;height:38px;font-size:.85rem;">
              {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) }}
            </div>
            <div>
              <div style="font-weight:700;font-size:.88rem;color:var(--text-primary);">{{ $contact->full_name }}</div>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $contact->job_title ?? '—' }}</div>
            </div>
          </div>
          @if($contact->accounts->first())
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem;">
              <i class="bi bi-building me-1"></i>{{ $contact->accounts->first()->name }}
            </div>
          @endif
          @if($contact->email)
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <i class="bi bi-envelope me-1"></i>{{ $contact->email }}
            </div>
          @endif
          @if($contact->phone)
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">
              <i class="bi bi-telephone me-1"></i>{{ $contact->phone }}
            </div>
          @endif
          <div class="d-flex flex-wrap gap-1 mt-auto">
            @foreach($contact->tags->take(3) as $tag)
              <span class="ncv-badge" style="background:{{ $tag->color }}22; color:{{ $tag->color }}; font-size:.65rem;">
                {{ $tag->name }}
              </span>
            @endforeach
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @if($contacts->hasPages())
    <div class="d-flex align-items-center justify-content-between mt-3" style="font-size:.82rem;">
      <span style="color:var(--text-muted);">
        Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ $contacts->total() }}
      </span>
      {{ $contacts->links('pagination::bootstrap-5') }}
    </div>
    @endif
  @endif
</div>

@endsection

@push('scripts')
<script>
function toggleView(v) {
  document.getElementById('view-table').style.display = v === 'table' ? 'block' : 'none';
  document.getElementById('view-grid').style.display  = v === 'grid'  ? 'block' : 'none';
  document.getElementById('btnTable').style.background = v === 'table' ? 'var(--ncv-blue-50)' : '';
  document.getElementById('btnTable').style.color      = v === 'table' ? 'var(--ncv-blue-600)' : '';
  document.getElementById('btnGrid').style.background  = v === 'grid'  ? 'var(--ncv-blue-50)' : '';
  document.getElementById('btnGrid').style.color       = v === 'grid'  ? 'var(--ncv-blue-600)' : '';
  localStorage.setItem('ncv_contacts_view', v);
}
const sv = localStorage.getItem('ncv_contacts_view') || 'table';
toggleView(sv);
</script>
@endpush
