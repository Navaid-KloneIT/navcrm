@extends('layouts.app')

@section('title', 'Quotes')
@section('page-title', 'Quotes')

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="ncv-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
  <div>
    <h1 class="ncv-page-title">Quotes</h1>
    <p class="ncv-page-subtitle">Create and manage customer quotes and proposals.</p>
  </div>
  <a href="{{ route('quotes.create') }}" class="ncv-btn ncv-btn-primary ncv-btn-sm">
    <i class="bi bi-plus-lg"></i> New Quote
  </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['label'=>'Total Quotes',   'value'=>'48',     'color'=>'var(--ncv-blue-600)', 'sub'=>'All time'],
    ['label'=>'Draft',          'value'=>'12',     'color'=>'#94a3b8',             'sub'=>'Pending send'],
    ['label'=>'Sent / Open',    'value'=>'21',     'color'=>'#f59e0b',             'sub'=>'Awaiting response'],
    ['label'=>'Accepted',       'value'=>'$380k',  'color'=>'#10b981',             'sub'=>'This quarter'],
  ] as $stat)
  <div class="col-6 col-md-3">
    <div class="ncv-card" style="padding:.875rem 1.125rem;">
      <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.25rem;">{{ $stat['label'] }}</div>
      <div style="font-size:1.6rem;font-weight:800;color:{{ $stat['color'] }};letter-spacing:-.03em;">{{ $stat['value'] }}</div>
      <div style="font-size:.72rem;color:var(--text-muted);">{{ $stat['sub'] }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Filter --}}
<div class="ncv-card mb-3">
  <div class="ncv-card-body" style="padding:.875rem 1.25rem;">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div style="position:relative;min-width:240px;flex:1;">
        <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem;pointer-events:none;"></i>
        <input type="text" placeholder="Search by quote #, account, or contact…" class="ncv-input" style="padding-left:2.375rem;" />
      </div>
      <div class="d-flex gap-1 flex-wrap">
        <button class="ncv-chip active">All</button>
        <button class="ncv-chip">Draft</button>
        <button class="ncv-chip">Sent</button>
        <button class="ncv-chip">Accepted</button>
        <button class="ncv-chip">Rejected</button>
      </div>
      <select class="ncv-select ms-auto" style="width:160px;height:38px;font-size:.82rem;">
        <option>Sort: Newest</option>
        <option>Sort: Oldest</option>
        <option>Sort: Value ↓</option>
        <option>Sort: Expiry Soon</option>
      </select>
    </div>
  </div>
</div>

