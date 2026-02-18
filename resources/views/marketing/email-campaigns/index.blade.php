@extends('layouts.app')

@section('title', 'Email Campaigns')
@section('page-title', 'Email Campaigns')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .metric-pill { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .55rem; border-radius:9999px; font-size:.7rem; font-weight:700; }
</style>
@endpush

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Email Campaigns</h1>
    <p class="ncv-page-subtitle">Manage drip campaigns, A/B tests, and email blasts.</p>
  </div>
  <a href="{{ route('marketing.email-campaigns.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Email Campaign
  </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('marketing.email-campaigns.index') }}">
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <div style="position:relative;flex:1;max-width:280px;">
      <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…"
             class="ncv-input" style="padding-left:2.375rem;height:38px;" />
    </div>
    <select name="status" class="ncv-select" style="width:140px;height:38px;font-size:.82rem;">
      <option value="">All Statuses</option>
      @foreach(['draft'=>'Draft','scheduled'=>'Scheduled','sending'=>'Sending','sent'=>'Sent','paused'=>'Paused'] as $v=>$l)
        <option value="{{ $v }}" {{ request('status')===$v ? 'selected' : '' }}>{{ $l }}</option>
      @endforeach
    </select>
    <select name="type" class="ncv-select" style="width:130px;height:38px;font-size:.82rem;">
      <option value="">All Types</option>
      <option value="single"  {{ request('type')==='single'   ? 'selected' : '' }}>Single</option>
      <option value="drip"    {{ request('type')==='drip'     ? 'selected' : '' }}>Drip</option>
      <option value="ab_test" {{ request('type')==='ab_test'  ? 'selected' : '' }}>A/B Test</option>
    </select>
    <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">Filter</button>
    @if(request()->hasAny(['search','status','type']))
      <a href="{{ route('marketing.email-campaigns.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
    @endif
  </div>
</form>

{{-- Table --}}
<div class="ncv-table-wrapper">
  <table class="ncv-table">
    <thead>
      <tr>
        <th>Campaign</th>
        <th>Type</th>
        <th>Status</th>
        <th>Sent</th>
        <th>Opens</th>
        <th>Clicks</th>
        <th>Bounces</th>
        <th>Scheduled</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($emailCampaigns as $ec)
      @php
        $typeColors = ['single'=>['#dbeafe','#1e40af'],'drip'=>['#dcfce7','#15803d'],'ab_test'=>['#f3e8ff','#7c3aed']];
        $statusBadge = ['draft'=>'muted','scheduled'=>'cyan','sending'=>'primary','sent'=>'success','paused'=>'warning'];
        $tc = $typeColors[$ec->type] ?? ['#f1f5f9','#475569'];
        $sb = $statusBadge[$ec->status->value] ?? 'muted';
      @endphp
      <tr>
        <td>
          <a href="{{ route('marketing.email-campaigns.show', $ec) }}"
             class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;">{{ $ec->name }}</a>
          @if($ec->campaign)
            <div class="ncv-table-cell-sub"><i class="bi bi-megaphone me-1"></i>{{ $ec->campaign->name }}</div>
          @endif
        </td>
        <td>
          <span style="display:inline-flex;align-items:center;padding:.25rem .6rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $tc[0] }};color:{{ $tc[1] }};">
            {{ match($ec->type) { 'single'=>'Single','drip'=>'Drip','ab_test'=>'A/B Test', default=>ucfirst($ec->type) } }}
          </span>
        </td>
        <td><span class="ncv-badge ncv-badge-{{ $sb }}"><span class="dot"></span>{{ ucfirst($ec->status->value) }}</span></td>
        <td style="font-size:.82rem;font-weight:600;">{{ number_format($ec->total_sent) }}</td>
        <td>
          <span class="metric-pill" style="background:#dcfce7;color:#15803d;">
            <i class="bi bi-envelope-open" style="font-size:.65rem;"></i>
            {{ $ec->open_rate }}%
          </span>
        </td>
        <td>
          <span class="metric-pill" style="background:#dbeafe;color:#1e40af;">
            <i class="bi bi-cursor" style="font-size:.65rem;"></i>
            {{ $ec->click_rate }}%
          </span>
        </td>
        <td>
          <span class="metric-pill" style="background:#fee2e2;color:#b91c1c;">
            <i class="bi bi-x-circle" style="font-size:.65rem;"></i>
            {{ $ec->bounce_rate }}%
          </span>
        </td>
        <td style="font-size:.775rem;color:var(--text-muted);">
          {{ $ec->scheduled_at?->format('M d, Y H:i') ?? ($ec->sent_at?->format('M d, Y') ?? '—') }}
        </td>
        <td>
          <div class="d-flex gap-1">
            <a href="{{ route('marketing.email-campaigns.show', $ec) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
              <i class="bi bi-eye" style="font-size:.8rem;"></i>
            </a>
            <a href="{{ route('marketing.email-campaigns.edit', $ec) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
              <i class="bi bi-pencil" style="font-size:.8rem;"></i>
            </a>
            <form method="POST" action="{{ route('marketing.email-campaigns.destroy', $ec) }}"
                  onsubmit="return confirm('Delete this email campaign?')">
              @csrf @method('DELETE')
              <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;">
                <i class="bi bi-trash" style="font-size:.8rem;"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="text-center" style="padding:3rem;color:var(--text-muted);">
          <i class="bi bi-envelope" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
          No email campaigns yet. <a href="{{ route('marketing.email-campaigns.create') }}">Create your first one</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($emailCampaigns->hasPages())
<div class="d-flex align-items-center justify-content-between mt-3">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
    Showing <strong>{{ $emailCampaigns->firstItem() }}–{{ $emailCampaigns->lastItem() }}</strong>
    of <strong>{{ $emailCampaigns->total() }}</strong>
  </p>
  {{ $emailCampaigns->links() }}
</div>
@endif

@endsection
