@extends('layouts.app')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.campaigns.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Campaigns</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

@php
  $statusColors = ['draft'=>'muted','active'=>'success','paused'=>'cyan','completed'=>'purple'];
  $sc = $statusColors[$campaign->status->value] ?? 'muted';
  $roi = $campaign->roi;
@endphp

{{-- Header --}}
<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="ncv-page-title mb-0">{{ $campaign->name }}</h1>
      <span class="ncv-badge ncv-badge-{{ $sc }}"><span class="dot"></span>{{ ucfirst($campaign->status->value) }}</span>
    </div>
    @if($campaign->description)
      <p class="ncv-page-subtitle">{{ $campaign->description }}</p>
    @endif
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('marketing.campaigns.edit', $campaign) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <form method="POST" action="{{ route('marketing.campaigns.destroy', $campaign) }}"
          onsubmit="return confirm('Delete this campaign?')">
      @csrf @method('DELETE')
      <button class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:.625rem;">
        <i class="bi bi-trash"></i> Delete
      </button>
    </form>
  </div>
</div>

{{-- ROI Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Planned Budget',  'value'=>'$'.number_format($campaign->planned_budget??0,0),  'color'=>'#2563eb', 'icon'=>'bi-wallet2'],
    ['label'=>'Actual Spend',    'value'=>'$'.number_format($campaign->actual_budget??0,0),   'color'=>'#f59e0b', 'icon'=>'bi-receipt'],
    ['label'=>'Actual Revenue',  'value'=>'$'.number_format($campaign->actual_revenue??0,0),  'color'=>'#10b981', 'icon'=>'bi-graph-up'],
    ['label'=>'ROI',             'value'=>$roi !== null ? ($roi >= 0 ? '+' : '').$roi.'%' : '—', 'color'=>($roi !== null && $roi >= 0) ? '#10b981' : '#ef4444', 'icon'=>'bi-percent'],
  ] as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:.9rem;"></i>
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);">{{ $stat['label'] }}</div>
      </div>
      <div style="font-size:1.4rem;font-weight:800;color:{{ $stat['color'] }};letter-spacing:-.03em;">{{ $stat['value'] }}</div>
    </div>
  </div>
  @endforeach
</div>

<div class="row g-3">

  {{-- Details --}}
  <div class="col-12 col-md-4">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6></div>
      <div class="ncv-card-body">
        <dl style="margin:0;display:grid;gap:.75rem;">
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Type</dt>
            <dd style="margin:0;font-size:.875rem;font-weight:600;">{{ ucfirst(str_replace('_',' ',$campaign->type->value)) }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Owner</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $campaign->owner?->name ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Start Date</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $campaign->start_date?->format('M d, Y') ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">End Date</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $campaign->end_date?->format('M d, Y') ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Created</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $campaign->created_at->format('M d, Y') }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>

  {{-- Target Lists --}}
  <div class="col-12 col-md-8">
    <div class="ncv-card">
      <div class="ncv-card-header d-flex align-items-center justify-content-between">
        <h6 class="ncv-card-title mb-0"><i class="bi bi-people me-2" style="color:var(--ncv-blue-500);"></i>Target Lists</h6>
        <button class="ncv-btn ncv-btn-primary ncv-btn-sm" data-bs-toggle="modal" data-bs-target="#addListModal">
          <i class="bi bi-plus-lg"></i> Add List
        </button>
      </div>
      <div class="ncv-card-body" style="padding:0;">
        @forelse($campaign->targetLists as $list)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 1.25rem;border-bottom:1px solid var(--border-color);">
          <div>
            <div style="font-weight:600;font-size:.875rem;">{{ $list->name }}</div>
            @if($list->description)
              <div style="font-size:.775rem;color:var(--text-muted);">{{ $list->description }}</div>
            @endif
          </div>
          <span class="ncv-badge ncv-badge-primary">{{ $list->contacts_count ?? 0 }} contacts</span>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:.85rem;">
          <i class="bi bi-people" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
          No target lists yet.
        </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Email Campaigns --}}
  <div class="col-12">
    <div class="ncv-card">
      <div class="ncv-card-header d-flex align-items-center justify-content-between">
        <h6 class="ncv-card-title mb-0"><i class="bi bi-envelope me-2" style="color:var(--ncv-blue-500);"></i>Email Campaigns</h6>
        <a href="{{ route('marketing.email-campaigns.create') }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
          <i class="bi bi-plus-lg"></i> New Email Campaign
        </a>
      </div>
      <div class="ncv-card-body" style="padding:0;">
        @forelse($campaign->emailCampaigns as $ec)
        @php $ecStatus = ['draft'=>'muted','scheduled'=>'cyan','sending'=>'primary','sent'=>'success','paused'=>'warning'][$ec->status->value] ?? 'muted'; @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 1.25rem;border-bottom:1px solid var(--border-color);">
          <div>
            <a href="{{ route('marketing.email-campaigns.show', $ec) }}"
               style="font-weight:600;font-size:.875rem;color:var(--ncv-blue-600);text-decoration:none;">{{ $ec->name }}</a>
            <div style="font-size:.775rem;color:var(--text-muted);">{{ ucfirst($ec->type) }} · {{ $ec->total_sent }} sent</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            @if($ec->total_sent)
              <span style="font-size:.75rem;color:var(--text-muted);">{{ $ec->open_rate }}% open</span>
            @endif
            <span class="ncv-badge ncv-badge-{{ $ecStatus }}"><span class="dot"></span>{{ ucfirst($ec->status->value) }}</span>
          </div>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:.85rem;">
          <i class="bi bi-envelope" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
          No email campaigns linked yet.
        </div>
        @endforelse
      </div>
    </div>
  </div>

</div>

{{-- Add Target List Modal --}}
<div class="modal fade" id="addListModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:1rem;border:none;">
      <form method="POST" action="{{ route('marketing.campaigns.index') }}">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border-color);padding:1.25rem 1.5rem;">
          <h5 class="modal-title" style="font-weight:700;font-size:1rem;">Add Target List</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:1.5rem;">
          <div class="mb-3">
            <label class="ncv-label">List Name <span class="required">*</span></label>
            <input type="text" name="list_name" class="ncv-input" placeholder="e.g. Decision Makers - US" required />
          </div>
          <div>
            <label class="ncv-label">Description</label>
            <textarea name="list_description" class="ncv-textarea" rows="2" placeholder="Who is in this list?"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border-color);padding:1rem 1.5rem;">
          <button type="button" class="ncv-btn ncv-btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="ncv-btn ncv-btn-primary">Create List</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