{{-- Quotes Table --}}
<div class="ncv-table-wrapper">
  <table class="ncv-table">
    <thead>
      <tr>
        <th class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></th>
        <th>Quote #</th>
        <th>Account</th>
        <th>Contact</th>
        <th>Status</th>
        <th>Subtotal</th>
        <th>Total</th>
        <th>Expires</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach([
        ['id'=>1, 'num'=>'QT-0092','account'=>'Acme Corp',     'contact'=>'Sarah Johnson', 'status'=>'Sent',     'sub'=>'$82,000','tax'=>'$7,380', 'total'=>'$89,380', 'exp'=>'Feb 28, 2026','created'=>'Today',       'color'=>'warning'],
        ['id'=>2, 'num'=>'QT-0091','account'=>'TechStart Inc',  'contact'=>'Michael Chen',  'status'=>'Draft',    'sub'=>'$42,000','tax'=>'$3,780', 'total'=>'$45,780', 'exp'=>'Mar 15, 2026','created'=>'Yesterday',   'color'=>'muted'],
        ['id'=>3, 'num'=>'QT-0090','account'=>'Globex Inc',     'contact'=>'Emma Williams', 'status'=>'Accepted', 'sub'=>'$28,500','tax'=>'$2,565', 'total'=>'$31,065', 'exp'=>'Jan 31, 2026','created'=>'Feb 15, 2026','color'=>'success'],
        ['id'=>4, 'num'=>'QT-0089','account'=>'Initech LLC',    'contact'=>'James Rodriguez','status'=>'Sent',    'sub'=>'$18,750','tax'=>'$1,688', 'total'=>'$20,438', 'exp'=>'Mar 1, 2026', 'created'=>'Feb 14, 2026','color'=>'warning'],
        ['id'=>5, 'num'=>'QT-0088','account'=>'Umbrella Corp',  'contact'=>'Olivia Brown',  'status'=>'Rejected', 'sub'=>'$65,000','tax'=>'$5,850', 'total'=>'$70,850', 'exp'=>'Expired',     'created'=>'Feb 10, 2026','color'=>'danger'],
        ['id'=>6, 'num'=>'QT-0087','account'=>'Stark Industries','contact'=>'David Kim',    'status'=>'Draft',    'sub'=>'$95,000','tax'=>'$8,550', 'total'=>'$103,550','exp'=>'Mar 30, 2026','created'=>'Feb 8, 2026', 'color'=>'muted'],
        ['id'=>7, 'num'=>'QT-0086','account'=>'Wayne Ent.',     'contact'=>'Sophia Martinez','status'=>'Accepted','sub'=>'$32,000','tax'=>'$2,880', 'total'=>'$34,880', 'exp'=>'Jan 15, 2026','created'=>'Jan 28, 2026','color'=>'success'],
      ] as $quote)
      <tr>
        <td class="col-check"><input type="checkbox" style="accent-color:#2563eb;" /></td>
        <td>
          <a href="{{ route('quotes.show', $quote['id']) }}"
             style="font-weight:800;color:var(--ncv-blue-600);text-decoration:none;font-size:.875rem;font-family:monospace;">
            {{ $quote['num'] }}
          </a>
        </td>
        <td style="font-size:.83rem;font-weight:600;color:var(--text-primary);">{{ $quote['account'] }}</td>
        <td style="font-size:.82rem;color:var(--text-muted);">{{ $quote['contact'] }}</td>
        <td>
          <span class="ncv-badge ncv-badge-{{ $quote['color'] }}"><span class="dot"></span>{{ $quote['status'] }}</span>
        </td>
        <td style="font-size:.82rem;color:var(--text-muted);">{{ $quote['sub'] }}</td>
        <td style="font-weight:800;color:var(--text-primary);">{{ $quote['total'] }}</td>
        <td>
          <span style="font-size:.78rem;color:{{ $quote['exp'] === 'Expired' ? '#ef4444' : 'var(--text-muted)' }};font-weight:{{ $quote['exp'] === 'Expired' ? '700' : '400' }};">
            {{ $quote['exp'] }}
          </span>
        </td>
        <td style="font-size:.775rem;color:var(--text-muted);">{{ $quote['created'] }}</td>
        <td>
          <div class="d-flex gap-1">
            <a href="{{ route('quotes.show', $quote['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="View"><i class="bi bi-eye" style="font-size:.8rem;"></i></a>
            @if(in_array($quote['status'], ['Draft','Sent']))
            <a href="{{ route('quotes.edit', $quote['id']) }}" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Edit"><i class="bi bi-pencil" style="font-size:.8rem;"></i></a>
            @endif
            <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" title="Download PDF"><i class="bi bi-file-pdf" style="font-size:.8rem;color:#ef4444;"></i></button>
            <div class="ncv-dropdown">
              <button class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm" onclick="toggleDropdown('qMenu{{ $quote['id'] }}')"><i class="bi bi-three-dots" style="font-size:.8rem;"></i></button>
              <div class="ncv-dropdown-menu" id="qMenu{{ $quote['id'] }}">
                <a href="{{ route('quotes.show', $quote['id']) }}" class="ncv-dropdown-item"><i class="bi bi-eye"></i> View</a>
                <button class="ncv-dropdown-item"><i class="bi bi-copy"></i> Duplicate</button>
                <button class="ncv-dropdown-item"><i class="bi bi-send"></i> Send to Client</button>
                <button class="ncv-dropdown-item"><i class="bi bi-file-pdf"></i> Download PDF</button>
                <div class="ncv-dropdown-divider"></div>
                <button class="ncv-dropdown-item danger"><i class="bi bi-trash"></i> Delete</button>
              </div>
            </div>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
  <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Showing <strong style="color:var(--text-primary);">1–7</strong> of <strong style="color:var(--text-primary);">48</strong> quotes</p>
  <nav class="ncv-pagination">
    <a href="#" class="ncv-page-btn" disabled><i class="bi bi-chevron-left" style="font-size:.75rem;"></i></a>
    <a href="#" class="ncv-page-btn active">1</a>
    <a href="#" class="ncv-page-btn">2</a>
    <a href="#" class="ncv-page-btn">3</a>
    <a href="#" class="ncv-page-btn"><i class="bi bi-chevron-right" style="font-size:.75rem;"></i></a>
  </nav>
</div>

@endsection
