@extends('layouts.app')

@section('title', 'Activities')
@section('page-title', 'Activities')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Activities</h1>
    <p class="ncv-page-subtitle">Track all emails, calls, meetings and tasks across your CRM.</p>
  </div>
  <div class="d-flex gap-2">
    <button class="ncv-btn ncv-btn-primary ncv-btn-sm" onclick="document.getElementById('newActivityModal').classList.add('show')">
      <i class="bi bi-plus-lg"></i> Log Activity
    </button>
  </div>
</div>

{{-- Filter Bar --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.75rem 1.25rem;">
    <form method="GET" class="d-flex align-items-center flex-wrap gap-2">
      <div style="position:relative; min-width:220px; flex:1;">
        <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search activities…"
               class="ncv-input" style="padding-left:2.375rem;" />
      </div>
      <select name="type" class="ncv-select" style="width:160px;height:38px;font-size:.8rem;">
        <option value="">All Types</option>
        <option value="email"   {{ request('type') == 'email'   ? 'selected' : '' }}>Email</option>
        <option value="call"    {{ request('type') == 'call'    ? 'selected' : '' }}>Call</option>
        <option value="meeting" {{ request('type') == 'meeting' ? 'selected' : '' }}>Meeting</option>
        <option value="task"    {{ request('type') == 'task'    ? 'selected' : '' }}>Task</option>
        <option value="note"    {{ request('type') == 'note'    ? 'selected' : '' }}>Note</option>
      </select>
      <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-funnel"></i> Filter
      </button>
      @if(request()->hasAny(['search','type']))
        <a href="{{ route('activities.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Clear</a>
      @endif
    </form>
  </div>
</div>

{{-- Activities List --}}
<div class="ncv-card">
  <div class="ncv-card-body p-0">
    @if($activities->isEmpty())
      <div class="text-center py-5" style="color:var(--text-muted);">
        <i class="bi bi-activity" style="font-size:2.5rem; display:block; margin-bottom:.75rem;"></i>
        <p class="mb-0">No activities logged yet.</p>
        <p style="font-size:.85rem;">Log a call, email, or meeting to get started.</p>
      </div>
    @else
      <div class="ncv-timeline px-4 py-3">
        @foreach($activities as $activity)
          @php
            $icon = match($activity->activity_type) {
              'email'   => 'bi-envelope-fill',
              'call'    => 'bi-telephone-fill',
              'meeting' => 'bi-calendar-event-fill',
              'task'    => 'bi-check2-square',
              default   => 'bi-chat-dots-fill',
            };
            $color = match($activity->activity_type) {
              'email'   => '#2563eb',
              'call'    => '#10b981',
              'meeting' => '#f59e0b',
              'task'    => '#8b5cf6',
              default   => '#94a3b8',
            };
          @endphp
          <div class="d-flex gap-3 py-3 border-bottom">
            <div style="width:36px;height:36px;border-radius:50%;background:{{ $color }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:.9rem;"></i>
            </div>
            <div style="flex:1;">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span style="font-weight:600;font-size:.875rem;text-transform:capitalize;">
                  {{ str_replace('_', ' ', $activity->activity_type) }}
                </span>
                @if($activity->activitable)
                  <span style="font-size:.75rem;color:var(--text-muted);">on</span>
                  <span style="font-size:.8rem;color:var(--ncv-blue-600);font-weight:500;">
                    {{ $activity->activitable->full_name ?? $activity->activitable->name ?? 'Unknown' }}
                  </span>
                @endif
                <span style="font-size:.72rem;color:var(--text-muted);margin-left:auto;">
                  {{ $activity->created_at->diffForHumans() }}
                </span>
              </div>
              @if($activity->description)
                <p style="font-size:.8rem;color:var(--text-secondary);margin:.25rem 0 0;">
                  {{ $activity->description }}
                </p>
              @endif
              @if($activity->user)
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">
                  By {{ $activity->user->name }}
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      {{-- Pagination --}}
      @if($activities->hasPages())
        <div class="px-4 py-3">
          {{ $activities->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

@endsection
