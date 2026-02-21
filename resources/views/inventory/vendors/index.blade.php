@extends('layouts.app')

@section('title', 'Vendors')
@section('page-title', 'Vendors')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Vendors</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">
      {{ $stats['total'] }} total &middot;
      {{ $stats['active'] }} active &middot;
      {{ $stats['inactive'] }} inactive
    </p>
  </div>
  <a href="{{ route('inventory.vendors.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Vendor
  </a>
</div>

{{-- Status chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ route('inventory.vendors.index', request()->except('status','page')) }}"
     class="ncv-chip {{ !request('status') ? 'active' : '' }}" style="font-size:.78rem;">All</a>
  @foreach(\App\Enums\VendorStatus::cases() as $s)
  <a href="{{ route('inventory.vendors.index', array_merge(request()->except('status','page'), ['status' => $s->value])) }}"
     class="ncv-chip {{ request('status') === $s->value ? 'active' : '' }}" style="font-size:.78rem;">
    {{ $s->label() }}
  </a>
  @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('inventory.vendors.index') }}" class="filter-bar mb-3">
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif

  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search vendors…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:220px;">
  </div>

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','status']))
    <a href="{{ route('inventory.vendors.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Table --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($vendors->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-person-plus" style="font-size:2.5rem;opacity:.4;"></i>
        <p class="mt-3 mb-1 fw-medium">No vendors found</p>
        <p class="small mb-3">Add a new vendor or adjust the filters.</p>
        <a href="{{ route('inventory.vendors.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New Vendor
        </a>
      </div>
    @else
      <table class="ncv-table">
        <thead>
          <tr>
            <th>Vendor #</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Status</th>
            <th style="text-align:right;">POs</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($vendors as $vendor)
          <tr>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $vendor->vendor_number }}</td>
            <td>
              <a href="{{ route('inventory.vendors.show', $vendor) }}"
                 class="ncv-table-cell-primary text-decoration-none" style="color:inherit;font-size:.875rem;">
                {{ $vendor->company_name }}
              </a>
            </td>
            <td style="font-size:.82rem;">{{ $vendor->contact_name ?? '—' }}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">{{ $vendor->email ?? '—' }}</td>
            <td>
              <span class="ncv-badge bg-{{ $vendor->status->color() }}-subtle text-{{ $vendor->status->color() }}" style="font-size:.72rem;">
                {{ $vendor->status->label() }}
              </span>
            </td>
            <td style="text-align:right;font-size:.82rem;">{{ $vendor->purchase_orders_count }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ route('inventory.vendors.show', $vendor) }}"
                   class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
                  <i class="bi bi-eye" style="font-size:.8rem;"></i>
                </a>
                <a href="{{ route('inventory.vendors.edit', $vendor) }}"
                   class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
                  <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                </a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($vendors->hasPages())
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-top:1px solid var(--border-color); font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }}
    </span>
    {{ $vendors->links('pagination::bootstrap-5') }}
  </div>
  @endif
</div>

@endsection
