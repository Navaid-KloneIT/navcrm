@extends('layouts.app')

@section('title', $emailCampaign->name)
@section('page-title', $emailCampaign->name)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span style="color:var(--text-muted);font-size:.8rem;">Marketing</span>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('marketing.email-campaigns.index') }}" style="color:inherit;text-decoration:none;font-size:.8rem;">Email Campaigns</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

@php
  $statusBadge = ['draft'=>'muted','scheduled'=>'cyan','sending'=>'primary','sent'=>'success','paused'=>'warning'];
  $sb = $statusBadge[$emailCampaign->status->value] ?? 'muted';
@endphp

{{-- Header --}}
<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="ncv-page-title mb-0">{{ $emailCampaign->name }}</h1>
      <span class="ncv-badge ncv-badge-{{ $sb }}"><span class="dot"></span>{{ ucfirst($emailCampaign->status->value) }}</span>
    </div>
    <p class="ncv-page-subtitle">
      {{ match($emailCampaign->type) {'single'=>'Single Send','drip'=>'Drip Sequence','ab_test'=>'A/B Test', default=>ucfirst($emailCampaign->type)} }}
      @if($emailCampaign->campaign) · <a href="{{ route('marketing.campaigns.show', $emailCampaign->campaign) }}" style="color:var(--ncv-blue-600);">{{ $emailCampaign->campaign->name }}</a> @endif
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('marketing.email-campaigns.edit', $emailCampaign) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <form method="POST" action="{{ route('marketing.email-campaigns.destroy', $emailCampaign) }}"
          onsubmit="return confirm('Delete this email campaign?')">
      @csrf @method('DELETE')
      <button class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:.625rem;">
        <i class="bi bi-trash"></i> Delete
      </button>
    </form>
  </div>
</div>

{{-- Tracking Metrics --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total Sent',    'value'=>number_format($emailCampaign->total_sent),        'color'=>'#2563eb', 'icon'=>'bi-send'],
    ['label'=>'Open Rate',     'value'=>$emailCampaign->open_rate.'%',                    'color'=>'#10b981', 'icon'=>'bi-envelope-open'],
    ['label'=>'Click Rate',    'value'=>$emailCampaign->click_rate.'%',                   'color'=>'#2563eb', 'icon'=>'bi-cursor'],
    ['label'=>'Bounce Rate',   'value'=>$emailCampaign->bounce_rate.'%',                  'color'=>'#f59e0b', 'icon'=>'bi-x-circle'],
    ['label'=>'Unsubscribes',  'value'=>$emailCampaign->unsubscribe_rate.'%',             'color'=>'#ef4444', 'icon'=>'bi-person-dash'],
  ] as $m)
  <div class="col-6 col-md">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi {{ $m['icon'] }}" style="color:{{ $m['color'] }};font-size:.9rem;"></i>
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);">{{ $m['label'] }}</div>
      </div>
      <div style="font-size:1.4rem;font-weight:800;color:{{ $m['color'] }};letter-spacing:-.03em;">{{ $m['value'] }}</div>
    </div>
  </div>
  @endforeach
</div>

<div class="row g-3">

  {{-- Details --}}
  <div class="col-12 col-md-5">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6></div>
      <div class="ncv-card-body">
        <dl style="margin:0;display:grid;gap:.75rem;">
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">From</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->from_name ?? '—' }} &lt;{{ $emailCampaign->from_email ?? '—' }}&gt;</dd>
          </div>
          @if($emailCampaign->type !== 'ab_test')
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Subject</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->subject ?? '—' }}</dd>
          </div>
          @else
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Subject A</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->subject_a ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Subject B</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->subject_b ?? '—' }}</dd>
          </div>
          @if($emailCampaign->winning_variant)
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Winning Variant</dt>
            <dd style="margin:0;"><span class="ncv-badge ncv-badge-success">Variant {{ strtoupper($emailCampaign->winning_variant) }}</span></dd>
          </div>
          @endif
          @endif
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Template</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->template?->name ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Scheduled</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->scheduled_at?->format('M d, Y H:i') ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Sent At</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->sent_at?->format('M d, Y H:i') ?? '—' }}</dd>
          </div>
          <div>
            <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.2rem;">Owner</dt>
            <dd style="margin:0;font-size:.875rem;">{{ $emailCampaign->owner?->name ?? '—' }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>

  {{-- Raw Counts --}}
  <div class="col-12 col-md-7">
    <div class="ncv-card">
      <div class="ncv-card-header"><h6 class="ncv-card-title"><i class="bi bi-bar-chart me-2" style="color:var(--ncv-blue-500);"></i>Delivery Summary</h6></div>
      <div class="ncv-card-body">
        @foreach([
          ['label'=>'Total Sent',       'value'=>$emailCampaign->total_sent,        'color'=>'#2563eb', 'pct'=>100],
          ['label'=>'Unique Opens',      'value'=>$emailCampaign->total_opens,       'color'=>'#10b981', 'pct'=>$emailCampaign->open_rate],
          ['label'=>'Clicks',           'value'=>$emailCampaign->total_clicks,      'color'=>'#0891b2', 'pct'=>$emailCampaign->click_rate],
          ['label'=>'Bounces',          'value'=>$emailCampaign->total_bounces,     'color'=>'#f59e0b', 'pct'=>$emailCampaign->bounce_rate],
          ['label'=>'Unsubscribes',     'value'=>$emailCampaign->total_unsubscribes,'color'=>'#ef4444', 'pct'=>$emailCampaign->unsubscribe_rate],
        ] as $row)
        <div style="margin-bottom:1rem;">
          <div class="d-flex justify-content-between mb-1">
            <span style="font-size:.8rem;font-weight:600;color:var(--text-secondary);">{{ $row['label'] }}</span>
            <span style="font-size:.8rem;font-weight:700;color:{{ $row['color'] }};">{{ number_format($row['value']) }} ({{ $row['pct'] }}%)</span>
          </div>
          <div style="height:6px;border-radius:9999px;background:var(--border-color);overflow:hidden;">
            <div style="height:6px;border-radius:9999px;background:{{ $row['color'] }};width:{{ min($row['pct'],100) }}%;"></div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

</div>

@endsection
