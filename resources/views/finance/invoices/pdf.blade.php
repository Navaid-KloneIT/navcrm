<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice {{ $invoice->invoice_number }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; }

    .page { padding: 40px 48px; max-width: 800px; margin: 0 auto; }

    .header { display: table; width: 100%; margin-bottom: 32px; }
    .header-left  { display: table-cell; vertical-align: top; width: 55%; }
    .header-right { display: table-cell; vertical-align: top; width: 45%; text-align: right; }
    .company-name { font-size: 22px; font-weight: 700; color: #0d1f4e; margin-bottom: 2px; }
    .invoice-label { font-size: 28px; font-weight: 900; color: #1e3a8f; letter-spacing: -.03em; }
    .invoice-number { font-size: 14px; color: #64748b; margin-top: 2px; }
    .meta-line { font-size: 11px; color: #64748b; margin-top: 3px; }
    .meta-label { font-weight: 700; }

    .divider { border: none; border-top: 2px solid #e2e8f0; margin: 24px 0; }

    .addresses { display: table; width: 100%; margin-bottom: 24px; }
    .address-block { display: table-cell; vertical-align: top; width: 50%; }
    .address-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: 6px; }
    .address-name { font-size: 13px; font-weight: 700; color: #0f172a; }
    .address-detail { font-size: 11px; color: #64748b; margin-top: 2px; }

    table.line-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.line-items thead tr { background: #0d1f4e; }
    table.line-items thead th { color: #fff; padding: 8px 10px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; text-align: left; }
    table.line-items thead th:last-child { text-align: right; }
    table.line-items thead th.right { text-align: right; }
    table.line-items tbody tr { border-bottom: 1px solid #e2e8f0; }
    table.line-items tbody tr:nth-child(even) { background: #f8fafc; }
    table.line-items tbody td { padding: 7px 10px; font-size: 11px; vertical-align: top; }
    table.line-items tbody td.right { text-align: right; }
    table.line-items tbody td .desc-secondary { font-size: 10px; color: #94a3b8; margin-top: 1px; }

    .totals-wrap { display: table; width: 100%; }
    .totals-spacer { display: table-cell; width: 55%; }
    .totals-table-cell { display: table-cell; width: 45%; }
    table.totals { width: 100%; border-collapse: collapse; }
    table.totals td { padding: 4px 8px; font-size: 11px; }
    table.totals td:last-child { text-align: right; font-weight: 600; }
    table.totals .grand { border-top: 2px solid #0d1f4e; }
    table.totals .grand td { font-size: 14px; font-weight: 800; color: #0d1f4e; padding-top: 6px; }
    table.totals .amount-due td { font-size: 13px; font-weight: 800; color: #ef4444; }

    .footer-section { margin-top: 28px; display: table; width: 100%; }
    .footer-col { display: table-cell; vertical-align: top; width: 50%; padding-right: 16px; }
    .footer-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: 4px; }
    .footer-text  { font-size: 11px; color: #64748b; }

    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .status-paid { background: #dcfce7; color: #15803d; }
    .status-partial { background: #fef9c3; color: #92400e; }
    .status-overdue { background: #fee2e2; color: #b91c1c; }
    .status-sent { background: #dbeafe; color: #1d4ed8; }
    .status-draft { background: #f1f5f9; color: #475569; }
  </style>
</head>
<body>
<div class="page">

  {{-- Header --}}
  <div class="header">
    <div class="header-left">
      <div class="company-name">{{ config('app.name', 'NavCRM') }}</div>
    </div>
    <div class="header-right">
      <div class="invoice-label">INVOICE</div>
      <div class="invoice-number">{{ $invoice->invoice_number }}</div>
      <div class="meta-line">
        <span class="meta-label">Issue Date:</span> {{ $invoice->issue_date?->format('M j, Y') }}
      </div>
      @if($invoice->due_date)
      <div class="meta-line">
        <span class="meta-label">Due Date:</span> {{ $invoice->due_date->format('M j, Y') }}
      </div>
      @endif
      <div style="margin-top:6px;">
        @php
          $statusClass = match($invoice->status->value) {
              'paid'    => 'status-paid',
              'partial' => 'status-partial',
              'overdue' => 'status-overdue',
              'sent'    => 'status-sent',
              default   => 'status-draft',
          };
        @endphp
        <span class="status-badge {{ $statusClass }}">{{ $invoice->status->label() }}</span>
      </div>
    </div>
  </div>

  <hr class="divider">

  {{-- Addresses --}}
  <div class="addresses">
    <div class="address-block">
      <div class="address-label">From</div>
      <div class="address-name">{{ config('app.name', 'NavCRM') }}</div>
    </div>
    <div class="address-block">
      <div class="address-label">Bill To</div>
      @if($invoice->contact)
        <div class="address-name">{{ $invoice->contact->first_name }} {{ $invoice->contact->last_name }}</div>
      @endif
      @if($invoice->account)
        <div class="address-detail">{{ $invoice->account->name }}</div>
        @if($invoice->account->email)
          <div class="address-detail">{{ $invoice->account->email }}</div>
        @endif
      @endif
    </div>
  </div>

  <hr class="divider">

  {{-- Line Items --}}
  <table class="line-items">
    <thead>
      <tr>
        <th style="width:40%;">Description</th>
        <th class="right" style="width:12%;">Qty</th>
        <th class="right" style="width:18%;">Unit Price</th>
        <th class="right" style="width:12%;">Disc%</th>
        <th class="right" style="width:18%;">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->lineItems as $item)
      <tr>
        <td>
          <div>{{ $item->description }}</div>
          @if($item->product)
            <div class="desc-secondary">{{ $item->product->name }}</div>
          @endif
        </td>
        <td class="right">{{ number_format($item->quantity, 2) }}</td>
        <td class="right">${{ number_format($item->unit_price, 2) }}</td>
        <td class="right">{{ $item->discount_percent > 0 ? number_format($item->discount_percent, 1).'%' : '—' }}</td>
        <td class="right">${{ number_format($item->subtotal, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="totals-wrap">
    <div class="totals-spacer"></div>
    <div class="totals-table-cell">
      <table class="totals">
        <tr>
          <td style="color:#64748b;">Subtotal</td>
          <td>${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
        <tr>
          <td style="color:#64748b;">Discount</td>
          <td style="color:#ef4444;">−${{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
          <td style="color:#64748b;">Tax ({{ $invoice->tax_rate }}%)</td>
          <td>${{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr class="grand">
          <td>Total</td>
          <td>${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @if($invoice->amount_paid > 0)
        <tr>
          <td style="color:#15803d;">Amount Paid</td>
          <td style="color:#15803d;">−${{ number_format($invoice->amount_paid, 2) }}</td>
        </tr>
        <tr class="amount-due">
          <td>Amount Due</td>
          <td>${{ number_format($invoice->amount_due, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @endif
      </table>
    </div>
  </div>

  {{-- Footer --}}
  @if($invoice->notes || $invoice->terms)
  <hr class="divider">
  <div class="footer-section">
    @if($invoice->notes)
    <div class="footer-col">
      <div class="footer-label">Notes</div>
      <div class="footer-text">{{ $invoice->notes }}</div>
    </div>
    @endif
    @if($invoice->terms)
    <div class="footer-col">
      <div class="footer-label">Payment Terms</div>
      <div class="footer-text">{{ $invoice->terms }}</div>
    </div>
    @endif
  </div>
  @endif

</div>
</body>
</html>
