@extends('layouts.app')

@section('title', 'Accounts')
@section('page-title', 'Accounts')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Accounts</h1>
    <p class="ncv-page-subtitle">Manage company accounts and their stakeholders.</p>
  </div>
  <div class="d-flex gap-2">
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm"><i class="bi bi-download"></i> Export</button>
    <a href="{{ route('accounts.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
      <i class="bi bi-plus-lg"></i> New Account
    </a>
  </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total Accounts', 'value'=>'342',    'color'=>'var(--ncv-blue-600)', 'icon'=>'bi-building'],
    ['label'=>'Active',         'value'=>'289',    'color'=>'#10b981',             'icon'=>'bi-check-circle'],
    ['label'=>'Enterprise',     'value'=>'47',     'color'=>'#8b5cf6',             'icon'=>'bi-stars'],
    ['label'=>'Total Revenue',  'value'=>'$8.4M',  'color'=>'#f59e0b',             'icon'=>'bi-currency-dollar'],
  ] as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;display:flex;align-items:center;gap:.875rem;">
      <div style="width:44px;height:44px;border-radius:.75rem;background:{{ $stat['color'] }}15;color:{{ $stat['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
        <i class="bi {{ $stat['icon'] }}"></i>
      </div>
      <div>
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);">{{ $stat['label'] }}</div>
        <div style="font-size:1.4rem;font-weight:800;color:{{ $stat['color'] }};letter-spacing:-.03em;line-height:1.2;">{{ $stat['value'] }}</div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div style="position:relative;min-width:240px;flex:1;">
        <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
        <input type="text" placeholder="Search accounts by name, industry…" class="ncv-input" style="padding-left:2.375rem;" />
      </div>
      <select class="ncv-select" style="width:150px;height:38px;font-size:.82rem;">
        <option>All Industries</option>
        <option>Technology</option>
        <option>Finance</option>
        <option>Healthcare</option>
        <option>Manufacturing</option>
        <option>Retail</option>
      </select>
      <select class="ncv-select" style="width:130px;height:38px;font-size:.82rem;">
        <option>All Sizes</option>
        <option>1–50</option>
        <option>51–200</option>
        <option>201–1000</option>
        <option>1000+</option>
      </select>
      <select class="ncv-select ms-auto" style="width:160px;height:38px;font-size:.82rem;">
        <option>Sort: Name A-Z</option>
        <option>Sort: Revenue ↓</option>
        <option>Sort: Employees ↓</option>
        <option>Sort: Newest</option>
      </select>
    </div>
  </div>
</div>

