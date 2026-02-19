@extends('layouts.app')

@section('title', $invoice->invoice_number)
@section('page-title', $invoice->invoice_number)

@section('breadcrumb-items')
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
  <a href="{{ route('finance.invoices.index') }}" class="ncv-breadcrumb-item">Invoices</a>
  <span class="ncv-breadcrumb-sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
@endsection

@push('styles')
<style>
  .inv-preview { font-size:.88rem; }
  .inv-header  { background:linear-gradient(135deg,#0d1f4e,#1e3a8f); color:#fff; border-radius:.75rem; padding:1.5rem; margin-bottom:1.5rem; }
  .inv-table th { background:#f1f5f9; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:.5rem .75rem; }
  .inv-table td { padding:.5rem .75rem; font-size:.85rem; }
  .inv-totals td { padding:.3rem .5rem; font-size:.88rem; }
  .inv-totals .grand-total td { font-size:1rem; font-weight:800; border-top:2px solid var(--border-color); }
  .payment-row { padding:.5rem 0; border-bottom:1px solid var(--border-color); }
  .payment-row:last-child { border-bottom:none; }
</style>
@endpush

@section('content')

{{-- Top action bar --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <span style="font-size:1.1rem;font-weight:800;">{{ $invoice->invoice_number }}</span>
    <span class="ncv-badge bg-{{ $invoice->status->color() }}-subtle text-{{ $invoice->status->color() }}" style="font-size:.8rem;">
      {{ $invoice->status->label() }}
    </span>
    @if($invoice->is_recurring)
      <span class="ncv-badge" style="background:#f5f3ff;color:#7c3aed;font-size:.78rem;">
        <i class="bi bi-arrow-repeat me-1"></i>{{ ucfirst($invoice->recurrence) }}
      </span>
    @endif
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('finance.invoices.edit', $invoice) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm">
      <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <a href="{{ route('finance.invoices.pdf', $invoice) }}" class="ncv-btn ncv-btn-outline ncv-btn-sm" target="_blank">
      <i class="bi bi-file-earmark-pdf me-1"></i>PDF
    </a>
    @if($invoice->is_recurring && $invoice->next_invoice_date)
    <form method="POST" action="{{ route('finance.invoices.recurring', $invoice) }}" class="d-inline">
      @csrf
      <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm"
              onclick="return confirm('Generate next recurring invoice?')">
        <i class="bi bi-arrow-repeat me-1"></i>Generate Next
      </button>
    </form>
    @endif
  </div>
</div>

<div class="row g-3">

  {{-- Left: Invoice preview --}}
  <div class="col-lg-8">
    <div class="ncv-card">
      <div class="ncv-card-body inv-preview">

        {{-- Invoice header --}}
        <div class="inv-header">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <div style="font-size:1.4rem;font-weight:900;letter-spacing:-.03em;">INVOICE</div>
              <div style="font-size:.88rem;opacity:.8;margin-top:.25rem;">{{ $invoice->invoice_number }}</div>
            </div>
            <div style="text-align:right;">
              @if($invoice->account)
              <div style="font-weight:700;font-size:1rem;">{{ $invoice->account->name }}</div>
              @endif
              <div style="font-size:.8rem;opacity:.75;margin-top:.25rem;">
                Issue: {{ $invoice->issue_date?->format('M j, Y') }}
              </div>
              @if($invoice->due_date)
              <div style="font-size:.8rem;opacity:.75;">
                Due: {{ $invoice->due_date->format('M j, Y') }}
              </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Bill To --}}
        @if($invoice->account || $invoice->contact)
        <div class="mb-3">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.4rem;">Bill To</div>
          @if($invoice->contact)
            <div style="font-weight:600;">{{ $invoice->contact->first_name }} {{ $invoice->contact->last_name }}</div>
          @endif
          @if($invoice->account)
            <div style="color:var(--text-muted);">{{ $invoice->account->name }}</div>
          @endif
          @if($invoice->account?->email)
            <div style="font-size:.82rem;color:var(--text-muted);">{{ $invoice->account->email }}</div>
          @endif
        </div>
        @endif

        {{-- Line Items --}}
        <table class="inv-table w-100 mb-3" style="border-collapse:collapse;">
          <thead>
            <tr>
              <th style="border-radius:.4rem 0 0 .4rem;">Description</th>
              <th style="text-align:right;">Qty</th>
              <th style="text-align:right;">Unit Price</th>
              <th style="text-align:right;">Disc%</th>
              <th style="text-align:right;border-radius:0 .4rem .4rem 0;">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->lineItems as $item)
            <tr style="border-bottom:1px solid var(--border-color);">
              <td>
                <div style="font-weight:600;">{{ $item->description }}</div>
                @if($item->product)
                  <div style="font-size:.75rem;color:var(--text-muted);">{{ $item->product->name }}</div>
                @endif
              </td>
              <td style="text-align:right;">{{ number_format($item->quantity, 2) }}</td>
              <td style="text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
              <td style="text-align:right;">{{ $item->discount_percent > 0 ? number_format($item->discount_percent, 1).'%' : '—' }}</td>
              <td style="text-align:right;font-weight:700;">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        {{-- Totals --}}
        <div class="d-flex justify-content-end">
          <table class="inv-totals" style="width:280px;">
            <tr>
              <td style="color:var(--text-muted);">Subtotal</td>
              <td style="text-align:right;">${{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
              <td style="color:var(--text-muted);">Discount</td>
              <td style="text-align:right;color:#ef4444;">−${{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
              <td style="color:var(--text-muted);">Tax ({{ $invoice->tax_rate }}%)</td>
              <td style="text-align:right;">${{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            <tr class="grand-total">
              <td>Total</td>
              <td style="text-align:right;">${{ number_format($invoice->total, 2) }}</td>
            </tr>
            @if($invoice->amount_paid > 0)
            <tr>
              <td style="color:#10b981;">Amount Paid</td>
              <td style="text-align:right;color:#10b981;">−${{ number_format($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr>
              <td style="font-weight:800;color:{{ $invoice->amount_due > 0 ? '#ef4444' : '#10b981' }};">Amount Due</td>
              <td style="text-align:right;font-weight:800;color:{{ $invoice->amount_due > 0 ? '#ef4444' : '#10b981' }};">
                ${{ number_format($invoice->amount_due, 2) }}
              </td>
            </tr>
            @endif
          </table>
        </div>

        {{-- Notes / Terms --}}
        @if($invoice->notes || $invoice->terms)
        <div class="row g-3 mt-2" style="border-top:1px solid var(--border-color);padding-top:1rem;">
          @if($invoice->notes)
          <div class="col-md-6">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Notes</div>
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">{{ $invoice->notes }}</p>
          </div>
          @endif
          @if($invoice->terms)
          <div class="col-md-6">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.3rem;">Payment Terms</div>
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;">{{ $invoice->terms }}</p>
          </div>
          @endif
        </div>
        @endif

      </div>
    </div>
  </div>

  {{-- Right sidebar --}}
  <div class="col-lg-4">

    {{-- Summary --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header"><span class="ncv-card-title">Summary</span></div>
      <div class="ncv-card-body">
        <table class="w-100" style="font-size:.82rem;">
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Invoice #</td>
            <td style="text-align:right;font-weight:600;">{{ $invoice->invoice_number }}</td>
          </tr>
          @if($invoice->quote)
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Quote</td>
            <td style="text-align:right;">
              <a href="{{ route('quotes.show', $invoice->quote) }}" style="color:var(--accent-blue);text-decoration:none;">
                {{ $invoice->quote->quote_number }}
              </a>
            </td>
          </tr>
          @endif
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Issue Date</td>
            <td style="text-align:right;">{{ $invoice->issue_date?->format('M j, Y') }}</td>
          </tr>
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Due Date</td>
            <td style="text-align:right;{{ $invoice->status === \App\Enums\InvoiceStatus::Overdue ? 'color:#ef4444;font-weight:600;' : '' }}">
              {{ $invoice->due_date?->format('M j, Y') ?? '—' }}
            </td>
          </tr>
          @if($invoice->opportunity)
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Opportunity</td>
            <td style="text-align:right;">
              <a href="{{ route('opportunities.show', $invoice->opportunity) }}" style="color:var(--accent-blue);text-decoration:none;">
                {{ $invoice->opportunity->name }}
              </a>
            </td>
          </tr>
          @endif
          <tr>
            <td style="color:var(--text-muted);padding:.3rem 0;">Owner</td>
            <td style="text-align:right;">{{ $invoice->owner?->name ?? '—' }}</td>
          </tr>
          <tr style="border-top:1px solid var(--border-color);">
            <td style="padding:.5rem 0;font-weight:700;">Total</td>
            <td style="text-align:right;font-weight:800;font-size:1rem;">${{ number_format($invoice->total, 2) }}</td>
          </tr>
          @if($invoice->amount_paid > 0)
          <tr>
            <td style="color:#10b981;padding:.3rem 0;">Paid</td>
            <td style="text-align:right;color:#10b981;">${{ number_format($invoice->amount_paid, 2) }}</td>
          </tr>
          <tr>
            <td style="color:{{ $invoice->amount_due > 0 ? '#ef4444' : '#10b981' }};padding:.3rem 0;font-weight:700;">Due</td>
            <td style="text-align:right;font-weight:800;color:{{ $invoice->amount_due > 0 ? '#ef4444' : '#10b981' }};">
              ${{ number_format($invoice->amount_due, 2) }}
            </td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    {{-- Payments --}}
    <div class="ncv-card mb-3">
      <div class="ncv-card-header"><span class="ncv-card-title">Payments</span></div>
      <div class="ncv-card-body">

        @forelse($invoice->payments as $payment)
        <div class="payment-row">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <div style="font-weight:700;font-size:.88rem;">${{ number_format($payment->amount, 2) }}</div>
            <span class="ncv-badge bg-{{ $payment->status->color() }}-subtle text-{{ $payment->status->color() }}" style="font-size:.72rem;">
              {{ $payment->status->label() }}
            </span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <div style="font-size:.78rem;color:var(--text-muted);">
              {{ $payment->method->label() }}
              @if($payment->reference_number) · {{ $payment->reference_number }} @endif
              · {{ $payment->payment_date->format('M j, Y') }}
            </div>
            <form method="POST" action="{{ route('finance.invoices.payments.destroy', [$invoice, $payment]) }}"
                  onsubmit="return confirm('Remove this payment?')">
              @csrf @method('DELETE')
              <button type="submit" class="ncv-btn ncv-btn-ghost ncv-btn-icon ncv-btn-sm text-danger" style="width:22px;height:22px;">
                <i class="bi bi-trash" style="font-size:.7rem;"></i>
              </button>
            </form>
          </div>
        </div>
        @empty
        <p style="font-size:.82rem;color:var(--text-muted);text-align:center;padding:.5rem 0;">No payments yet.</p>
        @endforelse

        {{-- Record Payment form --}}
        @if(!in_array($invoice->status->value, ['paid','cancelled','void']))
        <div style="border-top:1px solid var(--border-color);margin-top:.75rem;padding-top:.75rem;">
          <div style="font-size:.8rem;font-weight:700;margin-bottom:.5rem;">Record Payment</div>
          <form method="POST" action="{{ route('finance.invoices.payments.store', $invoice) }}">
            @csrf
            <div class="mb-2">
              <input type="number" name="amount"
                     value="{{ $invoice->amount_due > 0 ? number_format($invoice->amount_due, 2, '.', '') : '' }}"
                     placeholder="Amount" min="0.01" step="0.01"
                     class="ncv-input" style="font-size:.82rem;" required>
            </div>
            <div class="mb-2">
              <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}"
                     class="ncv-input" style="font-size:.82rem;" required>
            </div>
            <div class="mb-2">
              <select name="method" class="ncv-select" style="font-size:.82rem;" required>
                @foreach(\App\Enums\PaymentMethod::cases() as $m)
                  <option value="{{ $m->value }}">{{ $m->label() }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-2">
              <select name="status" class="ncv-select" style="font-size:.82rem;" required>
                @foreach(\App\Enums\PaymentStatus::cases() as $ps)
                  <option value="{{ $ps->value }}" {{ $ps->value === 'completed' ? 'selected' : '' }}>{{ $ps->label() }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-2">
              <input type="text" name="reference_number" placeholder="Ref / Transaction ID"
                     class="ncv-input" style="font-size:.82rem;">
            </div>
            <button type="submit" class="ncv-btn ncv-btn-primary ncv-btn-sm w-100">
              <i class="bi bi-check-lg me-1"></i>Record Payment
            </button>
          </form>
        </div>
        @endif

      </div>
    </div>

    {{-- Recurring info --}}
    @if($invoice->is_recurring)
    <div class="ncv-card mb-3">
      <div class="ncv-card-header"><span class="ncv-card-title">Recurring Schedule</span></div>
      <div class="ncv-card-body" style="font-size:.82rem;">
        <div class="d-flex justify-content-between mb-2">
          <span style="color:var(--text-muted);">Interval</span>
          <span style="font-weight:600;">{{ ucfirst($invoice->recurrence) }}</span>
        </div>
        @if($invoice->next_invoice_date)
        <div class="d-flex justify-content-between mb-2">
          <span style="color:var(--text-muted);">Next Invoice</span>
          <span style="font-weight:600;">{{ $invoice->next_invoice_date->format('M j, Y') }}</span>
        </div>
        @endif
        @if($invoice->recurrence_end_date)
        <div class="d-flex justify-content-between mb-3">
          <span style="color:var(--text-muted);">Ends</span>
          <span>{{ $invoice->recurrence_end_date->format('M j, Y') }}</span>
        </div>
        @endif
        @if($invoice->next_invoice_date)
        <form method="POST" action="{{ route('finance.invoices.recurring', $invoice) }}">
          @csrf
          <button type="submit" class="ncv-btn ncv-btn-outline ncv-btn-sm w-100"
                  onclick="return confirm('Generate next recurring invoice?')">
            <i class="bi bi-arrow-repeat me-1"></i>Generate Next Invoice
          </button>
        </form>
        @endif
        @if($invoice->childInvoices->count() > 0)
        <div style="margin-top:.75rem;font-size:.78rem;color:var(--text-muted);">
          {{ $invoice->childInvoices->count() }} invoice(s) generated from this template
        </div>
        @endif
      </div>
    </div>
    @endif

  </div>
</div>

@endsection
