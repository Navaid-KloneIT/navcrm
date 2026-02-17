@extends('layouts.app')

@section('title', 'Quote ' . ($quote->number ?? 'QT-0092'))
@section('page-title', 'Quote Details')

@section('breadcrumb-items')
  <a href="{{ route('quotes.index') }}" style="color:inherit;text-decoration:none;">Quotes</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div style="display:flex;align-items:center;gap:.875rem;">
    <h1 class="ncv-page-title mb-0">{{ $quote->number ?? 'QT-0092' }}</h1>
    <span class="ncv-badge ncv-badge-warning" style="font-size:.8rem;"><span class="dot"></span>Sent</span>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('quotes.edit', $quote->id ?? 1) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm" onclick="window.print()">
      <i class="bi bi-file-pdf" style="color:#ef4444;"></i> PDF
    </button>
    <button class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-send"></i> Resend
    </button>
    <button class="ncv-btn ncv-btn-sm" style="background:#10b981;color:#fff;border:none;">
      <i class="bi bi-check-circle-fill"></i> Mark Accepted
    </button>
    <button class="ncv-btn ncv-btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;">
      <i class="bi bi-x-circle"></i> Mark Rejected
    </button>
  </div>
</div>

<div class="row g-3">

  {{-- LEFT: Quote Preview --}}
  <div class="col-12 col-lg-8">
    <div class="ncv-card" id="quotePrintArea">
      <div class="ncv-card-body" style="padding:2rem;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
          <div>
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
              <div style="width:42px;height:42px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
              </div>
              <div>
                <div style="font-size:1.25rem;font-weight:800;color:#0d1f4e;letter-spacing:-.02em;">NavCRM</div>
                <div style="font-size:.72rem;color:var(--text-muted);">CRM Solutions Inc.</div>
              </div>
            </div>
            <div style="font-size:.82rem;color:var(--text-muted);line-height:1.6;">
              1600 Amphitheatre Pkwy<br />
              Mountain View, CA 94043<br />
              hello@navcrm.io · (555) 100-2000
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:1.5rem;font-weight:800;color:#0d1f4e;letter-spacing:-.02em;">QUOTE</div>
            <div style="font-size:.875rem;color:var(--text-muted);margin-top:.25rem;"># {{ $quote->number ?? 'QT-0092' }}</div>
            <div style="margin-top:.875rem;font-size:.82rem;color:var(--text-muted);line-height:1.7;">
              <strong style="color:var(--text-primary);">Date:</strong> {{ $quote->created_at?->format('M d, Y') ?? 'Feb 18, 2026' }}<br />
              <strong style="color:var(--text-primary);">Valid Until:</strong> <span style="color:#d97706;font-weight:600;">Feb 28, 2026</span><br />
              <strong style="color:var(--text-primary);">Status:</strong> <span class="ncv-badge ncv-badge-warning" style="font-size:.7rem;"><span class="dot"></span>Sent</span>
            </div>
          </div>
        </div>

        {{-- Bill To --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem;padding:1.25rem;background:#f8faff;border-radius:.75rem;border:1px solid var(--border-color);">
          <div>
            <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.5rem;">Bill To</div>
            <div style="font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:.2rem;">Acme Corporation</div>
            <div style="font-size:.82rem;color:var(--text-muted);line-height:1.65;">
              Att: Sarah Johnson (VP Sales)<br />
              1 Acme Plaza, Suite 800<br />
              New York, NY 10001<br />
              sarah@acme.com
            </div>
          </div>
          <div>
            <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.5rem;">Linked Opportunity</div>
            <div style="font-size:.875rem;color:var(--text-primary);">
              <a href="{{ route('opportunities.show', 1) }}" style="font-weight:600;color:var(--ncv-blue-600);text-decoration:none;">Acme Enterprise Renewal</a>
            </div>
            <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin:1rem 0 .5rem;">Prepared By</div>
            <div style="font-size:.875rem;color:var(--text-secondary);">{{ auth()->user()?->name ?? 'John Smith' }}</div>
          </div>
        </div>

        {{-- Line Items Table --}}
        <table style="width:100%;border-collapse:collapse;margin-bottom:1.5rem;">
          <thead>
            <tr style="background:#0d1f4e;color:#fff;">
              <th style="padding:.75rem 1rem;font-size:.75rem;font-weight:700;text-align:left;border-radius:.5rem 0 0 0;">#</th>
              <th style="padding:.75rem 1rem;font-size:.75rem;font-weight:700;text-align:left;">Product / Description</th>
              <th style="padding:.75rem 1rem;font-size:.75rem;font-weight:700;text-align:right;">Qty</th>
              <th style="padding:.75rem 1rem;font-size:.75rem;font-weight:700;text-align:right;">Unit Price</th>
              <th style="padding:.75rem 1rem;font-size:.75rem;font-weight:700;text-align:right;">Disc.</th>
              <th style="padding:.75rem 1rem;font-size:.75rem;font-weight:700;text-align:right;border-radius:0 .5rem 0 0;">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach([
              ['num'=>1,'name'=>'NavCRM Enterprise License','desc'=>'Full CRM suite — unlimited users','qty'=>12,'unit'=>'Month','price'=>2500.00,'disc'=>20,'total'=>24000.00],
              ['num'=>2,'name'=>'API Access Module',         'desc'=>'REST API — 100k calls/day',       'qty'=>12,'unit'=>'Month','price'=>199.00, 'disc'=>20,'total'=>1910.40],
              ['num'=>3,'name'=>'Onboarding Service',        'desc'=>'4-week guided onboarding program','qty'=>1, 'unit'=>'Each', 'price'=>4500.00,'disc'=>0, 'total'=>4500.00],
            ] as $line)
            <tr style="border-bottom:1px solid #e8edf5;">
              <td style="padding:.75rem 1rem;font-size:.82rem;color:var(--text-muted);">{{ $line['num'] }}</td>
              <td style="padding:.75rem 1rem;">
                <div style="font-weight:700;font-size:.875rem;color:var(--text-primary);">{{ $line['name'] }}</div>
                <div style="font-size:.75rem;color:var(--text-muted);">{{ $line['desc'] }}</div>
              </td>
              <td style="padding:.75rem 1rem;text-align:right;font-size:.875rem;">{{ $line['qty'] }} {{ $line['unit'] }}{{ $line['qty'] > 1 ? 's' : '' }}</td>
              <td style="padding:.75rem 1rem;text-align:right;font-size:.875rem;">${{ number_format($line['price'], 2) }}</td>
              <td style="padding:.75rem 1rem;text-align:right;font-size:.875rem;color:#10b981;font-weight:600;">{{ $line['disc'] > 0 ? $line['disc'].'%' : '—' }}</td>
              <td style="padding:.75rem 1rem;text-align:right;font-weight:800;color:var(--text-primary);">${{ number_format($line['total'], 2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        {{-- Totals --}}
        <div style="display:flex;justify-content:flex-end;margin-bottom:2rem;">
          <table style="width:280px;font-size:.875rem;">
            <tr>
              <td style="padding:.35rem 0;color:var(--text-muted);">Subtotal</td>
              <td style="text-align:right;font-weight:600;">$30,410.40</td>
            </tr>
            <tr>
              <td style="padding:.35rem 0;color:var(--text-muted);">Discount Applied</td>
              <td style="text-align:right;color:#10b981;font-weight:600;">-$5,902.40</td>
            </tr>
            <tr>
              <td style="padding:.35rem 0;color:var(--text-muted);">Tax (9%)</td>
              <td style="text-align:right;font-weight:600;">$2,736.94</td>
            </tr>
            <tr>
              <td colspan="2"><hr style="border-top:2px solid #e8edf5;margin:.5rem 0;" /></td>
            </tr>
            <tr>
              <td style="font-size:1rem;font-weight:800;color:var(--text-primary);">Total</td>
              <td style="text-align:right;font-size:1.25rem;font-weight:800;color:#0d1f4e;">$33,147.34</td>
            </tr>
          </table>
        </div>

        {{-- Notes --}}
        <div style="border-top:2px solid #e8edf5;padding-top:1.25rem;">
          <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.5rem;">Terms & Notes</div>
          <p style="font-size:.82rem;color:var(--text-muted);line-height:1.7;margin:0;">
            Payment due within 30 days of acceptance. Prices are valid until February 28, 2026.
            Volume discounts available for multi-year contracts. All prices in USD.
            This quote supersedes any previous quotations.
          </p>
        </div>

      </div>
    </div>
  </div>

  {{-- RIGHT: Actions & Info --}}
  <div class="col-12 col-lg-4">

    {{-- Quote Summary --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-info-circle me-2" style="color:var(--ncv-blue-500);"></i>Quote Summary</h6>
      </div>
      <div class="ncv-card-body">
        @foreach([
          ['l'=>'Quote #',      'v'=>'QT-0092'],
          ['l'=>'Account',      'v'=>'Acme Corporation'],
          ['l'=>'Contact',      'v'=>'Sarah Johnson'],
          ['l'=>'Opportunity',  'v'=>'Enterprise Renewal'],
          ['l'=>'Created',      'v'=>'Feb 18, 2026'],
          ['l'=>'Valid Until',  'v'=>'Feb 28, 2026'],
          ['l'=>'Status',       'v'=>'Sent'],
          ['l'=>'Items',        'v'=>'3'],
          ['l'=>'Total',        'v'=>'$33,147.34'],
        ] as $row)
        <div style="display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--border-color);font-size:.83rem;">
          <span style="color:var(--text-muted);font-weight:600;">{{ $row['l'] }}</span>
          <span style="font-weight:{{ $row['l'] === 'Total' ? '800' : '500' }};color:{{ $row['l'] === 'Total' ? 'var(--ncv-blue-700)' : 'var(--text-secondary)' }};font-size:{{ $row['l'] === 'Total' ? '.95rem' : '.83rem' }};">{{ $row['v'] }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Status Timeline --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-clock-history me-2" style="color:var(--ncv-blue-500);"></i>Quote History</h6>
      </div>
      <div class="ncv-card-body">
        <ul class="ncv-timeline" style="padding-left:0;">
          @foreach([
            ['icon'=>'bi-send-fill',           'bg'=>'#dbeafe','color'=>'#2563eb','title'=>'Quote sent to client',     'time'=>'Feb 18, 2026 · 10:30 AM'],
            ['icon'=>'bi-pencil-fill',         'bg'=>'#fef3c7','color'=>'#d97706','title'=>'Quote revised (v2)',       'time'=>'Feb 17, 2026 · 3:15 PM'],
            ['icon'=>'bi-file-earmark-plus',   'bg'=>'#d1fae5','color'=>'#059669','title'=>'Quote created',           'time'=>'Feb 16, 2026 · 11:00 AM'],
          ] as $evt)
          <li class="ncv-timeline-item" style="padding-bottom:.875rem;">
            <div class="ncv-timeline-icon" style="background:{{ $evt['bg'] }};color:{{ $evt['color'] }};width:32px;height:32px;"><i class="bi {{ $evt['icon'] }}" style="font-size:.75rem;"></i></div>
            <div class="ncv-timeline-body">
              <div style="font-size:.82rem;font-weight:600;color:var(--text-primary);">{{ $evt['title'] }}</div>
              <div style="font-size:.72rem;color:var(--text-muted);">{{ $evt['time'] }}</div>
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </div>

    {{-- Actions --}}
    <div class="ncv-card">
      <div class="ncv-card-header">
        <h6 class="ncv-card-title"><i class="bi bi-lightning me-2" style="color:var(--ncv-blue-500);"></i>Actions</h6>
      </div>
      <div class="ncv-card-body">
        <div style="display:flex;flex-direction:column;gap:.5rem;">
          <button class="ncv-btn ncv-btn-primary" style="justify-content:flex-start;gap:.625rem;">
            <i class="bi bi-check-circle-fill" style="font-size:1rem;"></i> Mark as Accepted
          </button>
          <button class="ncv-btn ncv-btn-outline" style="justify-content:flex-start;gap:.625rem;">
            <i class="bi bi-send" style="font-size:1rem;"></i> Resend Quote
          </button>
          <button class="ncv-btn ncv-btn-outline" style="justify-content:flex-start;gap:.625rem;" onclick="window.print()">
            <i class="bi bi-file-pdf" style="font-size:1rem;color:#ef4444;"></i> Download PDF
          </button>
          <button class="ncv-btn ncv-btn-outline" style="justify-content:flex-start;gap:.625rem;">
            <i class="bi bi-copy" style="font-size:1rem;"></i> Duplicate Quote
          </button>
          <button class="ncv-btn ncv-btn-outline" style="justify-content:flex-start;gap:.625rem;color:#ef4444;border-color:#fca5a5;">
            <i class="bi bi-x-circle" style="font-size:1rem;"></i> Mark as Rejected
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection
