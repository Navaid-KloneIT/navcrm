@extends('layouts.app')

@section('title', $pipeline->name)
@section('page-title', 'Pipeline Details')

@section('breadcrumb-items')
  <a href="{{ route('success.onboarding.index') }}" style="color:inherit;text-decoration:none;">Onboarding Pipelines</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>{{ $pipeline->pipeline_number }}</span>
@endsection

@section('content')

{{-- Hero --}}
<div class="ncv-card mb-3" style="background:linear-gradient(135deg,#0d6efd,#0143a3);border:none;color:#fff;overflow:hidden;position:relative;">
  <div style="position:absolute;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.04);top:-80px;right:-60px;"></div>
  <div class="ncv-card-body" style="padding:1.75rem;position:relative;z-index:1;">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.6);font-family:monospace;margin-bottom:.35rem;">{{ $pipeline->pipeline_number }}</div>
        <h1 style="font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0 0 .5rem;">{{ $pipeline->name }}</h1>
        <div style="display:flex;gap:1.25rem;font-size:.875rem;color:rgba(255,255,255,.75);flex-wrap:wrap;">
          @if($pipeline->account)
          <span><i class="bi bi-building"></i> {{ $pipeline->account->name }}</span>
          @endif
          @if($pipeline->assignee)
          <span><i class="bi bi-person"></i> Assignee: {{ $pipeline->assignee->name }}</span>
          @endif
          <span class="ncv-badge ncv-badge-{{ $pipeline->status->color() }}">{{ $pipeline->status->label() }}</span>
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('success.onboarding.edit', $pipeline) }}" class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
          <i class="bi bi-pencil"></i> Edit
        </a>
        <form method="POST" action="{{ route('success.onboarding.destroy', $pipeline) }}" onsubmit="return confirm('Delete this pipeline?')">
          @csrf @method('DELETE')
          <button type="submit" class="ncv-btn ncv-btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);">
            <i class="bi bi-trash"></i> Delete
          </button>
        </form>
      </div>
    </div>

    {{-- Progress Bar --}}
    @php $pct = $pipeline->progress; @endphp
    <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.1);">
      <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;font-size:.78rem;color:rgba(255,255,255,.75);">
        <span>Onboarding Progress</span>
        <span>{{ $pct }}%</span>
      </div>
      <div style="height:8px;background:rgba(255,255,255,.15);border-radius:4px;overflow:hidden;">
        <div style="width:{{ $pct }}%;height:100%;background:{{ $pct === 100 ? '#10b981' : '#60a5fa' }};border-radius:4px;transition:width .4s;"></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  {{-- Left Sidebar --}}
  <div class="col-12 col-lg-4">

    {{-- Details --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Details</h6>
      </div>
      <div class="ncv-card-body">
        @php
          $rows = [
            ['l'=>'Status',       'v'=>$pipeline->status->label(),                           'badge'=>$pipeline->status->color()],
            ['l'=>'Account',      'v'=>$pipeline->account?->name ?? '—',                     'link'=>$pipeline->account ? route('accounts.show', $pipeline->account) : null],
            ['l'=>'Contact',      'v'=>$pipeline->contact ? $pipeline->contact->first_name.' '.$pipeline->contact->last_name : '—', 'link'=>$pipeline->contact ? route('contacts.show', $pipeline->contact) : null],
            ['l'=>'Assignee',     'v'=>$pipeline->assignee?->name ?? '—'],
            ['l'=>'Created By',   'v'=>$pipeline->creator?->name ?? '—'],
            ['l'=>'Due Date',     'v'=>$pipeline->due_date?->format('M d, Y') ?? '—'],
            ['l'=>'Started At',   'v'=>$pipeline->started_at?->format('M d, Y g:ia') ?? '—'],
            ['l'=>'Completed At', 'v'=>$pipeline->completed_at?->format('M d, Y g:ia') ?? '—'],
          ];
        @endphp
        @foreach($rows as $row)
        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.55rem 0;border-bottom:1px solid var(--border-color);font-size:.85rem;">
          <span style="min-width:95px;font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;padding-top:2px;">{{ $row['l'] }}</span>
          @if(isset($row['badge']))
            <span class="ncv-badge ncv-badge-{{ $row['badge'] }}">{{ $row['v'] }}</span>
          @elseif(isset($row['link']) && $row['link'])
            <a href="{{ $row['link'] }}" style="color:var(--ncv-blue-600);font-weight:600;text-decoration:none;">{{ $row['v'] }}</a>
          @else
            <span style="color:var(--text-secondary);">{{ $row['v'] }}</span>
          @endif
        </div>
        @endforeach

        @if($pipeline->description)
        <div style="padding:.75rem 0 0;font-size:.85rem;color:var(--text-secondary);">
          {{ $pipeline->description }}
        </div>
        @endif
      </div>
    </div>

    {{-- Progress Summary --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-pie-chart me-2" style="color:var(--ncv-blue-500);"></i>Progress Summary</h6>
      </div>
      <div class="ncv-card-body">
        @php
          $totalSteps     = $pipeline->steps->count();
          $completedSteps = $pipeline->steps->where('is_completed', true)->count();
        @endphp
        <div style="text-align:center;padding:.5rem 0;">
          <div style="font-size:2rem;font-weight:800;color:var(--text-primary);line-height:1;">{{ $completedSteps }} <span style="font-size:1rem;font-weight:600;color:var(--text-muted);">of</span> {{ $totalSteps }}</div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:.35rem;">steps completed</div>
          <div style="margin-top:.75rem;height:8px;background:var(--border-color);border-radius:4px;overflow:hidden;">
            <div style="width:{{ $pct }}%;height:100%;background:{{ $pct === 100 ? '#10b981' : '#3b82f6' }};border-radius:4px;transition:width .4s;"></div>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Right Content --}}
  <div class="col-12 col-lg-8">

    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-list-check me-2" style="color:var(--ncv-blue-500);"></i>Onboarding Steps</h6>
        <button class="ncv-btn ncv-btn-outline ncv-btn-sm" data-bs-toggle="collapse" data-bs-target="#addStepForm">
          <i class="bi bi-plus-lg"></i> Add Step
        </button>
      </div>

      {{-- Add Step Form --}}
      <div class="collapse" id="addStepForm">
        <div class="ncv-card-body" style="border-bottom:1px solid var(--border-color);">
          <form method="POST" action="{{ route('success.onboarding.steps.store', $pipeline) }}">
            @csrf
            <div class="row g-2">
              <div class="col-md-6">
                <input type="text" name="title" class="ncv-input @error('title') is-invalid @enderror" placeholder="Step title *" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-3">
                <input type="date" name="due_date" class="ncv-input">
              </div>
              <div class="col-md-3">
                <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm w-100">Save Step</button>
              </div>
              <div class="col-12">
                <textarea name="description" class="ncv-input" rows="2" placeholder="Description (optional)..."></textarea>
              </div>
            </div>
          </form>
        </div>
      </div>

      {{-- Steps Checklist --}}
      <div class="ncv-card-body p-0">
        @forelse($pipeline->steps as $step)
        <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1.25rem;border-bottom:1px solid var(--border-color);{{ $step->is_completed ? 'background:#f0fdf4;' : '' }}">

          {{-- Toggle Checkbox --}}
          <form method="POST" action="{{ route('success.onboarding.steps.toggle', [$pipeline, $step]) }}" style="flex-shrink:0;padding-top:2px;">
            @csrf
            <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="width:28px;height:28px;border-radius:50%;border:2px solid {{ $step->is_completed ? '#10b981' : 'var(--border-color)' }};background:{{ $step->is_completed ? '#10b981' : 'transparent' }};color:{{ $step->is_completed ? '#fff' : 'transparent' }};display:flex;align-items:center;justify-content:center;padding:0;">
              <i class="bi bi-check-lg" style="font-size:.8rem;"></i>
            </button>
          </form>

          {{-- Step Content --}}
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);{{ $step->is_completed ? 'text-decoration:line-through;opacity:.65;' : '' }}">
              {{ $step->title }}
            </div>
            @if($step->description)
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;{{ $step->is_completed ? 'text-decoration:line-through;opacity:.55;' : '' }}">
              {{ $step->description }}
            </div>
            @endif
            <div style="display:flex;gap:.75rem;margin-top:.3rem;font-size:.72rem;color:var(--text-muted);">
              @if($step->due_date)
              <span>
                <i class="bi bi-calendar3" style="font-size:.65rem;"></i>
                <span class="{{ $step->due_date->isPast() && ! $step->is_completed ? 'text-danger fw-bold' : '' }}">
                  {{ $step->due_date->format('M d, Y') }}
                </span>
              </span>
              @endif
              @if($step->is_completed && $step->completedByUser)
              <span>
                <i class="bi bi-person-check" style="font-size:.65rem;"></i>
                {{ $step->completedByUser->name }} on {{ $step->completed_at->format('M d, Y') }}
              </span>
              @endif
            </div>
          </div>

          {{-- Delete Step --}}
          <form method="POST" action="{{ route('success.onboarding.steps.destroy', [$pipeline, $step]) }}" style="flex-shrink:0;" onsubmit="return confirm('Remove this step?')">
            @csrf @method('DELETE')
            <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash" style="font-size:.75rem;"></i></button>
          </form>

        </div>
        @empty
        <div class="text-center py-4 text-muted">
          <i class="bi bi-list-check" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
          No steps yet. Add one above to start tracking onboarding progress.
        </div>
        @endforelse
      </div>
    </div>

  </div>
</div>

@endsection
