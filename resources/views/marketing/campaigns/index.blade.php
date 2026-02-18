@extends('layouts.app')

@section('title', 'Campaigns')
@section('page-title', 'Campaigns')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Campaigns</h1>
    <p class="ncv-page-subtitle">Manage your marketing campaigns and track ROI.</p>
  </div>
  <a href="{{ route('marketing.campaigns.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Campaign
  </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total Campaigns', 'value'=>$campaigns->total(),           'color'=>'var(--ncv-blue-600)', 'icon'=>'bi-megaphone'],
    ['label'=>'Active',          'value'=>$statusCounts['active']  ?? 0, 'color'=>'#10b981',             'icon'=>'bi-play-circle'],
    ['label'=>'Draft',           'value'=>$statusCounts['draft']   ?? 0, 'color'=>'#f59e0b',             'icon'=>'bi-file-earmark'],
    ['label'=>'Completed',       'value'=>$statusCounts['completed']?? 0,'color'=>'#8b5cf6',             'icon'=>'bi-check-circle'],
  ] as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:1rem;"></i>
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);">{{ $stat['label'] }}</div>
      </div>
      <div style="font-size:1.6rem;font-weight:800;color:{{ $stat['color'] }};letter-spacing:-.03em;">{{ $stat['value'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('marketing.campaigns.index') }}">
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <div style="position:relative;flex:1;max-width:300px;">
      <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns…"
             class="ncv-input" style="padding-left:2.375rem;height:38px;" />
    </div>
    <select name="status" class="ncv-select" style="width:140px;height:38px;font-size:.82rem;">
      <option value="">All Statuses</option>
      @foreach(['draft'=>'Draft','active'=>'Active','paused'=>'Paused','completed'=>'Completed'] as $val=>$label)
        <option value="{{ $val }}" {{ request('status')===$val ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    <select name="type" class="ncv-select" style="width:150px;height:38px;font-size:.82rem;">
      <option value="">All Types</option>
      @foreach(['email'=>'Email','webinar'=>'Webinar','event'=>'Event','digital_ads'=>'Digital Ads','direct_mail'=>'Direct Mail'] as $val=>$label)
        <option value="{{ $val }}" {{ request('type')===$val ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">Filter</button>
    @if(request()->hasAny(['search','status','type']))
      <a href="{{ route('marketing.campaigns.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
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
        <th>Budget (Plan / Actual)</th>
        <th>Revenue</th>
        <th>ROI</th>
        <th>Dates</th>
        <th>Owner</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($campaigns as $campaign)
      @php
        $typeColors = [
          'email'=>['bg'=>'#dbeafe','color'=>'#1e40af'],
          'webinar'=>['bg'=>'#f3e8ff','color'=>'#7c3aed'],
          'event'=>['bg'=>'#dcfce7','color'=>'#15803d'],
          'digital_ads'=>['bg'=>'#fef3c7','color'=>'#92400e'],
          'direct_mail'=>['bg'=>'#fce7f3','color'=>'#9d174d'],
        ];
        $statusColors = ['draft'=>'muted','active'=>'success','paused'=>'cyan','completed'=>'purple'];
        $tc = $typeColors[$campaign->type->value] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
        $sc = $statusColors[$campaign->status->value] ?? 'muted';
        $roiVal = $campaign->roi;
      @endphp
      <tr>
        <td>
          <a href="{{ route('marketing.campaigns.show', $campaign) }}"
             class="ncv-table-cell-primary" style="text-decoration:none;color:inherit;">
            {{ $campaign->name }}
          </a>
          @if($campaign->description)
            <div class="ncv-table-cell-sub">{{ Str::limit($campaign->description, 50) }}</div>
          @endif
        </td>
        <td>
          <span style="display:inline-flex;align-items:center;padding:.25rem .6rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
            {{ ucfirst(str_replace('_',' ',$campaign->type->value)) }}
          </span>
        </td>
        <td><span class="ncv-badge ncv-badge-{{ $sc }}"><span class="dot"></span>{{ ucfirst($campaign->status->value) }}</span></td>
        <td style="font-size:.82rem;">
          <div style="color:var(--text-secondary);">${{ number_format($campaign->planned_budget ?? 0, 0) }}</div>
          <div style="color:var(--text-muted);font-size:.75rem;">${{ number_format($campaign->actual_budget ?? 0, 0) }} actual</div>
        </td>
        <td style="font-size:.82rem;color:var(--text-secondary);">${{ number_format($campaign->actual_revenue ?? 0, 0) }}</td>
        <td>
          @if($roiVal !== null)
            <span style="font-size:.82rem;font-weight:700;color:{{ $roiVal >= 0 ? '#10b981' : '#ef4444' }};">
              {{ $roiVal >= 0 ? '+' : '' }}{{ $roiVal }}%
            </span>
          @else
            <span style="font-size:.75rem;color:var(--text-muted);">—</span>
          @endif
        </td>
        <td style="font-size:.775rem;color:var(--text-muted);">
          {{ $campaign->start_date?->format('M d') ?? '—' }}
          @if($campaign->end_date) → {{ $campaign->end_date->format('M d, Y') }} @endif
        </td>
        <td style="font-size:.82rem;color:var(--text-muted);">{{ $campaign->owner?->name ?? '—' }}</td>
        <td>
          <div class="d-flex gap-1">
            <a href="{{ route('marketing.campaigns.show', $campaign) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View">
              <i class="bi bi-eye" style="font-size:.8rem;"></i>
            </a>
            <a href="{{ route('marketing.campaigns.edit', $campaign) }}"
               class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit">
              <i class="bi bi-pencil" style="font-size:.8rem;"></i>
            </a>
            <form method="POST" action="{{ route('marketing.campaigns.destroy', $campaign) }}"
                  onsubmit="return confirm('Delete this campaign?')">
              @csrf @method('DELETE')
              <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;" title="Delete">
                <i class="bi bi-trash" style="font-size:.8rem;"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="text-center" style="padding:3rem;color:var(--text-muted);">
          <i class="bi bi-megaphone" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
          No campaigns yet. <a href="{{ route('marketing.campaigns.create') }}">Create your first campaign</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Pagination --}}
@if($campaigns->hasPages())
<div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">
    Showing <strong>{{ $campaigns->firstItem() }}–{{ $campaigns->lastItem() }}</strong>
    of <strong>{{ $campaigns->total() }}</strong> campaigns
  </p>
  {{ $campaigns->links() }}
</div>
@endif

@endsection
