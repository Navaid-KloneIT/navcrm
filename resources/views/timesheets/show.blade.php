@extends('layouts.app')

@section('title', 'Time Entry')
@section('page-title', 'Time Entry')

@section('breadcrumb-items')
  <a href="{{ route('timesheets.index') }}" style="color:inherit;text-decoration:none;">Timesheets</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <span>#{{ $timesheet->id }}</span>
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-xl-6">

    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-clock me-2" style="color:var(--ncv-blue-500);"></i>Time Entry #{{ $timesheet->id }}</h6>
        <div class="d-flex gap-2">
          <a href="{{ route('timesheets.edit', $timesheet) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-pencil"></i> Edit</a>
        </div>
      </div>
      <div class="ncv-card-body">
        @php
          $rows = [
            ['l'=>'Project',     'v'=>$timesheet->project ? $timesheet->project->project_number.' — '.$timesheet->project->name : '—'],
            ['l'=>'User',        'v'=>$timesheet->user?->name ?? '—'],
            ['l'=>'Date',        'v'=>$timesheet->date->format('M d, Y')],
            ['l'=>'Hours',       'v'=>$timesheet->hours.'h', 'bold'=>true],
            ['l'=>'Billable',    'v'=>$timesheet->is_billable ? 'Yes' : 'No'],
          ];
          if($timesheet->billable_rate)
            $rows[] = ['l'=>'Rate', 'v'=>'$'.number_format($timesheet->billable_rate,2).'/h'];
          if($timesheet->description)
            $rows[] = ['l'=>'Description', 'v'=>$timesheet->description];
          $rows[] = ['l'=>'Logged By', 'v'=>$timesheet->createdBy?->name ?? '—'];
          $rows[] = ['l'=>'Created At', 'v'=>$timesheet->created_at->format('M d, Y H:i')];
        @endphp
        @foreach($rows as $row)
        <div style="display:flex;align-items:flex-start;gap:.5rem;padding:.55rem 0;border-bottom:1px solid var(--border-color);font-size:.85rem;">
          <span style="min-width:100px;font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;padding-top:2px;">{{ $row['l'] }}</span>
          <span style="color:var(--text-{{ isset($row['bold']) ? 'primary' : 'secondary' }});font-weight:{{ isset($row['bold']) ? '800' : '400' }};">{{ $row['v'] }}</span>
        </div>
        @endforeach
      </div>
    </div>

    <div class="d-flex gap-2 mt-3">
      @if($timesheet->project)
      <a href="{{ route('projects.show', $timesheet->project) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
        <i class="bi bi-kanban"></i> View Project
      </a>
      @endif
      <a href="{{ route('timesheets.index') }}" class="ncv-btn ncv-btn-ghost ncv-btn-sm">Back to Timesheets</a>
      <form method="POST" action="{{ route('timesheets.destroy', $timesheet) }}" class="ms-auto" onsubmit="return confirm('Delete this entry?')">
        @csrf @method('DELETE')
        <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-sm" style="color:#ef4444;"><i class="bi bi-trash"></i> Delete</button>
      </form>
    </div>

  </div>
</div>
@endsection