{{-- Account Cards Grid --}}
<div class="row g-3 mb-4">
  @foreach([
    ['id'=>1,'name'=>'Acme Corporation',   'initials'=>'AC','industry'=>'Technology',   'size'=>'500–1000','revenue'=>'$45M', 'contacts'=>12,'deals'=>3,'color'=>'#2563eb','website'=>'acme.com'],
    ['id'=>2,'name'=>'TechStart Inc',      'initials'=>'TI','industry'=>'Technology',   'size'=>'50–200',  'revenue'=>'$8M',  'contacts'=>5, 'deals'=>2,'color'=>'#10b981','website'=>'techstart.io'],
    ['id'=>3,'name'=>'Globex Inc',         'initials'=>'GI','industry'=>'Manufacturing','size'=>'1000+',   'revenue'=>'$120M','contacts'=>8, 'deals'=>1,'color'=>'#f59e0b','website'=>'globex.com'],
    ['id'=>4,'name'=>'Initech LLC',        'initials'=>'IL','industry'=>'Finance',      'size'=>'50–200',  'revenue'=>'$15M', 'contacts'=>4, 'deals'=>1,'color'=>'#8b5cf6','website'=>'initech.com'],
    ['id'=>5,'name'=>'Umbrella Corp',      'initials'=>'UC','industry'=>'Healthcare',   'size'=>'1000+',   'revenue'=>'$280M','contacts'=>15,'deals'=>4,'color'=>'#ef4444','website'=>'umbrella.co'],
    ['id'=>6,'name'=>'Stark Industries',   'initials'=>'SI','industry'=>'Defense/Tech', 'size'=>'1000+',   'revenue'=>'$550M','contacts'=>7, 'deals'=>2,'color'=>'#06b6d4','website'=>'stark.io'],
    ['id'=>7,'name'=>'Wayne Enterprises',  'initials'=>'WE','industry'=>'Conglomerate', 'size'=>'1000+',   'revenue'=>'$900M','contacts'=>9, 'deals'=>3,'color'=>'#0891b2','website'=>'wayne.com'],
    ['id'=>8,'name'=>'Cyberdyne Systems',  'initials'=>'CS','industry'=>'Technology',   'size'=>'201–1000','revenue'=>'$62M', 'contacts'=>6, 'deals'=>1,'color'=>'#16a34a','website'=>'cyberdyne.net'],
  ] as $acc)
  <div class="col-12 col-sm-6 col-xl-4">
    <div class="ncv-card" style="transition:box-shadow .2s,transform .2s;">
      <div class="ncv-card-body" style="padding:1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:.875rem;">
          <div style="width:50px;height:50px;border-radius:.875rem;background:{{ $acc['color'] }}15;color:{{ $acc['color'] }};display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:800;flex-shrink:0;border:1.5px solid {{ $acc['color'] }}25;">
            {{ $acc['initials'] }}
          </div>
          <div style="flex:1;min-width:0;">
            <a href="{{ route('accounts.show', $acc['id']) }}"
               style="font-weight:700;color:var(--text-primary);text-decoration:none;font-size:.9375rem;display:block;margin-bottom:.15rem;">
              {{ $acc['name'] }}
            </a>
            <div style="font-size:.775rem;color:var(--text-muted);">
              <i class="bi bi-briefcase"></i> {{ $acc['industry'] }}
              &nbsp;·&nbsp;
              <i class="bi bi-people"></i> {{ $acc['size'] }}
            </div>
          </div>
          <div class="ncv-dropdown">
            <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm"
                    onclick="toggleDropdown('accMenu{{ $acc['id'] }}')">
              <i class="bi bi-three-dots"></i>
            </button>
            <div class="ncv-dropdown-menu" id="accMenu{{ $acc['id'] }}">
              <a href="{{ route('accounts.show', $acc['id']) }}" class="ncv-dropdown-item"><i class="bi bi-eye"></i> View</a>
              <a href="{{ route('accounts.edit', $acc['id']) }}" class="ncv-dropdown-item"><i class="bi bi-pencil"></i> Edit</a>
              <div class="ncv-dropdown-divider"></div>
              <button class="ncv-dropdown-item danger"><i class="bi bi-trash"></i> Delete</button>
            </div>
          </div>
        </div>

        <hr class="ncv-divider" style="margin:.875rem 0;" />

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;text-align:center;">
          <div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);">{{ $acc['revenue'] }}</div>
            <div style="font-size:.65rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Revenue</div>
          </div>
          <div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);">{{ $acc['contacts'] }}</div>
            <div style="font-size:.65rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Contacts</div>
          </div>
          <div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);">{{ $acc['deals'] }}</div>
            <div style="font-size:.65rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Open Deals</div>
          </div>
        </div>

        <div style="margin-top:.875rem;padding-top:.875rem;border-top:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
          <a href="https://{{ $acc['website'] }}" target="_blank" rel="noopener"
             style="font-size:.78rem;color:var(--ncv-blue-600);text-decoration:none;">
            <i class="bi bi-globe2"></i> {{ $acc['website'] }}
          </a>
          <a href="{{ route('accounts.show', $acc['id']) }}"
             class="ncv-btn ncv-btn-outline ncv-btn-sm" style="font-size:.75rem;padding:.3rem .75rem;">
            View →
          </a>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Pagination --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Showing <strong style="color:var(--text-primary);">1–8</strong> of <strong style="color:var(--text-primary);">342</strong> accounts</p>
  <nav class="ncv-pagination">
    <a href="#" class="ncv-page-btn" disabled><i class="bi bi-chevron-left" style="font-size:.75rem;"></i></a>
    <a href="#" class="ncv-page-btn active">1</a>
    <a href="#" class="ncv-page-btn">2</a>
    <a href="#" class="ncv-page-btn">3</a>
    <span style="padding:0 .375rem;color:var(--text-muted);font-size:.8rem;">…</span>
    <a href="#" class="ncv-page-btn">43</a>
    <a href="#" class="ncv-page-btn"><i class="bi bi-chevron-right" style="font-size:.75rem;"></i></a>
  </nav>
</div>

@endsection
