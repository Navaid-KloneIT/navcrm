@extends('layouts.app')

@section('title', 'Accounts')
@section('page-title', 'Accounts')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .filter-bar { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .ncv-input-sm  { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .ncv-select-sm { height:34px; font-size:.82rem; padding:.25rem .6rem; }
  .account-card { border-radius:.75rem; transition:box-shadow .15s; cursor:pointer; }
  .account-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.09); }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title mb-0">Accounts</h1>
    <p class="text-muted mb-0" style="font-size:.82rem;">{{ $accounts->total() }} account{{ $accounts->total() !== 1 ? 's' : '' }} found</p>
  </div>
  <a href="{{ route('accounts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Account
  </a>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('accounts.index') }}" class="filter-bar mb-3">
  <div style="position:relative;">
    <i class="bi bi-search" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search name, website…"
           class="ncv-input ncv-input-sm" style="padding-left:2rem;width:200px;">
  </div>

  <select name="industry" class="ncv-select ncv-select-sm" style="width:150px;">
    <option value="">All Industries</option>
    @foreach($industries as $ind)
      <option value="{{ $ind }}" {{ request('industry') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
    @endforeach
  </select>

  <select name="size" class="ncv-select ncv-select-sm" style="width:130px;">
    <option value="">All Sizes</option>
    @foreach(['1-50'=>'1–50','51-200'=>'51–200','201-1000'=>'201–1,000','1001+'=>'1,001+'] as $val => $label)
      <option value="{{ $val }}" {{ request('size') === $val ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
  </select>

  <select name="owner_id" class="ncv-select ncv-select-sm" style="width:140px;">
    <option value="">All Owners</option>
    @foreach($owners as $u)
      <option value="{{ $u->id }}" {{ request('owner_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
    @endforeach
  </select>

  <input type="date" name="date_from" value="{{ request('date_from') }}"
         class="ncv-input ncv-input-sm" title="Created from">
  <input type="date" name="date_to" value="{{ request('date_to') }}"
         class="ncv-input ncv-input-sm" title="Created to">

  <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm">Apply</button>
  @if(request()->hasAny(['search','industry','size','owner_id','date_from','date_to']))
    <a href="{{ route('accounts.index') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">Clear</a>
  @endif
</form>

{{-- Account Cards --}}
@if($accounts->isEmpty())
  <div class="ncv-card text-center py-5" style="color:var(--text-muted);">
    <i class="bi bi-building" style="font-size:2.5rem;opacity:.4;"></i>
    <p class="mt-3 mb-1 fw-medium">No accounts found</p>
    <p class="small mb-3">Try adjusting your filters or add a new account.</p>
    <a href="{{ route('accounts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Account
    </a>
  </div>
@else
  <div class="row g-3">
    @foreach($accounts as $account)
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <div class="ncv-card account-card h-100"
           onclick="window.location='{{ route('accounts.show', $account) }}'">
        <div class="ncv-card-body">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="ncv-table-avatar" style="width:38px;height:38px;border-radius:.5rem;font-size:.85rem;font-weight:800;">
              {{ strtoupper(substr($account->name, 0, 2)) }}
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:700;font-size:.9rem;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $account->name }}
              </div>
              <div style="font-size:.75rem;color:var(--text-muted);">{{ $account->industry ?? 'No industry' }}</div>
            </div>
          </div>

          <div class="row g-2" style="font-size:.78rem; color:var(--text-muted);">
            @if($account->website)
            <div class="col-12" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <i class="bi bi-globe me-1"></i>
              <a href="{{ $account->website }}" target="_blank" rel="noopener"
                 onclick="event.stopPropagation()"
                 style="color:var(--accent-blue);text-decoration:none;">{{ $account->website }}</a>
            </div>
            @endif
            @if($account->employee_count)
            <div class="col-6">
              <i class="bi bi-people me-1"></i>{{ number_format($account->employee_count) }} employees
            </div>
            @endif
            @if($account->annual_revenue)
            <div class="col-6">
              <i class="bi bi-currency-dollar me-1"></i>{{ number_format($account->annual_revenue / 1000, 0) }}k revenue
            </div>
            @endif
            <div class="col-12" style="border-top:1px solid var(--border-color);padding-top:.5rem;margin-top:.25rem;">
              <i class="bi bi-person me-1"></i>{{ $account->owner?->name ?? 'Unassigned' }}
            </div>
          </div>
        </div>

        <div class="d-flex gap-1 px-3 pb-2" onclick="event.stopPropagation()">
          <a href="{{ route('accounts.show', $account) }}"
             class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
            <i class="bi bi-eye" style="font-size:.8rem;"></i>
          </a>
          <a href="{{ route('accounts.edit', $account) }}"
             class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
            <i class="bi bi-pencil" style="font-size:.8rem;"></i>
          </a>
          <form method="POST" action="{{ route('accounts.destroy', $account) }}"
                onsubmit="return confirm('Delete {{ addslashes($account->name) }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm text-danger" title="Delete">
              <i class="bi bi-trash" style="font-size:.8rem;"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  @if($accounts->hasPages())
  <div class="d-flex align-items-center justify-content-between mt-3" style="font-size:.82rem;">
    <span style="color:var(--text-muted);">
      Showing {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} of {{ $accounts->total() }}
    </span>
    {{ $accounts->links('pagination::bootstrap-5') }}
  </div>
  @endif
@endif

@endsection
